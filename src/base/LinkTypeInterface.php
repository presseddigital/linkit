<?php
namespace fruitstudios\linkit\base;

use Craft;
use craft\base\SavableComponentInterface;

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

    public function getInputHtml($name);

    public function getLink();
}
