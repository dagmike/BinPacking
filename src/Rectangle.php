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
    protected $width;

    /**
     * Height of the rectangle
     *
     * @var int
     */
    protected $height;

    /**
     * Construct the rectangle
     *
     * @param integer $width Outer width of the rectangle
     * @param integer $height Outer height of the rectangle
     */
    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
        $this->xPos = 0;
        $this->yPos = 0;
    }

    /**
     * Get the width of the rectangle
     *
     * @return integer
     */
    public function getWidth() : int
    {
        return $this->width;
    }

    /**
     * Set the width of the rectangle
     *
     * @param integer $width
     * @return void
     */
    public function setWidth(int $width) : void
    {
        $this->width = $width;
    }

    /**
     * Get the height of the rectangle
     *
     * @return integer
     */
    public function getHeight() : int
    {
        return $this->height;
    }

    /**
     * Set the height of the rectangle
     *
     * @param integer $height
     * @return void
     */
    public function setHeight(int $height) : void
    {
        $this->height = $height;
    }

    /**
     * Set the position of the rectangle
     *
     * @param int $xPos
     * @param int $yPos
     * @return void
     */
    public function setPosition(int $xPos, int $yPos) : void
    {
        $this->setX($xPos);
        $this->setY($yPos);
    }

    /**
     * Get the X coordinate
     *
     * @return integer
     */
    public function getX() : int
    {
        return $this->xPos;
    }

    /**
     * Set the X coordinate
     *
     * @param integer $xPos
     * @return void
     */
    public function setX(int $xPos) : void
    {
        $this->xPos = $xPos;
    }

    /**
     * Get the Y coordinate
     *
     * @return integer
     */
    public function getY() : int
    {
        return $this->yPos;
    }

    /**
     * Set the Y coordinate
     *
     * @param integer $yPos
     * @return void
     */
    public function setY(int $yPos) : void
    {
        $this->yPos = $yPos;
    }

    /**
     * Rotate the rectangle
     *
     * @return void
     */
    public function rotate() : void
    {
        $newWidth = $this->height;
        $newHeight = $this->width;

        $this->height = $newHeight;
        $this->width = $newWidth;
    }
}
