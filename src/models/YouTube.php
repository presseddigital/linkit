<?php
namespace fruitstudios\linkit\models;

use Craft;

use fruitstudios\linkit\Linkit;
use fruitstudios\linkit\base\Link;
use fruitstudios\linkit\validators\UrlValidator;

class YouTube extends Link
{
    // Private
    // =========================================================================

    private $_match = '/^http(?:s)?:\/\/(?:www\.)?(youtube\.com|youtu\.?be)\/([a-zA-Z0-9_]+)/';

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
        return Craft::t('linkit', 'https://youtube.com/CraftCMS');
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
            'message' => Craft::t('linkit', 'Please enter a valid YouTube link.')
        ];
        return $rules;
    }
}
