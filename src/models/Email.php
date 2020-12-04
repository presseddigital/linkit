<?php
namespace presseddigital\linkit\models;

use Craft;

use presseddigital\linkit\Linkit;
use presseddigital\linkit\base\Link;

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

class_alias(Email::class, \fruitstudios\linkit\models\Email::class);
