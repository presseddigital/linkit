<?php
namespace fruitstudios\linkit\models;

use Craft;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\base\LinkType;

use craft\elements\User as CraftUser;

class User extends LinkType
{

     // Private
    // =========================================================================

    private $_elementType = CraftUser::class;
    private $_settingsHtmlPath = 'link-it/types/settings/_user';
    private $_inputHtmlPath = 'link-it/types/input/_element';

    // Public
    // =========================================================================

    // public $value;
    public $typeLabel;
    public $sources = '*';
    public $selectionLabel;
    public $userPath;

    // Static
    // =========================================================================

    // Public Methods
    // =========================================================================

    public function getLabel()
    {
        return $this->typeLabel ?? self::defaultLabel();
    }

    public function getElementType()
    {
        return $this->_elementType;
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['typeLabel', 'string'];
        $rules[] = ['selectionLabel', 'string'];
        $rules[] = ['userPath', 'string'];
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
                'link' => $this->getLink(),
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
