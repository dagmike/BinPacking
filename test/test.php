<?php

require_once '../vendor/autoload.php';

use BinPacking\{RectangleBinPack, Rectangle, WindowedRectangle};
use BinPacking\Helpers\VisualisationHelper;

$binWidth = 1120;
$binHeight = 815;
$bins = [];

$toPack = [
    new Rectangle(100, 100),
    new Rectangle(100, 100),
    new Rectangle(100, 100),
    new Rectangle(100, 100),
    new Rectangle(100, 100),
    new Rectangle(100, 100),
    new Rectangle(100, 100),
    new Rectangle(100, 100),
    new Rectangle(100, 100),
    new Rectangle(100, 100),
    new WindowedRectangle(250, 250, 50, 50),
    new WindowedRectangle(250, 250, 50, 50),
    new WindowedRectangle(250, 250, 50, 50),
    new WindowedRectangle(250, 250, 50, 50),
    new WindowedRectangle(250, 250, 50, 50),
    new WindowedRectangle(250, 250, 50, 50),
    new WindowedRectangle(250, 250, 50, 50),
    new WindowedRectangle(250, 250, 50, 50),
    new WindowedRectangle(250, 250, 50, 50),
    new WindowedRectangle(250, 250, 50, 50)
];

// While there are still things to pack, attempt to pack them
while (!empty($toPack)) {
    // Create a new bin
    $bins[] = new RectangleBinPack($binWidth, $binHeight, true);
    // Loop through all bins to try to fit what is left to pack
    foreach ($bins as $bin) {
        $bin->insertMany($toPack, 'RectBestAreaFit');
        // Get what cannot be packed back and continue
        $toPack = $bin->getCantPack();
    }
}

// Draw each of the bins
foreach ($bins as $key => $bin) {
    $image = VisualisationHelper::generateVisualisation($bin);
    $data = $image->getImageBlob();
    file_put_contents("viz-{$key}.png", $data);
}

echo "\n";
