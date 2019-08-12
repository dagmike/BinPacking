<?php

namespace BinPacking\Algorithms;

use BinPacking\{RectangleBinPack, Rectangle};
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
                    $bestNode = clone $rectangle;
                    $bestNode->setX($freeRect->getX());
                    $bestNode->setY($freeRect->getY());

                    $bestShortSideFit = $shortSideFit;
                    $bestAreaFit = $areaFit;
                }
            }

            if ($bin->isFlipAllowed() && $freeRect->getWidth() >= $rectangle->getHeight() && $freeRect->getHeight() >= $rectangle->getWidth()) {
                $leftoverHoriz = abs($freeRect->getWidth() - $rectangle->getHeight());
                $leftoverVert = abs($freeRect->getHeight() - $rectangle->getWidth());
                $shortSideFit = min($leftoverHoriz, $leftoverVert);

                if ($areaFit < $bestAreaFit || ($areaFit == $bestAreaFit && $shortSideFit < $bestShortSideFit)) {
                    $bestNode = clone $rectangle;
                    $bestNode->setX($freeRect->getX());
                    $bestNode->setY($freeRect->getY());
                    $bestNode->setWidth($rectangle->getHeight());
                    $bestNode->setHeight($rectangle->getWidth());

                    $bestShortSideFit = $shortSideFit;
                    $bestAreaFit = $areaFit;
                }
            }
        }

        return $bestNode;
    }
}
