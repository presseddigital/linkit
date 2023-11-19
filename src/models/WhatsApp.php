<?php

namespace presseddigital\linkit\models;

use Craft;

use presseddigital\linkit\base\Link;
use presseddigital\linkit\validators\UrlValidator;

class WhatsApp extends Link
{
    // Private
    // =========================================================================

    private string $_match = '/^http(?:s)?:\/\/(?:api\.)?whatsapp\.com\/send\?phone=([0-9_]+)/';

    // Static
    // =========================================================================

    public static function group(): string
    {
        return Craft::t('linkit', 'Social');
    }

    public static function defaultLabel(): string
    {
        return Craft::t('linkit', 'WhatsApp');
    }

    public static function defaultPlaceholder(): string
    {
        return Craft::t('linkit', 'https://api.whatsapp.com/send?phone=NUMBER-INCL-COUNTRY-CODE');
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

class_alias(WhatsApp::class, \fruitstudios\linkit\models\WhatsApp::class);
