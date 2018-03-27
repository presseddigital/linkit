<?php
namespace fruitstudios\linkit\models;

use Craft;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\base\Link;

class Email extends Link
{
    // Static
    // =========================================================================

    public static function defaultLabel(): string
    {
        return Craft::t('linkit', 'Email Address');
    }

    public static function defaultPlaceholder(): string
    {
        return Craft::t('linkit', 'support@craftcms.com');
    }

    // Public Methods
    // =========================================================================

    public function getUrl(): string
    {
        return (string) 'mailto:'.$this->value;
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['value', 'email', 'message' => Craft::t('linkit', 'Please enter a valid email address.')];
        return $rules;
    }

}
