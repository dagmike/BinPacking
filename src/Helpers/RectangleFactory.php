<?php

namespace BinPacking\Helpers;

use BinPacking\Rectangle;
use BinPacking\RectangleWithMargin;
use BinPacking\WindowedRectangle;

class RectangleFactory
{
    public static function fromRectangle(Rectangle $rectangle) : Rectangle
    {
        if (get_class($rectangle) == 'BinPacking\WindowedRectangle') {
            $rect = new WindowedRectangle(
                $rectangle->getWidth(),
                $rectangle->getHeight(),
                $rectangle->getBottomBorder(),
                $rectangle->getLeftBorder(),
                $rectangle->getTopBorder(),
                $rectangle->getRightBorder(),
                $rectangle->getIsHollow(),
                $rectangle->getLabel(),
                $rectangle->getData(),
                $rectangle->getAllowFlip(),
                $rectangle->getVisOptsOverrides()
            );
        } else {
            $rect = new Rectangle($rectangle->getWidth(), $rectangle->getHeight(),
                $rectangle->getLabel(), $rectangle->getData(), $rectangle->getVisOptsOverrides());
        }

        $rect->setX($rectangle->getX());
        $rect->setY($rectangle->getY());
        return $rect;
    }
}
