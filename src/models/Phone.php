<?php
namespace fruitstudios\linkit\models;

use Craft;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\base\Link;

class Phone extends Link
{
    // Private
    // =========================================================================

    // Public
    // =========================================================================

    // Static
    // =========================================================================

    public static function defaultLabel(): string
    {
        return Craft::t('linkit', 'Phone Number');
    }

    // Public Methods
    // =========================================================================

    public function getUrl(): string
    {
        return (string) 'tel:'.$this->value;
    }

}
