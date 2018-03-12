<?php
namespace fruitstudios\linkit\models;

use Craft;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\base\ElementLink;

use craft\elements\Category as CraftCategory;

class Category extends ElementLink
{
    // Private
    // =========================================================================

    private $_category;

    // Public
    // =========================================================================

    // Static
    // =========================================================================

    public static function elementType()
    {
        return CraftCategory::class;
    }

    public static function settingsTemplatePath(): string
    {
        return 'linkit/types/settings/_element';
    }

    public static function inputTemplatePath(): string
    {
        return 'linkit/types/input/_element';
    }

    // Public Methods
    // =========================================================================

    public function getUrl(): string
    {
        if(!$this->getCategories())
        {
            return '';
        }
        return $this->getCategories()->getUrl() ?? '';
    }

    public function getText(): string
    {
        if($this->customText != '')
        {
            return $this->customText;
        }
        return $this->getCategories()->title ?? $this->getUrl() ?? '';
    }

    public function getCategories()
    {
        if(is_null($this->_category))
        {
            $this->_category = Craft::$app->getCategories()->getCategoryById((int) $this->value);
        }
        return $this->_category;
    }
}
