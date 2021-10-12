<?php
namespace presseddigital\linkit\models;

use presseddigital\linkit\base\ElementLink;

use Craft;
use craft\commerce\elements\Product as CraftCommerceProduct;

class Product extends ElementLink
{
    // Static
    // =========================================================================

    public static function elementType()
    {
        return CraftCommerceProduct::class;
    }

    // Public Methods
    // =========================================================================

    public function getProduct()
    {
        return $this->getElement();
    }
}

class_alias(Product::class, \fruitstudios\linkit\models\Product::class);
