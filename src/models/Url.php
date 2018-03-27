<?php
namespace fruitstudios\linkit\models;

use Craft;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\base\Link;
use fruitstudios\linkit\validators\UrlValidator;

class Url extends Link
{
    // Static
    // =========================================================================

    public static function defaultLabel(): string
    {
        return Craft::t('linkit', 'URL');
    }

    public static function defaultPlaceholder(): string
    {
        return Craft::t('linkit', 'https://craftcms.com');
    }

    // Public Methods
    // =========================================================================

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [
            ['value'],
            UrlValidator::class,
            'defaultScheme' => 'http',
            'message' => Craft::t('linkit', 'Please enter a valid url.')
        ];
        return $rules;
    }
}
