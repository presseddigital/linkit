<?php
namespace fruitstudios\linkit\types;

use Craft;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\base\LinkType;
use fruitstudios\linkit\base\LinkInterface;
use fruitstudios\linkit\models\ElementLink;

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

    public function getInputHtml($name, LinkInterface $link = null)
    {
        return Craft::$app->getView()->renderTemplate(
            $this->_inputHtmlPath,
            [
                'name' => $name,
                'type' => $this,
                'link' => $link,
            ]
        );
    }

    public function getLink($value): LinkInterface
    {
        $link = new ElementLink();
        $link->setAttributes($value, false);
        return $link;
    }
}
