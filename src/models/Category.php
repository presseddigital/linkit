<?php
namespace fruitstudios\linkit\models;

use Craft;

use fruitstudios\linkit\Linkit;
use fruitstudios\linkit\base\ElementLink;

use craft\elements\Category as CraftCategory;

class Category extends ElementLink
{
    // Private
    // =========================================================================

    private $_category;

    // Static
    // =========================================================================

    public static function elementType()
    {
        return CraftCategory::class;
    }

    // Public Methods
    // =========================================================================

    public function getCategory()
    {
        if(is_null($this->_category))
        {
            $this->_category = Craft::$app->getCategories()->getCategoryById((int) $this->value, static::elementType(), $this->ownerElement->siteId ?? null);
        }
        return $this->_category;
    }
}
