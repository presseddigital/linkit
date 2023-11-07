<?php

namespace presseddigital\linkit\base;

use Craft;
use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\base\SavableComponent;
use craft\helpers\Template;
use craft\helpers\Html;
use Twig\Markup;

/**
 * Class Link
 *
 * @property ElementInterface $owner
 */
abstract class Link extends SavableComponent implements LinkInterface, \Stringable
{
    // Static
    // =========================================================================

    public static function group(): string
    {
        return Craft::t('linkit', 'Basic');
    }

    public static function groupTitle(): string
    {
        return static::group() . ' ' . Craft::t('linkit', 'Links');
    }

    public static function defaultLabel(): string
    {
        $classNameParts = explode('\\', static::class);
        return array_pop($classNameParts);
    }

    public static function defaultPlaceholder(): string
    {
        return static::defaultLabel();
    }

    public static function settingsTemplatePath(): string
    {
        return 'linkit/types/settings/text';
    }

    public static function inputTemplatePath(): string
    {
        return 'linkit/types/input/text';
    }

    public static function hasSettings(): bool
    {
        return true;
    }

    // TODO: Check if this need to be here or if it could actually be in the Element Link only
    public static function elementType(): ?string
    {
        return null;
    }

    public static function hasElement(): bool
    {
        return (static::elementType() ?? false) ? true : false;
    }

    // Public
    // =========================================================================

    public ?string $customLabel = null;
    public ?string $customPlaceholder = null;
    public ?string $value = null;
    public ?string $customText = null;
    public ?bool $target = null;
    public ?string $siteId = null;

    public ?array $fieldSettings = null; // TODO: Remove and use field

    // Private
    // =========================================================================

    // Need to pass the element that owns this field to ensure multisite stuff works ok!
    private ?ElementInterface $_owner = null;
    private ?FieldInterface $_field = null;

    // Public Methods
    // =========================================================================

    public function __toString(): string
    {
        return $this->getLink([], false);
    }

    public function setOwner(ElementInterface $owner = null): void
    {
        $this->_owner = $owner;
    }

    public function getOwner(): ElementInterface
    {
        return $this->_owner;
    }

    public function setField(FieldInterface $field = null): void
    {
        $this->_field = $field;
    }

    public function getField(): FieldInterface
    {
        return $this->_field;
    }

    public function extraFields(): array
    {
        $names = parent::extraFields();
        $names[] = 'owner';
        $names[] = 'field';
        return $names;
    }

    public function defaultSelectionLabel(): string
    {
        return Craft::t('linkit', 'Select') . ' ' . static::defaultLabel();
    }

    public function getType(): string
    {
        return $this::class;
    }

    public function getTypeHandle(): string
    {
        $typeParts = explode('\\', $this->type);
        return strtolower(array_pop($typeParts));
    }

    public function getLabel(): string
    {
        if (!is_null($this->customLabel) && $this->customLabel != '') {
            return $this->customLabel;
        }
        return static::defaultLabel();
    }

    public function getSelectionLabel(): string
    {
        return $this->defaultSelectionLabel();
    }

    public function getPlaceholder(): string
    {
        if (!is_null($this->customPlaceholder) && $this->customPlaceholder != '') {
            return $this->customPlaceholder;
        }
        return static::defaultPlaceholder();
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
            static::settingsTemplatePath(),
            [
                'type' => $this,
            ]
        );
    }

    public function getInputHtml(string $name, $field, Link $currentLink = null, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate(
            static::inputTemplatePath(),
            [
                'name' => $name,
                'link' => $this,
                'currentLink' => $currentLink,
                'element' => $element,
                'field' => $field,
            ]
        );
    }

    public function getLink(array $attributes = [], bool $raw = true, bool $preview = false): Markup|string
    {
        if (!$preview && !$this->isAvailable()) {
            return '';
        }

        $link = Html::tag('a', $this->getText(), array_merge(
            $this->getBaseLinkAttributes(),
            $attributes,
            [
                'href' => $this->getUrl(),
            ]
        ));
        return $raw ? Template::raw($link) : $link;
    }

    public function getLinkPreview(array $attributes = [])
    {
        return $this->getLink($attributes, false, true);
    }

    public function getUrl(): string
    {
        return (string) $this->value;
    }

    public function getText(): string
    {
        if ($this->fieldSettings['allowCustomText'] && $this->customText != '') {
            return $this->customText;
        }
        return $this->fieldSettings['defaultText'] != '' ? $this->fieldSettings['defaultText'] : $this->value ?? '';
    }

    public function getBaseLinkAttributes(): array
    {
        $attributes = [];
        if ($this->fieldSettings['allowTarget'] && $this->target) {
            // Target="_blank" - the most underestimated vulnerability ever
            // https://www.jitbit.com/alexblog/256-targetblank---the-most-underestimated-vulnerability-ever/
            $attributes['target'] = '_blank';
            $attributes['rel'] = 'noopener noreferrer';
        }
        return $attributes;
    }

    public function getTargetString(): string
    {
        return $this->fieldSettings['allowTarget'] && $this->target ? '_blank' : '_self';
    }

    public function isAvailable(): bool
    {
        return $this->value && $this->value != '';
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['customLabel', 'customPlaceholder', 'value', 'customText'], 'string'];
        $rules[] = [['target'], 'boolean'];
        return $rules;
    }

    public function validateLinkValue(): bool
    {
        return true;
    }

    // Protected Methods
    // =========================================================================

    protected function getCustomOrDefaultText()
    {
        if ($this->fieldSettings['allowCustomText'] && $this->customText != '') {
            return $this->customText;
        }

        if ($this->fieldSettings['defaultText'] && $this->fieldSettings['defaultText'] != '') {
            return $this->fieldSettings['defaultText'];
        }

        return null;
    }
}
