<?php

namespace presseddigital\linkit\models;

use craft\base\ElementInterface;
use craft\commerce\elements\Product as CraftCommerceProduct;
use presseddigital\linkit\base\ElementLink;

class Product extends ElementLink
{
    // Static
    // =========================================================================

    public static function elementType(): ?string
    {
        return CraftCommerceProduct::class;
    }

    // Public Methods
    // =========================================================================

    public function getProduct(): ?ElementInterface
    {
        return $this->getElement();
    }
}

class_alias(Product::class, \fruitstudios\linkit\models\Product::class);
