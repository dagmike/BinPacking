<?php

namespace BinPacking\Algorithms;

use BinPacking\{RectangleBinPack, Rectangle, FlipType};
use BinPacking\Helpers\RectangleFactory;
use BinPacking\Helpers\RectangleHelper;

class Linear
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

        $bestNode = RectangleFactory::fromRectangle($rectangle);
        if ($rectangle->getAllowFlip() == FlipType::ForceFlip ||
            ($bin->isFlipAllowed() && $rectangle->getWidth() > $rectangle->getHeight())) {
            $bestNode->rotate();
        }

        foreach ($bin->getFreeRectangles() as $freeRect) {
            if ($freeRect->getY() == 0) {
                if ($freeRect->getWidth() >= $bestNode->getWidth()) {
                    $bestNode->setX($freeRect->getX());
                    $bestNode->setY($freeRect->getY());
                    $bestX = $freeRect->getWidth() - $bestNode->getWidth();
                    // Favour the tallest items first.
                    $bestY = $freeRect->getHeight() - $rectangle->getHeight();
                    return $bestNode;
                }
            }
        }
        return null;
    }
}