<?php

namespace BinPacking\Algorithms;

use BinPacking\{RectangleBinPack, Rectangle};
use BinPacking\Helpers\RectangleHelper;

class BottomLeft
{
    public static function findNewPosition(
        RectangleBinPack $bin,
        Rectangle $rectangle,
        int &$bestY,
        int &$bestX
    ) : ?Rectangle {
        $bestNode = null;
        $bestX = RectangleHelper::MAXINT;
        $bestY = RectangleHelper::MAXINT;

        foreach ($bin->getFreeRectangles() as $freeRect) {
            // Try to place the rectangle in upright (non-flipped) orientation
            if ($freeRect->getWidth() >= $rectangle->getWidth() && $freeRect->getHeight() >= $rectangle->getHeight()) {
                $topSideY = $freeRect->getY() + $rectangle->getHeight();
                if ($topSideY < $bestY || ($topSideY == $bestY && $freeRect->getX() < $bestX)) {
                    $bestNode = clone $rectangle;
                    $bestNode->setPosition($freeRect->getX(), $freeRect->getY());
                    $bestY = $topSideY;
                    $bestX = $freeRect->getX();
                }
            }

            if ($bin->isFlipAllowed() && $freeRect->getWidth() >= $rectangle->getHeight() && $freeRect->getHeight() >= $rectangle->getWidth()) {
                $topSideY = $freeRect->getY() + $rectangle->getWidth();
                if ($topSideY < $bestY || ($topSideY == $bestY && $freeRect->getX() < $bestX)) {
                    $bestNode = clone $rectangle;
                    $bestNode->setPosition($freeRect->getX(), $freeRect->getY());
                    $bestY = $topSideY;
                    $bestX = $freeRect->getX();
                    $bestNode->rotate();
                }
            }
        }

        return $bestNode;
    }
}
