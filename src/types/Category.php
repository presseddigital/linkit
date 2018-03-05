<?php
namespace fruitstudios\linkit\types;

use Craft;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\base\LinkType;

use craft\elements\Category as CraftCategory;

class Category extends LinkType
{
    // Private
    // =========================================================================

    private $_elementType = CraftCategory::class;
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


    public function getSettingsHtml()
    {
       return Craft::$app->getView()->renderTemplate(
            $this->_settingsHtmlPath,
            [
                'type' => $this,
            ]
        );
    }

    public function getInputHtml($name)
    {
        return Craft::$app->getView()->renderTemplate(
            $this->_inputHtmlPath,
            [
                'name' => $name,
                'type' => $this,
                'value' => $this->value,
            ]
        );
    }

    public function getLink()
    {
        return null;
        // return new Link();
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
