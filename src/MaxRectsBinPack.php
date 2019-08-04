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
     * @var Rect[]
     */
    private $usedRectangles;
    
    /**
     * Used rectangles array
     *
     * @var Rect[]
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
        $initialFree = new Rect($width, $height);
        $initialFree->setPosition(0, 0);

        $this->usedRectangles = [];
        $this->freeRectangles = [$initialFree];
    }

    public function insert(int $width, int $height, string $method) : Rect
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

        foreach ($this->freeRectangles as $key => $freeRect) {
            if ($this->splitFreeNode($freeRect, $newNode)) {
                unset($this->freeRectangles[$key]);
                $this->freeRectangles = array_values($this->freeRectangles);
            }
        }

        $this->pruneFreeList();

        $this->usedRectangles[] = $newNode;
        return $newNode;
    }

    private function findPositionForNewNodeBottomLeft(int $width, int $height, int &$bestX, int &$bestY) : Rect
    {
        $bestNode = null;

        $bestX = self::MAXINT;
        $bestY = self::MAXINT;

        foreach ($this->freeRectangles as $freeRect) {
            // Try to place the rectangle in upright (non-flipped) orientation
            if ($freeRect->getWidth() >= $width && $freeRect->getHeight() >= $height) {
                $topSideY = $freeRect->getY() + $height;
                if ($topSideY < $bestY || ($topSideY == $bestY && $freeRect->getX() < $bestX)) {
                    $bestNode = new Rect($width, $height);
                    $bestNode->setPosition($freeRect->getX(), $freeRect->getY());
                    $bestY = $topSideY;
                    $bestX = $freeRect->getX();
                }
            }

            if ($this->allowFlip && $freeRect->getWidth() >= $height && $freeRect->getHeight() >= $width) {
                $topSideY = $freeRect->getY() + $width;
                if ($topSideY < $bestY || ($topSideY == $bestY && $freeRect->getX() < $bestX)) {
                    $bestNode = new Rect($height, $width);
                    $bestNode->setPosition($freeRect->getX(), $freeRect->getY());
                    $bestY = $topSideY;
                    $bestX = $freeRect->getX();
                }
            }
        }

        return $bestNode;
    }

    private function splitFreeNode(Rect $freeNode, Rect &$usedNode) : bool
    {
        // Test with SAT if the rectangles even intersect
        if ($usedNode->getX() >= ($freeNode->getX() + $freeNode->getWidth())
            || $usedNode->getX() + $usedNode->getWidth() <= $freeNode->getX()
            || $usedNode->getY() >= $freeNode->getY() + $freeNode->getHeight()
            || $usedNode->getY() + $usedNode->getHeight() <= $freeNode->getY()) {
            return false;
        }

        if ($usedNode->getX() < $freeNode->getX() + $freeNode->getWidth()
            && $usedNode->getX() + $usedNode->getWidth() > $freeNode->getX()) {
            // New node at the top side of the used node.
            if ($usedNode->getY() > $freeNode->getY()
                && $usedNode->getY() < $freeNode->getY() + $freeNode->getHeight()) {
                $newNode = $freeNode;
                $newNode->setHeight($usedNode->getY() - $newNode->getY());
                $this->freeRectangles[] = $newNode;
            }

            // New node at the bottom side of the used node.
            if ($usedNode->getY() + $usedNode->getHeight() < $freeNode->getY() + $freeNode->getHeight()) {
                $newNode = $freeNode;
                $newNode->setY($usedNode->getY() + $usedNode->getHeight());
                $newNode->getHeight(
                    $freeNode->getY() + $freeNode->getHeight() - ($usedNode->getY() + $usedNode->getHeight())
                );
                $this->freeRectangles[] = $newNode;
            }
        }

        if ($usedNode->getY() < $freeNode->getY() + $freeNode->getHeight()
            && $usedNode->getY() + $usedNode->getHeight() > $freeNode->getY()) {
            // New node at the left side of the used node.
            if ($usedNode->getX() > $freeNode->getX()
                && $usedNode->getX() < $freeNode->getX() + $freeNode->getWidth()) {
                $newNode = $freeNode;
                $newNode->setWidth($usedNode->getX() - $newNode->getX());
                $this->freeRectangles[] = $newNode;
            }

            // New node at the right side of the used node.
            if ($usedNode->getX() + $usedNode->getWidth() < $freeNode->getX() + $freeNode->getWidth()) {
                $newNode = $freeNode;
                $newNode->setX($usedNode->getX() + $usedNode->getWidth());
                $newNode->setWidth(
                    $freeNode->getX() + $freeNode->getWidth() - ($usedNode->getX() + $usedNode->getWidth())
                );
                $this->freeRectangles[] = $newNode;
            }
        }

        return true;
    }

    private function pruneFreeList()
    {
        foreach ($this->freeRectangles as $keyA => $freeRectA) {
            foreach ($this->freeRectangles as $keyB => $freeRectB) {
                if (self::isContainedIn($freeRectA, $freeRectB)) {
                    unset($this->freeRectangles[$keyA]);
                    $this->freeRectangles = array_values($this->freeRectangles);
                    break;
                }

                if (self::isContainedIn($freeRectB, $freeRectA)) {
                    unset($this->freeRectangles[$keyB]);
                    $this->freeRectangles = array_values($this->freeRectangles);
                }
            }
        }
    }

    private static function isContainedIn(Rect $rectA, Rect $rectB) : bool
    {
        return $rectA->getX() >= $rectB->getX() && $rectA->getY() >= $rectB->getY()
            && $rectA->getX() + $rectA->getWidth() <= $rectB->getX() + $rectB->getWidth()
            && $rectA->getY() + $rectA->getHeight() <= $rectB->getY() + $rectB->getHeight();
    }
}
