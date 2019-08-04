<?php

namespace BinPacking;

class Rect
{
    private $xPos;
    private $yPos;

    private $width;
    private $height;

    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function getWidth() : int
    {
        return $this->width;
    }

    public function getHeight() : int
    {
        return $this->height;
    }

    public function setPosition(int $xPos, int $yPos)
    {
        $this->xPos = $this->setX($xPos);
        $this->yPos = $this->setY($yPos);
    }

    public function getX() : int
    {
        return $this->xPos;
    }

    public function setX(int $xPos)
    {
        $this->xPos = $xPos;
    }

    public function getY() : int
    {
        return $this->yPos;
    }

    public function setY(int $yPos)
    {
        $this->yPos = $yPos;
    }
}
