<?php

namespace presseddigital\linkit\models;

use craft\base\ElementInterface;
use craft\elements\Category as CraftCategory;
use presseddigital\linkit\base\ElementLink;

class Category extends ElementLink
{
    // Static
    // =========================================================================

    public static function elementType(): ?string
    {
        return CraftCategory::class;
    }

    // Public Methods
    // =========================================================================

    public function getCategory(): ?ElementInterface
    {
        return $this->getElement();
    }
}

class_alias(Category::class, \fruitstudios\linkit\models\Category::class);
