<?php

require_once '../vendor/autoload.php';

use BinPacking\{RectangleBinPack, Rectangle, WindowedRectangle};
use BinPacking\Helpers\VisualisationHelper;

$binWidth = 500;
$binHeight = 500;
$bins = [];

$toPack = [
    new WindowedRectangle(450, 250, 100, 50, 100, 50, false, "I am number 1\nYay", [ 'id' => 1 ]),
    new WindowedRectangle(450, 250, 100, 50, 100, 50, false, "I am number 2\nYay", [ 'id' => 2 ]),
    new WindowedRectangle(450, 250, 50, 50, 50, 50, false, "I am number 3\nYay", [ 'id' => 3 ]),
    new WindowedRectangle(450, 250, 50, 50, 50, 50, false, "I am number 4\nYay", [ 'id' => 4 ]),
    new Rectangle(100, 100, "I am number 5 but my label is long\nYay", [ 'id' => 5 ]),
    new Rectangle(100, 300, "I am number 6\nMy label is also fairly unreasonably long", [ 'id' => 6 ]),
    new Rectangle(100, 100, "I am number 7\nYay", [ 'id' => 7 ]),
    new Rectangle(400, 100, "I am number 8\nYay", [ 'id' => 8 ]),
    new Rectangle(375, 275, "I am number 9\nYay", [ 'id' => 9 ]),
    new Rectangle(275, 375, "I am number 10\nYay", [ 'id' => 10 ]),
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
    $image = VisualisationHelper::generateVisualisation($bin, [ 'labelMargin' => 10 ]);
    $data = $image->getImageBlob();
    file_put_contents("viz-{$key}.png", $data);

    echo "Dumping rects for bin $key...\n";
    $used = $bin->getUsedRectangles();
    foreach ($used as $rec) {
        echo "Item ({$rec->getWidth()}x{$rec->getHeight()}) at position ({$rec->getX()}, {$rec->getY()}) has data: " . json_encode($rec->getData()) . "\n";
    }
}

echo "\n";
