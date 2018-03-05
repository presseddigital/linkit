<?php
namespace fruitstudios\linkit\base;

use Craft;
use craft\base\SavableComponentInterface;

interface LinkInterface extends SavableComponentInterface
{
    // Static
    // =========================================================================

    // Public Methods
    // =========================================================================

	public function __toString(): string;
    public function getLink(): string;
    public function getLinkAttributes(): array;
    public function getUrl(): string;

}

