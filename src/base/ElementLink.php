<?php
namespace fruitstudios\linkit\base;

use fruitstudios\linkit\Linkit;

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
