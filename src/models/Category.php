<?php

namespace presseddigital\linkit\models;

use craft\elements\Category as CraftCategory;

use presseddigital\linkit\base\ElementLink;

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
