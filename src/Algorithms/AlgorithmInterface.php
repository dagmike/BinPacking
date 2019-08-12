<?php

namespace BinPacking\Algorithms;

use BinPacking\{RectangleBinPack, Rectangle};

interface AlgorithmInterface
{
    public const MAXINT = 9999999;

    public static function findNewPosition(
        RectangleBinPack $bin,
        Rectangle $rectangle,
        int &$score1,
        int &$score2
    ) : ?Rectangle;
}
