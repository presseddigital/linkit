<?php
namespace fruitstudios\linkit\base;

use Craft;
use craft\base\Component;
use craft\base\SavableComponent;

use fruitstudios\linkit\models\Link;

abstract class LinkType extends SavableComponent implements LinkTypeInterface
{
    // Static
    // =========================================================================

    public static function defaultLabel(): string
    {
        $classNameParts = explode('\\', static::class);
        return array_pop($classNameParts);
    }

    public static function defaultValue(): string
    {
        return '';
    }

    // Public Methods
    // =========================================================================

    public function defaultSelectionLabel(): string
    {
        return Craft::t('link-it', 'Select') . ' ' . $this->defaultLabel();
    }

    public function getType(): string
    {
        return get_class($this);
    }

    public function getLabel(): string
    {
        return $this->defaultLabel();
    }

    public function getSettingsHtml()
    {
        return null;
    }

    public function getInputHtml($name)
    {
        return null;
    }

    // public function getLink(): Link
    public function getLink()
    {
        return null;
        // return new Link();
    }
}
