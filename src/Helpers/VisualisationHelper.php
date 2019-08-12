<?php

namespace BinPacking\Helpers;

use BinPacking\RectangleBinPack;

class VisualisationHelper
{
    /**
     * Create an Imagick object of a bin
     *
     * @param RectangleBinPack $bin
     * @return \Imagick
     */
    public static function generateVisualisation(RectangleBinPack $bin) : \Imagick
    {
        $draw = new \ImagickDraw();
        $strokeColour = new \ImagickPixel('rgb(0, 0, 0)');
        $cutStrokeColour = new \ImagickPixel('rgb(255, 0, 0)');
        $freeStrokeColour = new \ImagickPixel('rgb(0, 0, 255)');
        $fillColour = new \ImagickPixel('rgb(255, 255, 255)');

        $margin = 10;

        $imagick = new \Imagick();
        $imagick->newImage($bin->getBinWidth() + ($margin * 2), $bin->getBinHeight() + ($margin * 2), $fillColour);

        $draw->setStrokeColor($cutStrokeColour);
        $draw->setFillColor($fillColour);
        $draw->setStrokeWidth(1);
        $draw->setStrokeDashArray([5]);
        $draw->setStrokeDashOffset(5);

        foreach ($bin->getUsedRectangles() as $rect) {
            $topLeftX = $margin + $rect->getX();
            $topLeftY = $margin + $bin->getBinHeight() - $rect->getY() - $rect->getHeight();
            $bottomRightX = $topLeftX + $rect->getWidth();
            $bottomRightY = $topLeftY + $rect->getHeight();

            $draw->rectangle(
                $topLeftX,
                $topLeftY,
                $bottomRightX,
                $bottomRightY
            );

            if (get_class($rect) == "BinPacking\WindowedRectangle") {
                $draw->rectangle(
                    $topLeftX + $rect->getLeftBorder(),
                    $topLeftY + $rect->getTopBorder(),
                    $bottomRightX - $rect->getRightBorder(),
                    $bottomRightY - $rect->getBottomBorder()
                );
            }
        }

        $draw->setStrokeDashArray([null]);
        $draw->setStrokeDashOffset(0);
        $draw->setStrokeColor($freeStrokeColour);
        foreach ($bin->getFreeRectangles() as $rect) {
            $topLeftX = $margin + $rect->getX();
            $topLeftY = $margin + $bin->getBinHeight() - $rect->getY() - $rect->getHeight();
            $bottomRightX = $topLeftX + $rect->getWidth();
            $bottomRightY = $topLeftY + $rect->getHeight();

            $draw->rectangle(
                $topLeftX,
                $topLeftY,
                $bottomRightX,
                $bottomRightY
            );
        }

        $draw->setStrokeColor($strokeColour);

        $draw->setFillOpacity(0);
        $draw->setStrokeDashArray([null]);
        $draw->setStrokeWidth(1);
        $draw->rectangle($margin, $margin, $bin->getBinWidth() + $margin, $bin->getBinHeight() + $margin);

        $imagick->setImageFormat("png");
        $imagick->drawImage($draw);

        return $imagick;
    }
}
