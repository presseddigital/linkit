<?php
namespace fruitstudios\linkit\base;

use Craft;
use craft\base\SavableComponent;

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

    // Static
    // =========================================================================

    public $value;

    // Public Methods
    // =========================================================================


    public function __construct($value = null)
    {
        $this->value = $value;
    }

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

    public function getSelectionLabel()
    {
        return $this->defaultSelectionLabel();
    }

    public function getSettingsHtml()
    {
        return null;
    }

    public function getInputHtml($name)
    {
        return null;
    }

    public function getLink()
    {
        return null;
    }
}
