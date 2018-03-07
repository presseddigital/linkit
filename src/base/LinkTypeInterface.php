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

    public static function settingsTemplatePath(): string;

    public static function inputTemplatePath(): string;

    // Public Methods
    // =========================================================================

    public function defaultSelectionLabel(): string;

    public function getLabel();

    public function getSelectionLabel();

    public function getInputHtml($name, LinkInterface $link = null);

    public function getLink($value): LinkInterface;
}
