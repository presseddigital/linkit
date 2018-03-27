<?php
namespace fruitstudios\linkit\base;

use fruitstudios\linkit\helpers\LinkItHelper;

use Craft;
use craft\base\SavableComponent;
use craft\helpers\Template as TemplateHelper;

abstract class Link extends SavableComponent implements LinkInterface
{
    // Static
    // =========================================================================

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

    public function getInputHtml(string $name, Link $currentLink = null): string
    {
        return Craft::$app->getView()->renderTemplate(
            static::inputTemplatePath(),
            [
                'name' => $name,
                'link' => $this,
                'currentLink' => $currentLink,
            ]
        );
    }

    public function getLink($customAttributes = [], $raw = true)
    {
        $html = LinkItHelper::getLinkHtml($this->getUrl(), $this->text, $this->prepLinkAttributes($customAttributes));
        return $raw ? TemplateHelper::raw($html) : $html;
    }

    public function getUrl(): string
    {
        return (string) $this->value;
    }

    public function getText(): string
    {
        if($this->customText != '')
        {
            return $this->customText;
        }
        return $this->fieldSettings['defaultText'] ?? $this->getUrl() ?? '';
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
        $attributes = [];
        if($this->target)
        {
            // Target="_blank" - the most underestimated vulnerability ever
            // https://www.jitbit.com/alexblog/256-targetblank---the-most-underestimated-vulnerability-ever/
            $attributes['target'] = '_blank';
            $attributes['rel'] = 'noopener noreferrer';
        }
        return array_merge($attributes, $customAttributes);;
    }
}
