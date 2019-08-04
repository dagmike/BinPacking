<?php

namespace BinPacking;

use Exception;

class MaxRectsBinPack
{
    /**
     * Width of the bin to pack into
     *
     * @var int
     */
    private $binWidth;
    
    /**
     * Height of the bin to pack into
     *
     * @var int
     */
    private $binHeight;

    /**
     * Allow 90 degree rotation or not
     *
     * @var bool
     */
    private $allowFlip;

    /**
     * Used rectangles array
     *
     * @var Rectangle[]
     */
    private $usedRectangles;
    
    /**
     * Used rectangles array
     *
     * @var Rectangle[]
     */
    private $freeRectangles;

    /**
     * Set maximum int for helpfulness
     */
    private const MAXINT = 9999999;

    public function __construct(int $width, int $height, bool $flip = true)
    {
        $this->binWidth = $width;
        $this->binHeight = $height;
        $this->allowFlip = $flip;

        // Create free rectangle
        $initialFree = new Rectangle($width, $height);
        $initialFree->setPosition(0, 0);

        $this->usedRectangles = [];
        $this->freeRectangles = [$initialFree];
    }

    /**
     * Insert a rectangle for a space to be found
     *
     * @param integer $width
     * @param integer $height
     * @param string $method
     * @return Rectangle
     */
    public function insert(int $width, int $height, string $method) : Rectangle
    {
        $newNode = null;

        $score1 = self::MAXINT;
        $score2 = self::MAXINT;

        switch ($method) {
            case 'RectBottomLeftRule':
                $newNode = $this->findPositionForNewNodeBottomLeft($width, $height, $score1, $score2);
                break;

            default:
                throw new \InvalidArgumentException("Method {$method} not recognised.");
        }

        if ($newNode->getHeight() === 0) {
            return $newNode;
        }

        $numRectsToProcess = count($this->freeRectangles);
        for ($i = 0; $i < $numRectsToProcess; ++$i) {
            if ($this->splitFreeNode($this->freeRectangles[$i], $newNode)) {
                unset($this->freeRectangles[$i]);
                $this->freeRectangles = array_values($this->freeRectangles);
                --$i;
                --$numRectsToProcess;
            }
        }

        $this->pruneFreeList();

        $this->usedRectangles[] = $newNode;
        return $newNode;
    }

    /**
     * Bottom left algorithm (max rectangles)
     *
     * @param integer $width
     * @param integer $height
     * @param integer $bestX
     * @param integer $bestY
     * @return Rectangle
     */
    private function findPositionForNewNodeBottomLeft(int $width, int $height, int &$bestX, int &$bestY) : Rectangle
    {
        $bestNode = null;
        $bestX = self::MAXINT;
        $bestY = self::MAXINT;

        foreach ($this->freeRectangles as $freeRect) {
            // Try to place the rectangle in upright (non-flipped) orientation
            if ($freeRect->getWidth() >= $width && $freeRect->getHeight() >= $height) {
                $topSideY = $freeRect->getY() + $height;
                if ($topSideY < $bestY || ($topSideY == $bestY && $freeRect->getX() < $bestX)) {
                    $bestNode = new Rectangle($width, $height);
                    $bestNode->setPosition($freeRect->getX(), $freeRect->getY());
                    $bestY = $topSideY;
                    $bestX = $freeRect->getX();
                }
            }

            if ($this->allowFlip && $freeRect->getWidth() >= $height && $freeRect->getHeight() >= $width) {
                $topSideY = $freeRect->getY() + $width;
                if ($topSideY < $bestY || ($topSideY == $bestY && $freeRect->getX() < $bestX)) {
                    $bestNode = new Rectangle($height, $width);
                    $bestNode->setPosition($freeRect->getX(), $freeRect->getY());
                    $bestY = $topSideY;
                    $bestX = $freeRect->getX();
                }
            }
        }

        if (!$bestNode) {
            throw new \Exception("Could not place block: {$width}x{$height}");
        }

        return $bestNode;
    }

