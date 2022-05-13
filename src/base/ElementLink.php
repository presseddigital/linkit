<?php

namespace presseddigital\linkit\base;

use Craft;
use craft\base\ElementInterface;
use craft\base\Element;
use craft\helpers\ArrayHelper;

use presseddigital\linkit\Linkit;

abstract class ElementLink extends Link implements \Stringable
{
    // Private
    // =========================================================================
    /**
     * @var mixed|null
     */
    private mixed $_element = null;

    // Static
    // =========================================================================

    public static function group(): string
    {
        return Craft::t('linkit', 'Element');
    }

    public static function elementType(): ?string
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

    public string|array|null $sources = '*';
    public ?string $customSelectionLabel = null;

    // Public Methods
    // =========================================================================

    public function __toString(): string
    {
        return $this->isAvailable() ? $this->getLink([], false) : '';
    }

    public function defaultSelectionLabel(): string
    {
        return Craft::t('linkit', 'Select') . ' ' . static::defaultLabel();
    }

    public function getSelectionLabel(): string
    {
        if (null !== $this->customSelectionLabel && $this->customSelectionLabel != '') {
            return $this->customSelectionLabel;
        }
        return $this->defaultSelectionLabel();
    }

    public function getUrl(): string
    {
        return $this->getElement()->getUrl() ?? '';
    }

    public function getText(): string
    {
        return $this->getCustomOrDefaultText() ?? $this->getElement()->title ?? $this->getUrl() ?? '';
    }

    public function getElement(): ?ElementInterface
    {
        if ($this->_element !== null) {
            return $this->_element;
        }

        // Check eager loading
        if ($this->owner) {
            $eagerLoadingHandle = $this->getField()->handle . '.' . $this->getTypeHandle();
            if ($this->owner->hasEagerLoadedElements($eagerLoadingHandle)) {
                $elements = $this->owner->getEagerLoadedElements($eagerLoadingHandle);
                return $this->_element = ArrayHelper::firstValue($elements);
            }
        }

        return $this->_element = Craft::$app->getElements()->getElementById((int) $this->value, static::elementType(), $this->owner->siteId ?? null);
    }

    public function isAvailable(): bool
    {
        $element = $this->getElement();
        return $element && $element->enabled && $element->enabledForSite;
    }

    /**
     * @return mixed[]
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = ['customSelectionLabel', 'string'];
        return $rules;
    }

    /**
     * @return mixed[]
     */
    public function getSourceOptions(): array
    {
        return Linkit::$plugin->service->getSourceOptions(static::elementType());
    }
}
