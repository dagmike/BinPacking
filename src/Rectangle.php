<?php

namespace BinPacking;

class Rectangle
{
    private $xPos;
    private $yPos;

    private $width;
    private $height;

    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
        $this->xPos = 0;
        $this->yPos = 0;
    }

    public function getWidth() : int
    {
        return $this->width;
    }

    public function setWidth(int $width)
    {
        $this->width = $width;
    }

    public function getHeight() : int
    {
        return $this->height;
    }

    public function setHeight(int $height)
    {
        $this->height = $height;
    }

    public function setPosition(int $xPos, int $yPos)
    {
        $this->setX($xPos);
        $this->setY($yPos);
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
