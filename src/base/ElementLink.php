<?php
namespace presseddigital\linkit\base;

use presseddigital\linkit\Linkit;

use Craft;
use craft\base\Element;

abstract class ElementLink extends Link
{
    // Private
    // =========================================================================

    private $_element;

    // Static
    // =========================================================================

    public static function group(): string
    {
        return Craft::t('linkit', 'Element');
    }

    public static function elementType()
    {
        return Element::class;
    }

    public static function settingsTemplatePath(): string
    {
        return 'linkit/types/settings/element';
    }

    public static function inputTemplatePath(): string
    {
        return 'linkit/types/input/element';
    }

    // Public
    // =========================================================================

    public $sources = '*';
    public $customSelectionLabel;

    // Public Methods
    // =========================================================================

    public function __toString(): string
    {
        return $this->isAvailable() ? $this->getLink([], false) : '';
    }

    public function defaultSelectionLabel(): string
    {
        return Craft::t('linkit', 'Select') . ' ' . $this->defaultLabel();
    }

    public function getSelectionLabel(): string
    {
        if(!is_null($this->customSelectionLabel) && $this->customSelectionLabel != '')
        {
            return $this->customSelectionLabel;
        }
        return $this->defaultSelectionLabel();
    }

    public function getUrl(): string
    {
        if(!$this->getElement())
        {
            return '';
        }
        return $this->getElement()->getUrl() ?? '';
    }

    public function getText(): string
    {
        return $this->getCustomOrDefaultText() ?? $this->getElement()->title ?? $this->getUrl() ?? '';
    }

    public function getElement()
    {

        // Use this to get element everywhere, just use the static type and get elementby id
        // also use do the eager loading stuff here
        //
        // if($this->_element !== null)
        // {
        //     return $this->_element;
        // }

        // Check eager loading

        // IDEA. Could we make the plugin eager load by default just like the data element does, could be a plugin setting?

        // Get the element by id / element type site etc

        if(is_null($this->_element))
        {
            $this->_element = Craft::$app->getElements()->getElementById((int) $this->value, static::elementType(), $this->ownerElement->siteId ?? null);
        }
        return $this->_element;
    }

    public function isAvailable(): bool
    {
        $element = $this->getElement();
        return $element && $element->enabled && $element->enabledForSite;
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['customSelectionLabel', 'string'];
        return $rules;
    }

    public function getSourceOptions(): array
    {
        return Linkit::$plugin->service->getSourceOptions($this->elementType());
    }
}
