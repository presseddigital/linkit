<?php
namespace fruitstudios\linkit\base;

use fruitstudios\linkit\helpers\LinkitHelper;

use Craft;
use craft\base\ElementInterface;
use craft\base\SavableComponent;
use craft\helpers\Template as TemplateHelper;

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
        return 'linkit/types/settings/_default';
    }

    public static function inputTemplatePath(): string
    {
        return 'linkit/types/input/_default';
    }

    public static function hasSettings(): bool
    {
        return true;
    }

    public static function elementType()
    {
        return null;
    }

    public static function isElementLink(): bool
    {
        return (static::elementType() ?? false) ? true : false;
    }

    // Public
    // =========================================================================

    public $customLabel;

    public $fieldSettings;
    public $value;
    public $customText;
    public $target;

    // Public Methods
    // =========================================================================

    public function __toString(): string
    {
        return $this->getLink([], false);
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

    public function getSettingsHtml(): string
    {
       return Craft::$app->getView()->renderTemplate(
            static::settingsTemplatePath(),
            [
                'type' => $this,
            ]
        );
    }

    public function getInputHtml(string $name, Link $currentLink = null, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate(
            static::inputTemplatePath(),
            [
                'name' => $name,
                'link' => $this,
                'currentLink' => $currentLink,
                'element' => $element,
            ]
        );
    }

    public function getLink($customAttributes = [], $raw = true)
    {
        $html = LinkitHelper::getLinkHtml($this->getUrl(), $this->text, $this->prepLinkAttributes($customAttributes));
        return $raw ? TemplateHelper::raw($html) : $html;
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

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['customLabel', 'string'];
        // $rules[] = ['value', 'required', 'message' => Craft::t('linkit', 'A {type} is required.', [
        //     'type' => 'value'
        // ])];
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
