<?php
namespace presseddigital\linkit\models;

use presseddigital\linkit\base\ElementLink;

use Craft;
use craft\elements\Category as CraftCategory;

class Category extends ElementLink
{
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
        return $this->getElement();
    }
}

class_alias(Category::class, \fruitstudios\linkit\models\Category::class);
