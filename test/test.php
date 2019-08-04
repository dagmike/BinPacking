<?php

require_once '../vendor/autoload.php';

use BinPacking\MaxRectsBinPack;

$binWidth = 100;
$binHeight = 100;

$bins = [new MaxRectsBinPack($binWidth, $binHeight, true)];

$toPack = [
    [50, 50],
    [50, 50],
    [50, 50],
    [25, 25],
    [25, 25]
];



foreach ($toPack as $packSize) {
    for ($i = 0; $i < count($bins); $i++) {
        try {
            $packed = $bins[$i]->insert($packSize[0], $packSize[1], 'RectBottomLeftRule');
            echo "Packed {$packed->getWidth()}x{$packed->getHeight()} into position x={$packed->getX()}, y={$packed->getY()}\n";
        } catch (Exception $e) {
            $bins[] = new MaxRectsBinPack($binWidth, $binHeight, true);
        }
    }
}

foreach ($bins as $key => $bin) {
    $bin->drawBin($key);
}

// $packed = $binPack->insert(25, 25, 'RectBottomLeftRule');