<?php

namespace BinPacking;

class Rectangle
{
    /**
     * X position of where this rectangle is placed (bottom left)
     *
     * @var int
     */
    private $xPos;
    
    /**
     * Y position of where this rectangle is placed (bottom left)
     *
     * @var int
     */
    private $yPos;

    /**
     * Width of the rectangle
     *
     * @var int
     */
    private $width;

    /**
     * Height of the rectangle
     *
     * @var int
     */
    private $height;

    /**
     * Label for this rectangle
     *
     * @var string
     */
    private $label;

    public function __construct(int $width, int $height, string $label = null)
    {
        $this->width = $width;
        $this->height = $height;
        $this->xPos = 0;
        $this->yPos = 0;
        $this->label = $label;
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

    public function getLabel() : ?string
    {
        return $this->label;
    }

    public function setLabel(string $label = null)
    {
        $this->label = $label;
    }
}
