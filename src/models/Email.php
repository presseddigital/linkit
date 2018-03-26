<?php
namespace fruitstudios\linkit\models;

use Craft;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\base\Link;

class Email extends Link
{
    // Private
    // =========================================================================

    // Public
    // =========================================================================

    // Static
    // =========================================================================

    public static function defaultLabel(): string
    {
        return Craft::t('linkit', 'Email Address');
    }

    public static function defaultPlaceholder(): string
    {
        return Craft::t('linkit', 'name@domain.com');
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
        $rules[] = ['value', 'email'];
        return $rules;
    }

    // public function validateLinkValue(): bool
    // {
    //     return true;
    // }



    // public function customLinkValue(): bool
    // {


    //     return true;
    // }

}
