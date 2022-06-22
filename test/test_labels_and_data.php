<?php

require_once '../vendor/autoload.php';

use BinPacking\{RectangleBinPack, Rectangle, WindowedRectangle};
use BinPacking\Helpers\VisualisationHelper;

$binWidth = 1120;
$binHeight = 815;
$bins = [];

$toPack = [
    new WindowedRectangle(450, 250, 100, 50, 100, 50, false, "I am number 1", [ 'id' => 1 ]),
    new WindowedRectangle(450, 250, 100, 50, 100, 50, false, "I am number 2", [ 'id' => 2 ]),
    new WindowedRectangle(450, 250, 50, 50, 50, 50, false, "I am number 3", [ 'id' => 3 ]),
    new WindowedRectangle(450, 250, 50, 50, 50, 50, false, "I am number 4", [ 'id' => 4 ]),
    new Rectangle(100, 100, "I am number 5", [ 'id' => 5 ], false),
    new Rectangle(100, 100, "I am number 6", [ 'id' => 6 ], false),
    new Rectangle(100, 100, "I am number 7", [ 'id' => 7 ], false),
    new Rectangle(100, 100, "I am number 8", [ 'id' => 8 ], false),
    new Rectangle(75, 75, "I am number 9", [ 'id' => 9 ], false),
    new Rectangle(75, 75, "I am number 10", [ 'id' => 10 ], false),
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
foreach ($bins as $key => $bin) {
    $image = VisualisationHelper::generateVisualisation($bin);
    $data = $image->getImageBlob();
    file_put_contents("viz-{$key}.png", $data);
    echo "Wrote viz-{$key}.png\n";

    foreach ($bin->getUsedRectangles() as $rect) {
        echo "  Used rect had data: " . json_encode($rect->getData()) . "\n";
    }
}

echo "\n";
