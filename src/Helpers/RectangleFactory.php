<?php

namespace BinPacking\Helpers;

use BinPacking\Rectangle;
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
                $rectangle->getLeftBorder()
            );
        } else {
            $rect = new Rectangle($rectangle->getWidth(), $rectangle->getHeight());
        }

        $rect->setX($rectangle->getX());
        $rect->setY($rectangle->getY());
        return $rect;
    }
}
