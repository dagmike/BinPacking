<?php

require_once '../vendor/autoload.php';

use BinPacking\{RectangleBinPack, Rectangle, WindowedRectangle, FlipType};
use BinPacking\Helpers\VisualisationHelper;

$binWidth = 1120;
$binHeight = 815;
$bins = [];

$toPack = [
    new WindowedRectangle(450, 250, 100, 50, 100, 50, false, 'Rect 1'),
    new WindowedRectangle(450, 250, 100, 50, 100, 50, false, 'Rect 2'),
    new WindowedRectangle(450, 250, 50, 50, 50, 50, false, 'Rect 3', null, FlipType::AllowFlip, [ 'fontSize' => 60 ]),
    new WindowedRectangle(450, 250, 50, 50, 50, 50, false, 'Rect 4', null, FlipType::AllowFlip, [ 'fontSize' => 60 ]),
    new Rectangle(100, 100, 'Rect 5'),
    new Rectangle(100, 100, 'Rect 6'),
    new Rectangle(100, 100, 'Rect 7'),
    new Rectangle(100, 100, 'Rect 8'),
    new Rectangle(75, 75, 'Rect 9'),
    new Rectangle(75, 75, 'Rect 10'),
];

// While there are still things to pack, attempt to pack them
while (!empty($toPack)) {
    // Create a new bin
    $bins[] = (new RectangleBinPack($binWidth, $binHeight, true))->init();
    // Loop through all bins to try to fit what is left to pack
    foreach ($bins as $bin) {
        $bin->insertMany($toPack, 'RectBestLongSideFit');
        // Get what cannot be packed back and continue
        $toPack = $bin->getCantPack();
    }
}

// Draw each of the bins
$visOpts = [
    //'font' => 'c:\\Windows\\Fonts\\Comic.ttf',
    'fontSize' => 16,
    'fontColour' => 'black'
];
foreach ($bins as $key => $bin) {
    $image = VisualisationHelper::generateVisualisation($bin, $visOpts);
    $data = $image->getImageBlob();
    file_put_contents("viz-{$key}.png", $data);
    echo "Wrote viz-{$key}.png\n";
}

echo "\n";
