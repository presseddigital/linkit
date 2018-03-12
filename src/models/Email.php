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

    // Public Methods
    // =========================================================================

    public function getUrl(): string
    {
        return (string) 'mailto:'.$this->value;
    }

}
