<?php

namespace presseddigital\linkit\base;

use Craft;
use craft\base\Element;
use craft\helpers\ArrayHelper;

use presseddigital\linkit\Linkit;

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
        if (!is_null($this->customSelectionLabel) && $this->customSelectionLabel != '') {
            return $this->customSelectionLabel;
        }
        return $this->defaultSelectionLabel();
    }

    public function getUrl(): string
    {
        $element = $this->getElement();
        if(!$element) return '';
        return $element->getUrl() ?? '';
    }

    public function getText(): string
    {
        return $this->getCustomOrDefaultText() ?? ($this->getElement() ? $this->getElement()->title : null) ?? $this->getUrl() ?? '';
    }

    public function getElement()
    {
        if ($this->_element !== null) {
            return $this->_element;
        }

        $siteId = $this->elementSiteId ?? null;

        // Check eager loading
        if ($this->owner) {
            $eagerLoadingHandle = $this->getField()->handle . '.' . $this->getTypeHandle();
            if ($this->owner->hasEagerLoadedElements($eagerLoadingHandle)) {
                $elements = $this->owner->getEagerLoadedElements($eagerLoadingHandle);
                if($element = ArrayHelper::firstValue($elements)) {
                    return $this->_element = $element;
                }
            }
        }

        return $this->_element = Craft::$app->getElements()->getElementById((int) $this->value, static::elementType(), $siteId);
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
