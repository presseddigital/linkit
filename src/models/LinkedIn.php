<?php

namespace presseddigital\linkit\models;

use Craft;

use presseddigital\linkit\base\Link;
use presseddigital\linkit\validators\UrlValidator;

class LinkedIn extends Link
{
    // Private
    // =========================================================================

    private string $_match = '/^http(?:s)?:\/\/([a-z]{2,3}\\.)?linkedin\\.com\\/.*$/';

    // Static
    // =========================================================================

    public static function group(): string
    {
        return Craft::t('linkit', 'Social');
    }

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

    /**
     * @return mixed[]
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [
            ['value'],
            UrlValidator::class,
            'defaultScheme' => 'https',
            'message' => Craft::t('linkit', 'Please enter a valid url.'),
        ];
        $rules[] = [
            ['value'],
            'match',
            'pattern' => $this->_match,
            'message' => Craft::t('linkit', 'Please enter a valid Linked In link.'),
        ];
        return $rules;
    }
}

class_alias(LinkedIn::class, \fruitstudios\linkit\models\LinkedIn::class);
