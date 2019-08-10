<?php

namespace BinPacking;

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
     * @var Rectangle[]
     */
    private $cantPack = [];

    /**
     * Bottom border of the bin that cannot be used
     */
    private const BOTTOMBORDER = 24;

    /**
     * Left border of thebin that cannot be used
     */
    private const LEFTBORDER = 24;

    /**
     * Construct the bin for packing into
     *
     * @param int $width  Width of the bin
     * @param int $height Height of the bin
     * @param boolean $flip   Allow rotation of the items to pack
     */
    public function __construct(int $width, int $height, bool $flip = true)
    {
        $this->binWidth = $width;
        $this->binHeight = $height;
        $this->allowFlip = $flip;

        // Create free rectangle
        $initialFree = new Rectangle($width - self::LEFTBORDER, $height - self::BOTTOMBORDER);
        $initialFree->setPosition(self::LEFTBORDER, self::BOTTOMBORDER);

        $this->usedRectangles = [];
        $this->freeRectangles = [$initialFree];
    }

    /**
     * Insert a rectangle for a space to be found
     *
     * @param Rectangle $rect
     * @param string $method
     * @return Rectangle
     */
    public function insert(Rectangle $rect, string $method) : ?Rectangle
    {
        $newNode = null;

        $score1 = self::MAXINT;
        $score2 = self::MAXINT;

        switch ($method) {
            case 'RectBottomLeftRule':
                $newNode = $this->findPositionForNewNodeBottomLeft($rect, $score1, $score2);
                break;

            case 'RectBestAreaFit':
                $newNode = $this->findPostionForNewNodeBestAreaFit($rect, $score1, $score2);
                break;

            default:
                throw new \InvalidArgumentException("Method {$method} not recognised.");
        }

        if (!$newNode) {
            return $newNode;
        }

        $this->placeRect($newNode);

        return $newNode;
    }

    /**
     * Insert multiple rectangles at once (trying to find the best fit)
     *
     * @param Rectangle[] $toPack
     * @param string $method
     * @return Rectangle[]
     */
    public function insertMany(array $toPack, string $method) : array
    {
        $packed = [];

        // die(var_dump($toPack));
        while (count($toPack) > 0) {
            $bestScore1 = self::MAXINT;
            $bestScore2 = self::MAXINT;
            $bestRectIndex = -1;
            $bestNode = null;

            for ($i = 0; $i < count($toPack); ++$i) {
                $score1 = self::MAXINT;
                $score2 = self::MAXINT;
                $newNode = $this->scoreRect(
                    $toPack[$i],
                    $method,
                    $score1,
                    $score2
                );

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

    /**
     * Place the rectangle in the bin
     *
     * @param Rectangle $node
     * @return void
     */
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

    /**
     * Attempt to get a "score" for how well the rectangle is placed (based on the algorithm used)
     *
     * @param int $width
     * @param int $height
     * @param string $method
     * @param int $score1
     * @param int $score2
     * @return Rectangle|null
     */
    private function scoreRect(Rectangle $rect, string $method, int &$score1, int &$score2) : ?Rectangle
    {
        $score1 = self::MAXINT;
        $score2 = self::MAXINT;
        
        switch ($method) {
            case 'RectBottomLeftRule':
                $newNode = $this->findPositionForNewNodeBottomLeft($rect, $score1, $score2);
                break;

            case 'RectBestAreaFit':
                $newNode = $this->findPostionForNewNodeBestAreaFit($rect, $score1, $score2);
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
     * Best area fit algorithm (max rectangles)
     *
     * @param Rectangle $rect
     * @param int $bestAreaFit
     * @param int $bestShortSideFit
     * @return Rectangle|null
     */
    private function findPostionForNewNodeBestAreaFit(
        Rectangle $rect,
        int &$bestAreaFit,
        int &$bestShortSideFit
    ) : ?Rectangle {
        $bestNode = null;
        $bestAreaFit = self::MAXINT;
        $bestShortSideFit = self::MAXINT;

        foreach ($this->freeRectangles as $freeRect) {
            $areaFit = ($freeRect->getWidth() * $freeRect->getHeight()) - ($rect->getWidth() * $rect->getHeight());

            if ($freeRect->getWidth() >= $rect->getWidth() && $freeRect->getHeight() >= $rect->getHeight()) {
                $leftoverHoriz = abs($freeRect->getWidth() - $rect->getWidth());
                $leftoverVert = abs($freeRect->getHeight() - $rect->getHeight());
                $shortSideFit = min($leftoverHoriz, $leftoverVert);

                if ($areaFit < $bestAreaFit || ($areaFit == $bestAreaFit && $shortSideFit < $bestShortSideFit)) {
                    $bestNode = clone $rect;
                    $bestNode->setX($freeRect->getX());
                    $bestNode->setY($freeRect->getY());

                    $bestShortSideFit = $shortSideFit;
                    $bestAreaFit = $areaFit;
                }
            }

            if ($this->allowFlip && $freeRect->getWidth() >= $rect->getHeight() && $freeRect->getHeight() >= $rect->getWidth()) {
                $leftoverHoriz = abs($freeRect->getWidth() - $rect->getHeight());
                $leftoverVert = abs($freeRect->getHeight() - $rect->getWidth());
                $shortSideFit = min($leftoverHoriz, $leftoverVert);

                if ($areaFit < $bestAreaFit || ($areaFit == $bestAreaFit && $shortSideFit < $bestShortSideFit)) {
                    $bestNode = clone $rect;
                    $bestNode->setX($freeRect->getX());
                    $bestNode->setY($freeRect->getY());
                    $bestNode->setWidth($rect->getHeight());
                    $bestNode->setHeight($rect->getWidth());

                    $bestShortSideFit = $shortSideFit;
                    $bestAreaFit = $areaFit;
                }
            }
        }

        return $bestNode;
    }

    /**
     * Bottom left algorithm (max rectangles)
     *
     * @param int $width
     * @param int $height
     * @param int $bestX
     * @param int $bestY
     * @return Rectangle|null
     */
    private function findPositionForNewNodeBottomLeft(Rectangle $rect, int &$bestX, int &$bestY) : ?Rectangle
    {
        $bestNode = null;
        $bestX = self::MAXINT;
        $bestY = self::MAXINT;

        foreach ($this->freeRectangles as $freeRect) {
            // Try to place the rectangle in upright (non-flipped) orientation
            if ($freeRect->getWidth() >= $rect->getWidth() && $freeRect->getHeight() >= $rect->getHeight()) {
                $topSideY = $freeRect->getY() + $rect->getHeight();
                if ($topSideY < $bestY || ($topSideY == $bestY && $freeRect->getX() < $bestX)) {
                    $bestNode = clone $rect;
                    $bestNode->setPosition($freeRect->getX(), $freeRect->getY());
                    $bestY = $topSideY;
                    $bestX = $freeRect->getX();
                }
            }

            if ($this->allowFlip && $freeRect->getWidth() >= $rect->getHeight() && $freeRect->getHeight() >= $rect->getWidth()) {
                $topSideY = $freeRect->getY() + $rect->getWidth();
                if ($topSideY < $bestY || ($topSideY == $bestY && $freeRect->getX() < $bestX)) {
                    $bestNode = clone $rect;
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

        // Check if the used node has a window
        if (get_class($usedNode) == "BinPacking\WindowedRectangle") {
            $newNode = clone $usedNode->getWindow();
            $newNode->setX($usedNode->getX() + $usedNode->getLeftBorder() + WindowedRectangle::INNERBORDER);
            $newNode->setY($usedNode->getY() + $usedNode->getBottomBorder() + WindowedRectangle::INNERBORDER);
            $this->freeRectangles[] = $newNode;
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
     * @return \Imagick
     */
    public function getVisualization() : \Imagick
    {
        $draw = new \ImagickDraw();
        $strokeColour = new \ImagickPixel('rgb(0, 0, 0)');
        $cutStrokeColour = new \ImagickPixel('rgb(255, 0, 0)');
        $freeStrokeColour = new \ImagickPixel('rgb(0, 0, 255)');
        $fillColour = new \ImagickPixel('rgb(255, 255, 255)');

        $draw->setStrokeColor($cutStrokeColour);
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

            if (get_class($rect) == "BinPacking\WindowedRectangle") {
                $draw->rectangle(
                    $topLeftX + $rect->getLeftBorder(),
                    $topLeftY + $rect->getTopBorder(),
                    $bottomRightX - $rect->getRightBorder(),
                    $bottomRightY - $rect->getBottomBorder()
                );
            }
        }

        $draw->setStrokeDashArray([null]);
        $draw->setStrokeDashOffset(0);
        $draw->setStrokeColor($freeStrokeColour);
        foreach ($this->freeRectangles as $rect) {
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

        $draw->setStrokeColor($strokeColour);

        $draw->setFillOpacity(0);
        $draw->setStrokeDashArray([null]);
        $draw->setStrokeWidth(1);
        $draw->rectangle($margin, $margin, $this->binWidth + $margin, $this->binHeight + $margin);

        $imagick = new \Imagick();
        $imagick->newImage($this->binWidth + ($margin * 2), $this->binHeight + ($margin * 2), $fillColour);

        $imagick->setImageFormat("png");
        $imagick->drawImage($draw);

        return $imagick;
    }

    /**
     * Get the array of rectangles unable to pack
     *
     * @return Rectangle[]
     */
    public function getCantPack() : array
    {
        return $this->cantPack;
    }

    /**
     * Get the rectangles that are "used" aka been placed in the bin
     *
     * @return Rectangle[]
     */
    public function getUsedRectangles() : array
    {
        return $this->usedRectangles;
    }

    /**
     * Get the percentage of the area of the bin used
     *
     * @return float
     */
    public function getUsage() : float
    {
        $usedSurfaceArea = 0;
        foreach ($this->usedRectangles as $usedRect) {
            $usedSurfaceArea += $usedRect->getWidth() * $usedRect->getHeight();
        }

        return $usedSurfaceArea / ($this->binWidth * $this->binHeight);
    }

    /**
     * Helper method to figure out if one rect is within another
     *
     * @param Rectangle $rectA
     * @param Rectangle $rectB
     * @return boolean
     */
    private static function isContainedIn(Rectangle $rectA, Rectangle $rectB) : bool
    {
        return $rectA->getX() >= $rectB->getX() && $rectA->getY() >= $rectB->getY()
            && $rectA->getX() + $rectA->getWidth() <= $rectB->getX() + $rectB->getWidth()
            && $rectA->getY() + $rectA->getHeight() <= $rectB->getY() + $rectB->getHeight();
    }
}
