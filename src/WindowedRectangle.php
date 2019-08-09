<?php

namespace BinPacking;

class WindowedRectangle extends Rectangle
{
    /**
     * The window/aperture rectangle
     *
     * @var Rectangle
     */
    private $window;

    /**
     * The margin from the bottom of the outer y position
     *
     * @var int
     */
    private $bottomBorder;

    /**
     * The margin from the left of the outer x position
     *
     * @var int
     */
    private $leftBorder;

    /**
     * Construct the windowed rectangle
     *
     * @param int $width Outer width of the rectangle
     * @param int $height Outer height of the rectangle
     * @param int $bottomBorder Margin from the bottom of the outer rect
     * @param int $leftBorder Margin from the left of the outer rect
     * @param int|null $topBorder Margin from the top of the outer rect
     * @param int|null $rightBorder Margin from the right of the outer rect
     */
    public function __construct(
        int $width,
        int $height,
        int $bottomBorder,
        int $leftBorder,
        int $topBorder = null,
        int $rightBorder = null
    ) {
        parent::__construct($width, $height);
        
        $this->bottomBorder = $bottomBorder;
        $this->leftBorder = $leftBorder;
        $this->topBorder = $topBorder;
        $this->rightBorder = $rightBorder;

        if ($this->rightBorder) {
            $windowWidth = $width - ($this->leftBorder + $this->rightBorder);
        } else {
            $windowWidth = $width - (2 * $this->leftBorder);
        }

        if ($this->topBorder) {
            $windowHeight = $height - ($this->bottomBorder + $this->topBorder);
        } else {
            $windowHeight = $height - (2 * $this->bottomBorder);
        }

        $this->window = new Rectangle($windowWidth, $windowHeight);
    }

    /**
     * Get the window rectangle
     *
     * @return Rectangle
     */
    public function getWindow() : Rectangle
    {
        return $this->window;
    }

    /**
     * Get the bottom border
     *
     * @return int
     */
    public function getBottomBorder() : int
    {
        return $this->bottomBorder;
    }

    /**
     * Get the left border
     *
     * @return int
     */
    public function getLeftBorder() : int
    {
        return $this->leftBorder;
    }
}
