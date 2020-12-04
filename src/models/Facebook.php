<?php
namespace presseddigital\linkit\models;

use Craft;

use presseddigital\linkit\Linkit;
use presseddigital\linkit\base\Link;
use presseddigital\linkit\validators\UrlValidator;

class Facebook extends Link
{
    // Private
    // =========================================================================

    private $_match = '/(?:(?:http|https):\/\/)?(?:www.)?facebook.com\/(?:(?:\w)*#!\/)?(?:pages\/)?(?:[?\w\-]*\/)?(?:profile.php\?id=(?=\d.*))?([\w\-]*)?/';

    // Static
    // =========================================================================

    public static function group(): string
    {
        return Craft::t('linkit', 'Social');
    }

    public static function defaultLabel(): string
    {
        return Craft::t('linkit', 'Facebook');
    }

    public static function defaultPlaceholder(): string
    {
        return Craft::t('linkit', 'https://www.facebook.com/craftcms');
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
            UrlValidator::class,
            'defaultScheme' => 'https',
            'message' => Craft::t('linkit', 'Please enter a valid url.')
        ];
        $rules[] = [
            ['value'],
            'match',
            'pattern' => $this->_match,
            'message' => Craft::t('linkit', 'Please enter a valid Facebook link.')
        ];
        return $rules;
    }
}

class_alias(Facebook::class, \fruitstudios\linkit\models\Facebook::class);
