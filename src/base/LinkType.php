<?php
namespace fruitstudios\linkit\base;

use Craft;
use craft\base\SavableComponent;

use fruitstudios\linkit\base\Link;
use fruitstudios\linkit\base\LinkInterface;

abstract class LinkType extends SavableComponent implements LinkTypeInterface
{
    // Static
    // =========================================================================

    public static function defaultLabel(): string
    {
        $classNameParts = explode('\\', static::class);
        return array_pop($classNameParts);
    }

    public static function settingsTemplatePath(): string
    {
        return 'link-it/types/settings/_default';
    }

    public static function inputTemplatePath(): string
    {
        return 'link-it/types/input/_default';
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
       return Craft::$app->getView()->renderTemplate(
            static::inputTemplatePath(),
            [
                'type' => $this,
            ]
        );
    }

    public function getInputHtml($name, LinkInterface $link = null)
    {
        return Craft::$app->getView()->renderTemplate(
            static::inputTemplatePath(),
            [
                'name' => $name,
                'type' => $this,
                'link' => $link,
            ]
        );
    }

    public function getLink($value): LinkInterface
    {
        $link = new Link();
        $link->setAttributes($value, false);
        return $link;
    }
}
