<?php
namespace fruitstudios\linkit\base;

use Craft;
use craft\base\SavableComponentInterface;

use fruitstudios\linkit\base\LinkInterface;

interface LinkTypeInterface extends SavableComponentInterface
{
    // Static
    // =========================================================================

    public static function defaultLabel(): string;

    public static function defaultValue(): string;

    // Public Methods
    // =========================================================================

    public function defaultSelectionLabel(): string;

    public function getLabel();

    public function getSelectionLabel();

    public function getInputHtml($name, LinkInterface $link = null);

    public function getLink($value): LinkInterface;
}
