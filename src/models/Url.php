<?php
namespace fruitstudios\linkit\models;

use Craft;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\base\LinkType;

class Url extends LinkType
{
    // Private
    // =========================================================================

    private $_settingsHtmlPath = 'link-it/types/settings/_default';
    private $_inputHtmlPath = 'link-it/types/input/_default';

    // Public
    // =========================================================================

    public $typeLabel;

    // Static
    // =========================================================================

    public static function defaultLabel(): string
    {
        return Craft::t('link-it', 'URL');
    }

    public static function defaultValue(): string
    {
        return Craft::t('link-it', 'https://domain.co.uk');
    }


    // Public Methods
    // =========================================================================

    public function getLabel()
    {
        return $this->typeLabel ?? self::defaultLabel();
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['typeLabel', 'string'];
        return $rules;
    }

    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            $this->_settingsHtmlPath,
            [
                'type' => $this,
            ]
        );
    }

    public function getInputHtml($name)
    {
        return Craft::$app->getView()->renderTemplate(
            $this->_inputHtmlPath,
            [
                'name' => $name,
                'type' => $this,
                'link' => $this->getLink(),
            ]
        );
    }

    public function getLink()
    {
        return null;
        // return new Link();
    }

}
