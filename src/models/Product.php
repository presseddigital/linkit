<?php
namespace fruitstudios\linkit\models;

use Craft;

use fruitstudios\linkit\Linkit;
use fruitstudios\linkit\base\ElementLink;

use craft\commerce\Plugin as CraftCommercePlugin;
use craft\commerce\elements\Product as CraftCommerceProduct;

class Product extends ElementLink
{
    // Private
    // =========================================================================

    private $_product;

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
        if(is_null($this->_product))
        {
            $this->_product = CraftCommercePlugin::getInstance()->getProducts()->getProductById((int) $this->value, $this->ownerElement->siteId ?? null);
        }
        return $this->_product;
    }
}
