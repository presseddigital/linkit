<?php
namespace fruitstudios\linkit\models;

use Craft;

use fruitstudios\linkit\Linkit;
use fruitstudios\linkit\base\Link;

class LinkedIn extends Link
{
    // Private
    // =========================================================================

    private $_match = '/^http(?:s)?:\/\/[a-z]{2,3}\\.linkedin\\.com\\/.*$/';

    // Static
    // =========================================================================

    public static function defaultLabel(): string
    {
        return Craft::t('linkit', 'Linked In');
    }

    public static function defaultPlaceholder(): string
    {
        return Craft::t('linkit', 'https://www.linkedin.com/in/username');
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
            'message' => Craft::t('linkit', 'Please enter a valid Linked In link.')
        ];
        return $rules;
    }
}
