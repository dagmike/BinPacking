<?php

namespace BinPacking;

abstract class FlipType {
    /**
     * Rotation is not allowed.
     */
    public const NoFlip = "NoFlip";
    /**
     * Rotation may happen if the algorithm decides it's better.
     */
    public const AllowFlip = "AllowFlip";
    /**
     * Rotation is forced to happen.
     */
    public const ForceFlip = "ForceFlip";
}
