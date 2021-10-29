<?php
namespace presseddigital\linkit\base;

use presseddigital\linkit\helpers\LinkitHelper;

use Craft;
use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\base\SavableComponent;
use craft\helpers\Template as TemplateHelper;

/**
 * Class Link
 *
 * @property ElementInterface $owner
 */
abstract class Link extends SavableComponent implements LinkInterface
{
    // Static
    // =========================================================================

    public static function group(): string
    {
        return Craft::t('linkit', 'Basic');
    }

    public static function groupTitle(): string
    {
        return static::group().' '.Craft::t('linkit', 'Links');
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

    public static function elementType()
    {
        return null;
    }

    public static function hasElement(): bool
    {
        return (static::elementType() ?? false) ? true : false;
    }

    // Public
    // =========================================================================

    public $customLabel;
    public $customPlaceholder;

    public $fieldSettings;
    public $value;
    public $customText;
    public $target;

    // Private
    // =========================================================================

    // Need to pass the element that owns this field to ensure multisite stuff works ok!
    private $_owner;
    private $_field;

    // Public Methods
    // =========================================================================

    public function __toString(): string
    {
        return $this->getLink([], false);
    }

    public function setOwner(ElementInterface $owner = null)
    {
        $this->_owner = $owner;
    }

    public function getOwner(): ElementInterface
    {
        return $this->_owner;
    }

    public function setField(FieldInterface $field = null)
    {
        $this->_field = $field;
    }

    public function getField(): FieldInterface
    {
        return $this->_field;
    }

    public function extraFields()
    {
        $names = parent::extraFields();
        $names[] = 'owner';
        $names[] = 'field';
        return $names;
    }

    public function defaultSelectionLabel(): string
    {
        return Craft::t('linkit', 'Select') . ' ' . $this->defaultLabel();
    }

    public function getType(): string
    {
        return get_class($this);
    }

    public function getTypeHandle(): string
    {
        $typeParts = explode('\\', $this->type);
        return strtolower(array_pop($typeParts));
    }

    public function getLabel(): string
    {
        if(!is_null($this->customLabel) && $this->customLabel != '')
        {
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
        if(!is_null($this->customPlaceholder) && $this->customPlaceholder != '')
        {
            return $this->customPlaceholder;
        }
        return static::defaultPlaceholder();
    }

    public function getSettingsHtml(): string
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
        return Craft::$app->getView()->renderTemplate(static::inputTemplatePath(), [
            'name' => $name,
            'link' => $this,
            'currentLink' => $currentLink,
            'element' => $element,
            'field' => $field,
        ]);
    }

    public function getLink($customAttributes = [], $raw = true, $preview = false)
    {
        if(!$preview && !$this->isAvailable())
        {
            return '';
        }

        $html = LinkitHelper::getLinkHtml($this->getUrl(), $this->text, $this->prepLinkAttributes($customAttributes));
        return $raw ? TemplateHelper::raw($html) : $html;
    }

    public function getLinkPreview($customAttributes = [])
    {
        return $this->getLink($customAttributes, false, true);
    }

    public function getUrl(): string
    {
        return (string) $this->value;
    }

    public function getText(): string
    {
        if($this->fieldSettings['allowCustomText'] && $this->customText != '')
        {
            return $this->customText;
        }
        return $this->fieldSettings['defaultText'] != '' ? $this->fieldSettings['defaultText'] : $this->value ?? '';
    }

    public function getLinkAttributes(): array
    {
        $attributes = [];
        if($this->fieldSettings['allowTarget'] && $this->target)
        {
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

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['customLabel', 'string'];
        return $rules;
    }

    public function validateLinkValue(): bool
    {
        return true;
    }

    // Protected Methods
    // =========================================================================

    protected function prepLinkAttributes($customAttributes = []): array
    {
        return array_merge($this->getLinkAttributes(), $customAttributes);;
    }

    protected function getCustomOrDefaultText()
    {
        if($this->fieldSettings['allowCustomText'] && $this->customText != '')
        {
            return $this->customText;
        }

        if($this->fieldSettings['defaultText'] && $this->fieldSettings['defaultText'] != '')
        {
            return $this->fieldSettings['defaultText'];
        }

        return null;
    }
}
