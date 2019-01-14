<?php
namespace fruitstudios\linkit\models;

use Craft;

use fruitstudios\linkit\Linkit;
use fruitstudios\linkit\base\Link;
use fruitstudios\linkit\validators\UrlValidator;

class Url extends Link
{
    // Public
    // =========================================================================

    public $allowAlias = true;
    public $allowHash = true;
    public $allowPaths = true;
    public $allowMailto = true;

    // Static
    // =========================================================================

    public static function defaultLabel(): string
    {
        return Craft::t('linkit', 'URL');
    }

    public static function settingsTemplatePath(): string
    {
        return 'linkit/types/settings/url';
    }

    public static function defaultPlaceholder(): string
    {
        return Craft::t('linkit', 'https://craftcms.com');
    }

    // Public Methods
    // =========================================================================

    public function getUrl(): string
    {
        return (string) Craft::getAlias($this->value);
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['allowAlias','allowHash','allowPaths','allowMailto'], 'boolean'];
        $rules[] = [['allowAlias','allowHash','allowPaths','allowMailto'], 'default', 'value' => true];
        $rules[] = [
            ['value'],
            UrlValidator::class,
            'defaultScheme' => 'http',
            'allowAlias' => $this->allowAlias,
            'allowHash' => $this->allowHash,
            'allowPaths' => $this->allowPaths,
            'allowMailto' => $this->allowMailto,
            'message' => Craft::t('linkit', 'Please enter a valid url.')
        ];
        return $rules;
    }
}