    /**
     * Remove the "used" node from the free node, then split the free node into 2 further free nodes
     *
     * @param Rectangle $freeNode
     * @param Rectangle $usedNode
     * @return boolean
     */
    private function splitFreeNode(Rectangle $freeNode, Rectangle &$usedNode) : bool
    {
        // Test with SAT if the rectangles even intersect
        if ($usedNode->getX() >= ($freeNode->getX() + $freeNode->getWidth())
            || ($usedNode->getX() + $usedNode->getWidth()) <= $freeNode->getX()
            || $usedNode->getY() >= ($freeNode->getY() + $freeNode->getHeight())
            || ($usedNode->getY() + $usedNode->getHeight()) <= $freeNode->getY()) {
            return false;
        }

        if ($usedNode->getX() < ($freeNode->getX() + $freeNode->getWidth())
            && ($usedNode->getX() + $usedNode->getWidth()) > $freeNode->getX()) {
            // New node at the top side of the used node.
            if ($usedNode->getY() > $freeNode->getY()
                && $usedNode->getY() < ($freeNode->getY() + $freeNode->getHeight())) {
                $newNode = clone $freeNode;
                $newNode->setHeight($usedNode->getY() - $newNode->getY());
                $this->freeRectangles[] = $newNode;
            }

            // New node at the bottom side of the used node.
            if (($usedNode->getY() + $usedNode->getHeight()) < ($freeNode->getY() + $freeNode->getHeight())) {
                $newNode = clone $freeNode;
                $newNode->setY($usedNode->getY() + $usedNode->getHeight());
                $newNode->setHeight(
                    ($freeNode->getY() + $freeNode->getHeight()) - ($usedNode->getY() + $usedNode->getHeight())
                );
                $this->freeRectangles[] = $newNode;
            }
        }

        if ($usedNode->getY() < ($freeNode->getY() + $freeNode->getHeight())
            && ($usedNode->getY() + $usedNode->getHeight()) > $freeNode->getY()) {
            // New node at the left side of the used node.
            if ($usedNode->getX() > $freeNode->getX()
                && $usedNode->getX() < ($freeNode->getX() + $freeNode->getWidth())) {
                $newNode = clone $freeNode;
                $newNode->setWidth($usedNode->getX() - $newNode->getX());
                $this->freeRectangles[] = $newNode;
            }

            // New node at the right side of the used node.
            if (($usedNode->getX() + $usedNode->getWidth()) < ($freeNode->getX() + $freeNode->getWidth())) {
                $newNode = clone $freeNode;
                $newNode->setX($usedNode->getX() + $usedNode->getWidth());
                $newNode->setWidth(
                    ($freeNode->getX() + $freeNode->getWidth()) - ($usedNode->getX() + $usedNode->getWidth())
                );
                $this->freeRectangles[] = $newNode;
            }
        }

        return true;
    }

    /**
     * Remove any free rectangles that lie within another free rectangle
     *
     * @return void
     */
    private function pruneFreeList()
    {
        
        for ($i = 0; $i < count($this->freeRectangles); ++$i) {
            for ($j = $i + 1; $j < count($this->freeRectangles); ++$j) {
                if (self::isContainedIn($this->freeRectangles[$i], $this->freeRectangles[$j])) {
                    unset($this->freeRectangles[$i]);
                    $this->freeRectangles = array_values($this->freeRectangles);
                    --$i;
                    break;
                }

                if (self::isContainedIn($this->freeRectangles[$j], $this->freeRectangles[$i])) {
                    unset($this->freeRectangles[$j]);
                    $this->freeRectangles = array_values($this->freeRectangles);
                    --$j;
                    break;
                }
            }
        }
    }
    
    /**
     * Output the algorithm result to a file
     *
     * @return void
     */
    public function drawBin(string $filename)
    {
        $draw = new \ImagickDraw();
        $strokeColour = new \ImagickPixel('rgb(0, 0, 0)');
        $fillColour = new \ImagickPixel('rgb(255, 255, 255)');

        $draw->setStrokeColor($strokeColour);
        $draw->setFillColor($fillColour);
        $draw->setStrokeOpacity(1);
        $draw->setStrokeWidth(1);

        $margin = 10;
        $draw->rectangle($margin, $margin, $this->binWidth + $margin, $this->binHeight + $margin);
        
        foreach ($this->usedRectangles as $rect) {
            $topLeftX = $margin + $rect->getX();
            $topLeftY = $margin + $this->binHeight - $rect->getY() - $rect->getHeight();
            $bottomRightX = $topLeftX + $rect->getWidth();
            $bottomRightY = $topLeftY + $rect->getHeight();

            $draw->setStrokeDashArray([5]);
            $draw->setStrokeDashOffset(5);

            $draw->rectangle(
                $topLeftX,
                $topLeftY,
                $bottomRightX,
                $bottomRightY
            );
        }

        $imagick = new \Imagick();
        $imagick->newImage($this->binWidth + ($margin * 2), $this->binHeight + ($margin * 2), $fillColour);
        $imagick->setImageFormat("png");

        $imagick->drawImage($draw);

        $data = $imagick->getImageBlob();

        file_put_contents("viz-{$filename}.png", $data);
    }

    private static function isContainedIn(Rectangle $rectA, Rectangle $rectB) : bool
    {
        return $rectA->getX() >= $rectB->getX() && $rectA->getY() >= $rectB->getY()
            && $rectA->getX() + $rectA->getWidth() <= $rectB->getX() + $rectB->getWidth()
            && $rectA->getY() + $rectA->getHeight() <= $rectB->getY() + $rectB->getHeight();
    }
}
