<?php
namespace fruitstudios\linkit\models;

use Craft;

use fruitstudios\linkit\Linkit;
use fruitstudios\linkit\base\Link;

class Twitter extends Link
{
    // Private
    // =========================================================================

    private $_match = '/^http(?:s)?:\/\/(?:www\.)?twitter\.com\/([a-zA-Z0-9_]+)/';

    // Static
    // =========================================================================

    public static function defaultLabel(): string
    {
        return Craft::t('linkit', 'Twitter');
    }

    public static function defaultPlaceholder(): string
    {
        return Craft::t('linkit', 'https://twitter.com/CraftCMS');
    }

    // Public Methods
    // =========================================================================

    public function getUrl(): string
    {
        return (string) $this->value;
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [
            ['value'],
            'match',
            'pattern' => $this->_match,
            'message' => Craft::t('linkit', 'Please enter a valid twitter link.')
        ];
        return $rules;
    }
}
