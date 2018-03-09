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

    // Public
    // =========================================================================

    public $customLabel;

    // Static
    // =========================================================================

    public static function defaultLabel(): string
    {
        return Craft::t('linkit', 'Phone Number');
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

    public function getLink($value): LinkInterface
    {
        $link = new Link();
        $link->setAttributes($value, false);
        return $link;
    }

}
