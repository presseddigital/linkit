<?php
namespace fruitstudios\linkit\types;

use Craft;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\base\LinkType;
use fruitstudios\linkit\base\LinkInterface;
use fruitstudios\linkit\models\ElementLink;

use craft\elements\Asset as CraftAsset;

class Asset extends LinkType
{
    // Private
    // =========================================================================

    private $_elementType = CraftAsset::class;
    private $_settingsHtmlPath = 'link-it/types/settings/_element';
    private $_inputHtmlPath = 'link-it/types/input/_element';

    // Public
    // =========================================================================

    public $customLabel;
    public $sources = '*';
    public $customSelectionLabel;

    // Static
    // =========================================================================

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
        $rules[] = ['customSelectionLabel', 'string'];
        return $rules;
    }


    public function getSettingsHtml()
    {
       return Craft::$app->getView()->renderTemplate(
            $this->_settingsHtmlPath,
            [
                'type' => $this,
            ]
        );
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
