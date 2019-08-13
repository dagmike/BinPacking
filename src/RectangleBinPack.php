<?php

namespace BinPacking;

use BinPacking\Algorithms\{BestAreaFit, BestLongSideFit, BottomLeft, BestShortSideFit};
use BinPacking\Helpers\RectangleFactory;
use BinPacking\Helpers\RectangleHelper;

class RectangleBinPack
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
     * Array of rectangles unable to pack in the bin
     *
     * @var Rectangle[]
     */
    private $cantPack = [];

    /**
     * Bottom border of the bin that cannot be used
     */
    private $bottomBorder;

    /**
     * Left border of thebin that cannot be used
     */
    private $leftBorder;

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

        $this->bottomBorder = 0;
        $this->leftBorder = 0;

        $this->usedRectangles = [];
        $this->freeRectangles = [];
    }

    /**
     * Initialize the free bins to pack into
     *
     * @return void
     */
    public function init() : RectangleBinPack
    {
        // Create free rectangle
        $initialFree = new Rectangle($this->binWidth - $this->leftBorder, $this->binHeight - $this->bottomBorder);
        $initialFree->setPosition($this->leftBorder, $this->bottomBorder);

        $this->freeRectangles = [$initialFree];
        return $this;
    }

    /**
     * Set the bottom border of the sheet (that cannot be but)
     *
     * @param integer $bottomBorder
     * @return RectangleBinPack
     */
    public function setBottomBorder(int $bottomBorder) : RectangleBinPack
    {
        $this->bottomBorder = $bottomBorder;
        return $this;
    }

    /**
     * Set the left border of the sheet that cannot be cut
     *
     * @param integer $leftBorder
     * @return RectangleBinPack
     */
    public function setLeftBorder(int $leftBorder) : RectangleBinPack
    {
        $this->leftBorder = $leftBorder;
        return $this;
    }

    /**
     * Get the width of the bin
     *
     * @return integer
     */
    public function getBinWidth() : int
    {
        return $this->binWidth;
    }

    /**
     * Get the height of the bin
     *
     * @return integer
     */
    public function getBinHeight() : int
    {
        return $this->binHeight;
    }

    /**
     * Get whether the rectangles can be flipped or not
     *
     * @return boolean
     */
    public function isFlipAllowed() : bool
    {
        return $this->allowFlip;
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
     * Get the rectangles that have not been used
     *
     * @return Rectangle[]
     */
    public function getFreeRectangles() : array
    {
        return $this->freeRectangles;
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
            if (get_class($usedRect) == 'BinPacking\WindowedRectangle') {
                $usedSurfaceArea -= $usedRect->getWindow()->getWidth() * $usedRect->getWindow()->getHeight();
            }
        }

        return $usedSurfaceArea / ($this->binWidth * $this->binHeight);
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

        $score1 = RectangleHelper::MAXINT;
        $score2 = RectangleHelper::MAXINT;

        switch ($method) {
            case 'RectBottomLeftRule':
                $newNode = BottomLeft::findNewPosition($this, $rect, $score1, $score2);
                break;

            case 'RectBestAreaFit':
                $newNode = BestAreaFit::findNewPosition($this, $rect, $score1, $score2);
                break;

            case 'RectBestLongSideFit':
                $newNode = BestLongSideFit::findNewPosition($this, $rect, $score1, $score2);
                break;

            case 'RectBestShortSideFit':
                $newNode = BestShortSideFit::findNewPosition($this, $rect, $score1, $score2);
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

        while (count($toPack) > 0) {
            $bestScore1 = RectangleHelper::MAXINT;
            $bestScore2 = RectangleHelper::MAXINT;
            $bestRectIndex = -1;
            $bestNode = null;

            for ($i = 0; $i < count($toPack); ++$i) {
                $score1 = RectangleHelper::MAXINT;
                $score2 = RectangleHelper::MAXINT;
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
    private function placeRect(Rectangle $node)
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
        $score1 = RectangleHelper::MAXINT;
        $score2 = RectangleHelper::MAXINT;
        
        switch ($method) {
            case 'RectBottomLeftRule':
                $newNode = BottomLeft::findNewPosition($this, $rect, $score1, $score2);
                break;

            case 'RectBestAreaFit':
                $newNode = BestAreaFit::findNewPosition($this, $rect, $score1, $score2);
                break;

            case 'RectBestLongSideFit':
                $newNode = BestLongSideFit::findNewPosition($this, $rect, $score1, $score2);
                break;

            case 'RectBestShortSideFit':
                $newNode = BestShortSideFit::findNewPosition($this, $rect, $score1, $score2);
                break;

            default:
                throw new \InvalidArgumentException("Method {$method} not recognised.");
        }

        if (!$newNode) {
            $score1 = RectangleHelper::MAXINT;
            $score2 = RectangleHelper::MAXINT;
        }

        return $newNode;
    }

    /**
     * Remove the "used" node from the free node, then split the free node into 2 further free nodes
     *
     * @param Rectangle $freeNode
     * @param Rectangle $usedNode
     * @return boolean
     */
    private function splitFreeNode(Rectangle $freeNode, Rectangle $usedNode) : bool
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
                $newNode = RectangleFactory::fromRectangle($freeNode);
                $newNode->setHeight($usedNode->getY() - $newNode->getY());
                $this->freeRectangles[] = $newNode;
            }

            // New node at the bottom side of the used node.
            if (($usedNode->getY() + $usedNode->getHeight()) < ($freeNode->getY() + $freeNode->getHeight())) {
                $newNode = RectangleFactory::fromRectangle($freeNode);
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
                $newNode = RectangleFactory::fromRectangle($freeNode);
                $newNode->setWidth($usedNode->getX() - $newNode->getX());
                $this->freeRectangles[] = $newNode;
            }

            // New node at the right side of the used node.
            if (($usedNode->getX() + $usedNode->getWidth()) < ($freeNode->getX() + $freeNode->getWidth())) {
                $newNode = RectangleFactory::fromRectangle($freeNode);
                $newNode->setX($usedNode->getX() + $usedNode->getWidth());
                $newNode->setWidth(
                    ($freeNode->getX() + $freeNode->getWidth()) - ($usedNode->getX() + $usedNode->getWidth())
                );
                $this->freeRectangles[] = $newNode;
            }
        }

        // Check if the used node has a window
        if (get_class($usedNode) == "BinPacking\WindowedRectangle") {
            $newNode = RectangleFactory::fromRectangle($usedNode->getWindow());
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
    private function pruneFreeList() : void
    {
        
        for ($i = 0; $i < count($this->freeRectangles); ++$i) {
            for ($j = $i + 1; $j < count($this->freeRectangles); ++$j) {
                if (RectangleHelper::isContainedIn($this->freeRectangles[$i], $this->freeRectangles[$j])) {
                    unset($this->freeRectangles[$i]);
                    $this->freeRectangles = array_values($this->freeRectangles);
                    --$i;
                    break;
                }

                if (RectangleHelper::isContainedIn($this->freeRectangles[$j], $this->freeRectangles[$i])) {
                    unset($this->freeRectangles[$j]);
                    $this->freeRectangles = array_values($this->freeRectangles);
                    --$j;
                    break;
                }
            }
        }
    }
}
