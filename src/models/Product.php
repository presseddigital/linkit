<?php
namespace fruitstudios\linkit\models;

use Craft;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\base\ElementLink;

use craft\commerce\Plugin;
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

    public function getText(): string
    {
        if($this->customText != '')
        {
            return $this->customText;
        }
        return $this->getProduct()->title ?? $this->getUrl() ?? '';
    }

    public function getProduct()
    {
        if(is_null($this->_product))
        {
            $this->_product = Plugin::getInstance()->getProducts()->getProductById((int) $this->productId);
        }
        return $this->_product;
    }
}
