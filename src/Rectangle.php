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
     * Label of the rectangle when visualised
     *
     * @var string
     */
    protected $label;

    /**
     * Indicates whether the rotate function was called
     *
     * @var bool
     */
    protected $isRotated = false;

    /**
     * Construct the rectangle
     *
     * @param integer $width Outer width of the rectangle
     * @param integer $height Outer height of the rectangle
     */
    public function __construct(int $width, int $height, string $label = null, $data = null)
    {
        $this->width = $width;
        $this->height = $height;
        $this->xPos = 0;
        $this->yPos = 0;
        $this->label = $label;
        $this->data = $data;
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
     * Get the label
     *
     * @return string
     */
    public function getLabel() : ?string
    {
        return $this->label;
    }

    /**
     * Set the label
     *
     * @param string $label
     * @return void
     */
    public function setLabel(string $label) : void
    {
        $this->label = $label;
    }

    /**
     * Get the extra data about this rect
     *
     * @return array
     */
    public function getData() : ?array
    {
        return $this->data;
    }

    /**
     * Set the extra data about this rect
     *
     * @param array $data
     * @return void
     */
    public function setData(array $data) : void
    {
        $this->data = $data;
    }

    /**
     * Returns whether the rotate function was used
     *
     * @return bool
     */
    public function getIsRotated() : bool
    {
        return $this->isRotated;
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
        $this->isRotated = true;
    }
}
