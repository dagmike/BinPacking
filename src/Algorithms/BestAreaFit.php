<?php

namespace BinPacking\Algorithms;

use BinPacking\{RectangleBinPack, Rectangle, FlipType};
use BinPacking\Helpers\RectangleFactory;
use BinPacking\Helpers\RectangleHelper;

class BestAreaFit
{
    public static function findNewPosition(
        RectangleBinPack $bin,
        Rectangle $rectangle,
        int &$bestAreaFit,
        int &$bestShortSideFit
    ) : ?Rectangle {
        $bestNode = null;
        $bestAreaFit = RectangleHelper::MAXINT;
        $bestShortSideFit = RectangleHelper::MAXINT;

        foreach ($bin->getFreeRectangles() as $freeRect) {
            $areaFit = ($freeRect->getWidth() * $freeRect->getHeight()) - ($rectangle->getWidth() * $rectangle->getHeight());

            if ($freeRect->getWidth() >= $rectangle->getWidth() && $freeRect->getHeight() >= $rectangle->getHeight()) {
                $leftoverHoriz = abs($freeRect->getWidth() - $rectangle->getWidth());
                $leftoverVert = abs($freeRect->getHeight() - $rectangle->getHeight());
                $shortSideFit = min($leftoverHoriz, $leftoverVert);

                if ($areaFit < $bestAreaFit || ($areaFit == $bestAreaFit && $shortSideFit < $bestShortSideFit)) {
                    $bestNode = RectangleFactory::fromRectangle($rectangle);
                    $bestNode->setX($freeRect->getX());
                    $bestNode->setY($freeRect->getY());

                    $bestShortSideFit = $shortSideFit;
                    $bestAreaFit = $areaFit;
                }
            }

            $tryFlip = $rectangle->getAllowFlip() == FlipType::ForceFlip ||
                ($bin->isFlipAllowed() && $rectangle->getWidth() > $rectangle->getHeight());
            if ($tryFlip && ($freeRect->getWidth() >= $rectangle->getHeight() && $freeRect->getHeight() >= $rectangle->getWidth())) {
                $leftoverHoriz = abs($freeRect->getWidth() - $rectangle->getHeight());
                $leftoverVert = abs($freeRect->getHeight() - $rectangle->getWidth());
                $shortSideFit = min($leftoverHoriz, $leftoverVert);

                if ($areaFit < $bestAreaFit || ($areaFit == $bestAreaFit && $shortSideFit < $bestShortSideFit)) {
                    $bestNode = RectangleFactory::fromRectangle($rectangle);
                    $bestNode->setX($freeRect->getX());
                    $bestNode->setY($freeRect->getY());
                    $bestNode->rotate();
                             
                    $bestShortSideFit = $shortSideFit;
                    $bestAreaFit = $areaFit;
                }
            }
        }

        return $bestNode;
    }
}
