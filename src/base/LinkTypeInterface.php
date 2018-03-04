<?php
namespace fruitstudios\linkit\base;

use Craft;
use craft\base\ComponentInterface;
use craft\base\SavableComponentInterface;

use fruitstudios\linkit\models\Link;

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

    public function getInputHtml($name);

    public function getLink();
    // public function getLink(): Link;

}
