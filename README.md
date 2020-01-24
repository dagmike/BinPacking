# 2D Bin Packing Algorithms using PHP
This repository is a port of the 2D bin packing algorithms found here: [juj/RectangleBinPack](https://www.github.com/juj/RectangleBinPack) into PHP.

## Installation

You can install the package via composer:

```bash
composer require dagmike/BinPacking
```

## Usage
```php
use BinPacking\RectangleBinPack;
use BinPacking\Rectangle;

$bin = new RectangleBinPack(1000, 1000);

$packed = $bin->insert(new Rectangle(100, 100), "RectBestAreaFit");

if ($packed) {
    echo "Item ({$packed->getWidth()}x{$packed->getHeight()}) packed at position ({$packed->getX()}, {$packed->getY()})";
} else {
    echo "Unable to pack item";
}
```

## Currently Implemented Algorithms
* Maximum Rectangles
    * Bottom-Left - RectBottomLeft
    * Best Area Fit - RectBestAreaFit
    * Best Short Side Fit - RectBestShortSideFit
    * Best Long Side Fit - RectBestLongSideFit
