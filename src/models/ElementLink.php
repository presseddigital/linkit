<?php
namespace fruitstudios\linkit\models;

use Craft;
use craft\base\Model;

use fruitstudios\linkit\base\Link as BaseLink;

class Link extends BaseLink
{
    // Public
    // =========================================================================

    public $type;
    public $value;
    public $text;
    public $target;
    private $_element;

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


    }

    public function getUrl()
    {


    }

    public function getText()
    {

    }

    public function getElement()
    {


    }

    public function rules()
    {
        return [
            ['url', 'string'],
            ['text', 'default', 'value' => '#'],
        ];
    }

}
