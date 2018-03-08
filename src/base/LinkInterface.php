<?php
namespace fruitstudios\linkit\base;

use Craft;
use craft\base\ComponentInterface;
use craft\base\SavableComponentInterface;

interface LinkInterface extends ComponentInterface
{
    // Static
    // =========================================================================

    // Public Methods
    // =========================================================================

	public function __toString(): string;
    public function getLink($raw = true);
    public function getLinkAttributes(): array;
    public function getUrl(): string;
    public function getText(): string;

}

