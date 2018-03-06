<?php
namespace fruitstudios\linkit\types;

use Craft;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\base\LinkType;
use fruitstudios\linkit\base\LinkInterface;
use fruitstudios\linkit\models\Link;


class Phone extends LinkType
{
    // Private
    // =========================================================================

    private $_settingsHtmlPath = 'link-it/types/settings/_default';
    private $_inputHtmlPath = 'link-it/types/input/_default';

    // Public
    // =========================================================================

    public $customLabel;

    // Static
    // =========================================================================

    public static function defaultLabel(): string
    {
        return Craft::t('link-it', 'Phone Number');
    }

    public static function defaultValue(): string
    {
        return Craft::t('link-it', '+44 00000 000000');
    }

    // Public Methods
    // =========================================================================

    public function getLabel()
    {
        if($this->customLabel != '')
        {
            return $this->customLabel;
        }
        return static::defaultLabel();
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['customLabel', 'string'];
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

    public function getInputHtml($name, LinkInterface $link = null)
    {
        return Craft::$app->getView()->renderTemplate(
            $this->_inputHtmlPath,
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
