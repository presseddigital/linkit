<?php
namespace fruitstudios\linkit\models;

use Craft;
use craft\base\Model;

use fruitstudios\linkit\base\Link;
use fruitstudios\linkit\helpers\LinkItHelper;

class ElementLink extends Link
{
    // Public
    // =========================================================================

    public $_element;

    // Public Methods
    // =========================================================================

    public function __toString(): string
    {
        return (string) $this->getLink();
    }

    public function getLinkType()
    {

    }

    public function getLink()
    {
        return LinkItHelper::getLinkHtml($this->getUrl(), $this->text, $this->getAttributes());
    }

    public function getUrl()
    {
        return $this->_element->getUrl() ?? '';
    }

    public function getElement()
    {
        if(is_null($this->_element))
        {
            $this->_element = Craft::$app->getElements()->getElementById((int) $this->value);
        }
        return $this->_element;
    }

}
