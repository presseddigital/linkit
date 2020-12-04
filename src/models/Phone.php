<?php
namespace presseddigital\linkit\models;

use Craft;

use presseddigital\linkit\Linkit;
use presseddigital\linkit\base\Link;

class Phone extends Link
{
    // Private
    // =========================================================================

    private $_match = '/^(?:\+\d{1,3}|0\d{1,3}|00\d{1,2})?(?:\s?\(\d+\))?(?:[-\/\s.]|\d)+$/';

    // Static
    // =========================================================================

    public static function defaultLabel(): string
    {
        return Craft::t('linkit', 'Phone Number');
    }

    public static function defaultPlaceholder(): string
    {
        return Craft::t('linkit', '+44(0)0000 000000');
    }

    // Public Methods
    // =========================================================================

    public function getUrl(): string
    {
        return (string) 'tel:'.$this->value;
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [
            ['value'],
            'match',
            'pattern' => $this->_match,
            'message' => Craft::t('linkit', 'Please enter a valid phone number.')
        ];
        return $rules;
    }
}

class_alias(Phone::class, \fruitstudios\linkit\models\Phone::class);
