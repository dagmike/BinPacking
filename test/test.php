<?php

require_once '../vendor/autoload.php';

use BinPacking\{MaxRectsBinPack, Rectangle};

$binWidth = 500;
$binHeight = 500;
$bins = [];

$toPack = [
    new Rectangle(100, 100, 'A'),
    new Rectangle(100, 100, 'B'),
    new Rectangle(100, 100, 'C'),
    new Rectangle(100, 100, 'D'),
    new Rectangle(50, 400, 'E'),
    new Rectangle(100, 200, 'F'),
    new Rectangle(500, 500)
];

while (!empty($toPack)) {
    $bins[] = new MaxRectsBinPack($binWidth, $binHeight, true);
    foreach ($bins as $bin) {
        $bin->insertMany($toPack, 'RectBottomLeftRule');
        $toPack = $bin->getCantPack();
    }
}

foreach ($bins as $key => $bin) {
    $bin->drawBin($key);
}
