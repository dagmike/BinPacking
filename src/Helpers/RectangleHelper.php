<?php

namespace BinPacking\Helpers;

use BinPacking\Rectangle;

class RectangleHelper
{
    public const MAXINT = 99999999;

    /**
     * Check if the first rectangle is within the second rectangle
     *
     * @param Rectangle $rectA
     * @param Rectangle $rectB
     * @return bool
     */
    public static function isContainedIn(Rectangle $rectA, Rectangle $rectB) : bool
    {
        return $rectA->getX() >= $rectB->getX() && $rectA->getY() >= $rectB->getY()
            && $rectA->getX() + $rectA->getWidth() <= $rectB->getX() + $rectB->getWidth()
            && $rectA->getY() + $rectA->getHeight() <= $rectB->getY() + $rectB->getHeight();
    }
}
