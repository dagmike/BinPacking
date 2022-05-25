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
     * Should the space inside the window be treated as free?
     *
     * @var bool
     */
    private $isHollow;

    /**
     * Border from the window that cannot be used
     */
    public const INNERBORDER = 15;

    /**
     * Construct the windowed rectangle
     *
     * @param int $width Outer width of the rectangle
     * @param int $height Outer height of the rectangle
     * @param int $bottomBorder Margin from the bottom of the outer rect
     * @param int $leftBorder Margin from the left of the outer rect
     * @param int|null $topBorder Margin from the top of the outer rect
     * @param int|null $rightBorder Margin from the right of the outer rect
     * @param bool $isHollow Should the algorithm consider the inside portion of this rect to allow other rects to pack into it?
     * @param string $label String to render in the center of the rect (may contain "\n" for multiline)
     * @param array $data Arbitrary data you can examine later to identify packed rects
     */
    public function __construct(
        int $width,
        int $height,
        int $bottomBorder,
        int $leftBorder,
        int $topBorder,
        int $rightBorder,
        bool $isHollow = true,
        string $label = null,
        array $data = null,
        string $allowFlip = FlipType::AllowFlip,
        array $visOptsOverrides = null
    ) {
        parent::__construct($width, $height, $label, $data, $allowFlip, $visOptsOverrides);
        $this->bottomBorder = $bottomBorder;
        $this->leftBorder = $leftBorder;
        $this->topBorder = $topBorder;
        $this->rightBorder = $rightBorder;
        $this->isHollow = $isHollow;

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

        $this->window = new Rectangle($windowWidth - (2 * self::INNERBORDER), $windowHeight - (2 * self::INNERBORDER));
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
     * @return integer
     */
    public function getBottomBorder() : int
    {
        return $this->bottomBorder;
    }

    /**
     * Get the left border
     *
     * @return integer
     */
    public function getLeftBorder() : int
    {
        return $this->leftBorder;
    }

    /**
     * Get the top border
     *
     * @return integer
     */
    public function getTopBorder() : int
    {
        return $this->topBorder ?? $this->bottomBorder;
    }

    /**
     * Get the right border
     *
     * @return integer
     */
    public function getRightBorder() : int
    {
        return $this->rightBorder ?? $this->leftBorder;
    }

    /**
     * Gets the isHollow attribute
     *
     * @return bool
     */
    public function getIsHollow() : int
    {
        return $this->isHollow;
    }

    /**
     * Rotate the rectangle and the window
     *
     * @return void
     */
    public function rotate() : void
    {
        parent::rotate();
     
        $this->window->rotate();
        $newBottomBorder = $this->getRightBorder();
        $newLeftBorder = $this->getBottomBorder();
        $newTopBorder = $this->getLeftBorder();
        $newRightBorder = $this->getTopBorder();

        $this->bottomBorder = $newBottomBorder;
        $this->leftBorder = $newLeftBorder;
        $this->topBorder = $newTopBorder;
        $this->rightBorder = $newRightBorder;
    }
}
