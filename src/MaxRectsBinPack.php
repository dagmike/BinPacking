<?php

namespace BinPacking;
use BinPacking\Exceptions\CannotPackException;

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

    /**
     * Array of rectangles unable to pack in the bin
     *
     * @var Rectangle
     */
    private $cantPack = [];

    public function getCantPack() : array
    {
        return $this->cantPack;
    }

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
     * Undocumented function
     *
     * @param Rectangle[] $toPack
     * @param string $method
     * @return void
     */
    public function insertMany(array $toPack, string $method)
    {
        $packed = [];

        while (count($toPack) > 0) {
            $bestScore1 = self::MAXINT;
            $bestScore2 = self::MAXINT;
            $bestRectIndex = -1;
            $bestNode = null;

            for ($i = 0; $i < count($toPack); ++$i) {
                $score1 = self::MAXINT;
                $score2 = self::MAXINT;
                $newNode = $this->scoreRect(
                    $toPack[$i]->getWidth(),
                    $toPack[$i]->getHeight(),
                    $method,
                    $score1,
                    $score2
                );

                if ($newNode) {
                    $newNode->setLabel($toPack[$i]->getLabel());
                }

                if ($score1 < $bestScore1 || ($score1 == $bestScore1 && $score2 < $bestScore2)) {
                    $bestScore1 = $score1;
                    $bestScore2 = $score2;
                    $bestNode = $newNode;
                    $bestRectIndex = $i;
                }
            }

            // Can't fit the rectangle
            if ($bestRectIndex == -1) {
                $this->cantPack = $toPack;
                $toPack = [];
            } else {
                $this->placeRect($bestNode);
                $packed[] = $bestNode;
                unset($toPack[$bestRectIndex]);
                $toPack = array_values($toPack);
            }
        }

        return $packed;
    }

    private function placeRect(Rectangle &$node)
    {
        $numRectsToProcess = count($this->freeRectangles);
        for ($i = 0; $i < $numRectsToProcess; ++$i) {
            if ($this->splitFreeNode($this->freeRectangles[$i], $node)) {
                unset($this->freeRectangles[$i]);
                $this->freeRectangles = array_values($this->freeRectangles);
                --$i;
                --$numRectsToProcess;
            }
        }

        $this->pruneFreeList();

        $this->usedRectangles[] = $node;
    }

    private function scoreRect(int $width, int $height, string $method, int &$score1, int &$score2)
    {
        $score1 = self::MAXINT;
        $score2 = self::MAXINT;
        
        switch ($method) {
            case 'RectBottomLeftRule':
                $newNode = $this->findPositionForNewNodeBottomLeft($width, $height, $score1, $score2);
                break;

            default:
                throw new \InvalidArgumentException("Method {$method} not recognised.");
        }

        if (!$newNode) {
            $score1 = self::MAXINT;
            $score2 = self::MAXINT;
        }

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
    private function findPositionForNewNodeBottomLeft(int $width, int $height, int &$bestX, int &$bestY) : ?Rectangle
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
        $draw->setStrokeWidth(1);
        $draw->setStrokeDashArray([5]);
        $draw->setStrokeDashOffset(5);

        $margin = 10;
        
        foreach ($this->usedRectangles as $rect) {
            $topLeftX = $margin + $rect->getX();
            $topLeftY = $margin + $this->binHeight - $rect->getY() - $rect->getHeight();
            $bottomRightX = $topLeftX + $rect->getWidth();
            $bottomRightY = $topLeftY + $rect->getHeight();

            $draw->rectangle(
                $topLeftX,
                $topLeftY,
                $bottomRightX,
                $bottomRightY
            );
        }

        $draw->setFillOpacity(0);
        $draw->setStrokeDashArray([null]);
        $draw->rectangle($margin, $margin, $this->binWidth + $margin, $this->binHeight + $margin);

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
