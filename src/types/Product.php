<?php
namespace fruitstudios\linkit\types;

use Craft;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\base\LinkType;
use fruitstudios\linkit\base\LinkInterface;
use fruitstudios\linkit\models\ElementLink;

use craft\elements\Product as CraftProduct;

class Product extends LinkType
{
    // Private
    // =========================================================================

    private $_elementType = CraftProduct::class;

    // Public
    // =========================================================================

    public $customLabel;
    public $sources = '*';
    public $customSelectionLabel;

    // Static
    // =========================================================================

    public static function settingsTemplatePath(): string
    {
        return 'linkit/types/settings/_element';
    }

    public static function inputTemplatePath(): string
    {
        return 'linkit/types/input/_element';
    }

    // Public Methods
    // =========================================================================

    public function getLabel()
    {
        if($this->customLabel != '')
        {
            return $this->customLabel;
        }
        return static::defaultLabel();
    }

    public function getSelectionLabel()
    {
        if($this->customSelectionLabel != '')
        {
            return $this->customSelectionLabel;
        }
        return static::defaultSelectionLabel();
    }

    public function getElementType()
    {
        return $this->_elementType;
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['customLabel', 'string'];
        $rules[] = ['selectionLabel', 'string'];
        return $rules;
    }

    public function getLink($value): LinkInterface
    {
        $link = new ElementLink();
        $link->setAttributes($value, false);
        return $link;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Normalizes the available sources into select input options.
     *
     * @return array
     */
    public function getSourceOptions(): array
    {
        return LinkIt::$plugin->service->getSourceOptions($this->_elementType);
    }

}
