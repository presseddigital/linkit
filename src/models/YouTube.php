<?php

namespace presseddigital\linkit\models;

use Craft;

use presseddigital\linkit\base\Link;
use presseddigital\linkit\validators\UrlValidator;

class YouTube extends Link
{
    // Private
    // =========================================================================

    private string $_match = '/^http(?:s)?:\/\/(?:www\.)?(youtube\.com|youtu\.?be)\/([a-zA-Z0-9_]+)/';

    // Static
    // =========================================================================

    public static function group(): string
    {
        return Craft::t('linkit', 'Social');
    }

    public static function defaultLabel(): string
    {
        return Craft::t('linkit', 'YouTube');
    }

    public static function defaultPlaceholder(): string
    {
        return Craft::t('linkit', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');
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
            'message' => Craft::t('linkit', 'Please enter a valid {type} link.', [ 'type' => static::defaultLabel() ]),
        ];
        return $rules;
    }
}

class_alias(YouTube::class, \fruitstudios\linkit\models\YouTube::class);
