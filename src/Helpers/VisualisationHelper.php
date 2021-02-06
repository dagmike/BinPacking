<?php

namespace BinPacking\Helpers;

use BinPacking\RectangleBinPack;

class VisualisationHelper
{
    /**
     * Create an Imagick object of a bin
     *
     * @param RectangleBinPack $bin
     * @param array $opts
     * @return \Imagick
     */
    public static function generateVisualisation(RectangleBinPack $bin, $opts = []) : \Imagick
    {
        $draw = new \ImagickDraw();
        $strokeColour = new \ImagickPixel('black');
        $cutStrokeColour = new \ImagickPixel('red');
        $freeStrokeColour = new \ImagickPixel('blue');
        $fillColour = new \ImagickPixel('white');

        $textDraw = new \ImagickDraw();
        $textStrokeColour = new \ImagickPixel('rgb(0, 0, 0)');
        $textFillColour = new \ImagickPixel('rgb(0, 0, 0)');
        $textDraw->setStrokeColor($textStrokeColour);
        $textDraw->setFillColor($textFillColour);
        if (isset($opts['font'])) {
            $draw->setFont($opts['font']);
        }
        if (isset($opts['fontSize'])) {
            $draw->setFontSize($opts['fontSize']);
        }
        $labelMargin = isset($opts['labelMargin']) ? $opts['labelMargin'] : 20;

        $draw->setGravity(\Imagick::GRAVITY_CENTER);
        $cx = $bin->getBinWidth() / 2;
        $cy = $bin->getBinHeight() / 2;
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

            $label = $rect->getLabel();
            if ($label != null) {
                $tx = $topLeftX + (($bottomRightX - $topLeftX) / 2) - $cx - $margin;
                $ty = $topLeftY + (($bottomRightY - $topLeftY) / 2) - $cy - $margin;
                // set font color
                if (isset($opts['fontColour'])) {
                    $draw->setStrokeColor(new \ImagickPixel($opts['fontColour']));
                    $draw->setFillColor(new \ImagickPixel($opts['fontColour']));
                }
                // Word wrap the label within the allowed width
                $lines = explode("\n", $label);
                $maxWidth = $rect->getWidth() - ($labelMargin * 2);
                for ($i = 0 ; $i < count($lines) ; $i ++) {
                    $metrics = $imagick->queryFontMetrics($draw, $lines[$i]);
                    $curWidth = $metrics['textWidth'];
                    if ($curWidth > $maxWidth) {
                        // Need to split this line, if possible.. iterate until we find words that will fit
                        $curLine = $lines[$i];
                        while ($curWidth > 0 && $curWidth > $maxWidth) {
                            $curLine = substr($curLine, 0, strrpos($curLine, ' '));
                            $metrics = $imagick->queryFontMetrics($draw, $curLine);
                            $curWidth = $metrics['textWidth'];
                        }
                        // Check for no words fitting nicely
                        if (strlen($curLine) == 0) {
                            // If there really aren't any spaces, give up, otherwise take the first long word as this line candidate
                            if (strpos($lines[$i], ' ') == false) {
                                continue; // Give up and move on to the next line.
                            } else {
                                $curLine = substr($lines[$i], 0, strpos($lines[$i], ' '));
                            }
                        }
                        array_splice($lines, $i + 1, 0, substr($lines[$i], strlen($curLine) + 1));
                        $lines[$i] = $curLine;
                    }
                }
                $label = join("\n", $lines);
                $draw->annotation($tx, $ty, $label);
                // reset stroke and fill color
                $draw->setStrokeColor($cutStrokeColour);
                $draw->setFillColor($fillColour);

            }
        }

        $draw->setStrokeColor($freeStrokeColour);
        $draw->setFillColor('gray');
        $draw->setFillOpacity(0.1);
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
        // $draw->rectangle($margin, $margin, $bin->getBinWidth() + $margin, $bin->getBinHeight() + $margin);

        $imagick->setImageFormat("png");
        $imagick->drawImage($draw);

        return $imagick;
    }
}
