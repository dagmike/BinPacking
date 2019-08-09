<?php

require_once '../vendor/autoload.php';

use BinPacking\{MaxRectsBinPack, Rectangle};

$binWidth = 500;
$binHeight = 500;
$bins = [];

$toPack = [
    new Rectangle(100, 100, 'A'),
    new Rectangle(100, 100, 'B'),
    new Rectangle(200, 200, 'C'),
    new Rectangle(100, 100, 'D'),
    new Rectangle(50, 400, 'E'),
    new Rectangle(100, 200, 'F'),
    new Rectangle(350, 150),
    new Rectangle(100, 200),
    new Rectangle(100, 100, 'A'),
    new Rectangle(100, 100, 'B'),
    new Rectangle(200, 200, 'C'),
    new Rectangle(100, 100, 'D'),
    new Rectangle(50, 400, 'E'),
    new Rectangle(100, 200, 'F'),
    new Rectangle(350, 150),
    new Rectangle(100, 200)
];

// While there are still things to pack, attempt to pack them
while (!empty($toPack)) {
    // Create a new bin
    $bins[] = new MaxRectsBinPack($binWidth, $binHeight, true);
    // Loop through all bins to try to fit what is left to pack
    foreach ($bins as $bin) {
        $bin->insertMany($toPack, 'RectBottomLeftRule');
        // Get what cannot be packed back and continue
        $toPack = $bin->getCantPack();
    }
}

// Draw each of the bins
foreach ($bins as $key => $bin) {
    echo "\n[Bin {$key}] - " . count($bin->getUsedRectangles()) . " items - " . round($bin->getUsage() * 100, 1) . "% used";
    $image = $bin->getVisualization($key);
    $data = $image->getImageBlob();
    file_put_contents("viz-{$key}.png", $data);
}


echo "\n";
