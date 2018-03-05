<?php
namespace fruitstudios\linkit\types;

use Craft;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\base\LinkType;

class Email extends LinkType
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
        return Craft::t('link-it', 'Email Address');
    }

    public static function defaultValue(): string
    {
        return Craft::t('link-it', 'email@domain.co.uk');
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

    public function getInputHtml($name)
    {
        return Craft::$app->getView()->renderTemplate(
            $this->_inputHtmlPath,
            [
                'name' => $name,
                'type' => $this,
                'value' => $this->value,
            ]
        );
    }

    public function getLink()
    {
        $link = new Link();
        $link->type = static::class;
        $link->url = $this->value['values'][static::class] ?? '';
        $link->text = $value['customText'] ?? 'The text';
        $link->target = $this->value['target'] ?? null;

        return $link;
    }
}
