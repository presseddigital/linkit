<?php
namespace presseddigital\linkit\models;

use Craft;

use presseddigital\linkit\Linkit;
use presseddigital\linkit\base\ElementLink;

use craft\elements\User as CraftUser;

class User extends ElementLink
{
    // Private
    // =========================================================================

    private $_user;

    // Public
    // =========================================================================

    public $userPath;

    // Static
    // =========================================================================

    public static function elementType()
    {
        return CraftUser::class;
    }

    public static function settingsTemplatePath(): string
    {
        return 'linkit/types/settings/user';
    }

    public static function defaultUserPath(): string
    {
        return 'user/{id}';
    }

    // Public Methods
    // =========================================================================

    public function getUrl(): string
    {
        if($this->getUser())
        {
            $userPath = static::defaultUserPath();
            if($this->userPath && $this->userPath != '')
            {
                $userPath = $this->userPath;
            }

            return Craft::$app->getView()->renderObjectTemplate($userPath, $this->getUser());
        }
        return '';
    }

    public function getText(): string
    {
        return $this->getCustomOrDefaultText() ?? $this->getUser()->fullName ?? $this->getUser()->name ?? $this->getUrl() ?? '';
    }

    public function getUser()
    {
        if(is_null($this->_user))
        {
            $this->_user = Craft::$app->getUsers()->getUserById((int) $this->value, $this->ownerElement->siteId ?? null);
        }
        return $this->_user;
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['userPath', 'string'];
        return $rules;
    }
}

class_alias(User::class, \fruitstudios\linkit\models\User::class);
