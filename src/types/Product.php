<?php
namespace fruitstudios\linkit\types;

use Craft;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\base\LinkType;

// use craft\elements\Product as CraftProduct;

class Product extends LinkType
{

    // Static
    // =========================================================================



    // Public Methods
    // =========================================================================

    public function getLabel()
    {
        if($this->typeLabel != '')
        {
            return $this->typeLabel;
        }
        return static::defaultLabel();
    }

    public function getSettingsHtml()
    {
        return '<p>Product settings here</p>';
    }

    public function getInputHtml($name)
    {
        return '<p>Product settings here</p>';
    }

    public function getLink()
    {
        return null;
        // return new Link();
    }

}
