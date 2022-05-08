<?php

namespace presseddigital\linkit\fields;

use Craft;
use craft\base\EagerLoadingFieldInterface;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\helpers\Json as JsonHelper;
use craft\validators\ArrayValidator;

use presseddigital\linkit\Linkit;
use presseddigital\linkit\assetbundles\field\FieldAssetBundle;
use presseddigital\linkit\assetbundles\fieldsettings\FieldSettingsAssetBundle;
use presseddigital\linkit\base\Link;
use presseddigital\linkit\gql\types\generators\LinkitGenerator;
use presseddigital\linkit\models\Asset;
use presseddigital\linkit\models\Category;
use presseddigital\linkit\models\Email;
use presseddigital\linkit\models\Entry;
use presseddigital\linkit\models\Phone;
use presseddigital\linkit\models\Product;
use presseddigital\linkit\models\Url;

use yii\base\ErrorException;
use yii\db\Schema;

class LinkitField extends Field implements PreviewableFieldInterface, EagerLoadingFieldInterface
{
    // Constants
    // =========================================================================

    /**
     * @event RegisterComponentTypesEvent The event that is triggered when registering field types.
     */
    public const EVENT_REGISTER_LINKIT_LINK_TYPES = 'registerLinkitLinkTypes';

    // Private Properties
    // =========================================================================
    /**
     * @var mixed[]|null
     */
    private ?array $_availableLinkTypes = null;
    /**
     * @var mixed[]|null
     */
    private ?array $_enabledLinkTypes = null;
    private string $_columnType = Schema::TYPE_TEXT;


    //  Properties
    // =========================================================================

    public string $selectLinkText = '';
    public $types;
    public $allowCustomText;
    public $defaultText;
    public $allowTarget;

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('linkit', 'Linkit');
    }

    public static function defaultSelectLinkText(): string
    {
        return Craft::t('linkit', 'Select link type...');
    }

    // Public Methods
    // =========================================================================

    public function __construct($config = [])
    {
        // Handle field settings for installations prior to 1.2.0
        if (array_key_exists('types', $config)) {
            $types = [];
            foreach ($config['types'] as $typeClass => $typeSettings) {
                $types[str_replace('fruitstudios', 'presseddigital', $typeClass)] = $typeSettings;
            }
            $config['types'] = $types;
        }

        parent::__construct($config);
    }

    /**
     * Returns an array that maps source-to-target element IDs based on this custom field.
     *
     * This method aids in the eager-loading of elements when performing an element query. The returned array should
     * contain the following keys:
     * - `elementType` – the fully qualified class name of the element type that should be eager-loaded
     * - `map` – an array of element ID mappings, where each element is a sub-array with `source` and `target` keys.
     * - `criteria` *(optional)* – Any criteria parameters that should be applied to the element query when fetching the eager-loaded elements.
     *
     * @param ElementInterface[] $sourceElements An array of the source elements
     * @return array|false|null The eager-loading element ID mappings, false if no mappings exist, or null if the result
     * should be ignored.
     */
    public function getEagerLoadingMap(array $sourceElements): void
    {
    }


    /**
     * @return mixed[]
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['types'], ArrayValidator::class, 'min' => 1, 'tooFew' => Craft::t('linkit', 'You must select at least one link type.'), 'skipOnEmpty' => false];
        return $rules;
    }

    public function getContentColumnType(): array|string
    {
        return $this->_columnType;
    }

    public function getContentGqlType(): \craft\gql\base\ObjectType
    {
        return LinkitGenerator::generateType($this);
    }

    public static function hasContentColumn(): bool
    {
        return true;
    }

    public function normalizeValue(mixed $value, ?\craft\base\ElementInterface $element = null): mixed
    {
        if ($value instanceof Link) {
            return $value;
        }

        if (is_string($value)) {
            $value = JsonHelper::decodeIfJson($value);
        }

        // Handle any Craft2 content
        if (!isset($value['value'])) {
            $value = $this->_normalizeValueCraft2($value);
        }

        if (isset($value['type']) && $value['type'] != '') {
            if (isset($value['value']) && $value['value'] == '') {
                return null;
            }

            if (isset($value['values'])) {
                $postedValue = $value['values'][$value['type']] ?? '';
                $value['value'] = is_array($postedValue) ? $postedValue[0] : $postedValue;
                unset($value['values']);
            }

            $link = $this->_getLinkTypeModelByType($value['type']);
            unset($value['type']);
            $link->setAttributes($value, false); // TODO: Get Rules added for these and remove false
            // Craft::configure($link, $value); // Want to use but only if we can confirm that we can't get passed invalid attributes here
            $link->setOwner($element);
            return $link;
        }

        return null;
    }

    public function serializeValue(mixed $value, ?\craft\base\ElementInterface $element = null): mixed
    {
        $serialized = [];
        if ($value instanceof Link) {
            $serialized = [
                'type' => $value->type,
                'value' => $value->value,
                'customText' => $value->customText,
                'target' => $value->target,
            ];
        }

        return parent::serializeValue($serialized, $element);
    }

    public function getSettingsHtml(): ?string
    {
        $view = Craft::$app->getView();
        $view->registerAssetBundle(FieldSettingsAssetBundle::class);

        return $view->renderTemplate('linkit/fields/_settings', [
            'field' => $this,
        ]);
    }

    public function getInputHtml(mixed $value, ?\craft\base\ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();

        // Register our asset bundle
        $view->registerAssetBundle(FieldAssetBundle::class);

        // Get our id and namespace
        $id = $view->formatInputId($this->handle);
        $namespacedId = $view->namespaceInputId($id);

        // Javascript
        $jsVariables = JsonHelper::encode([
            'id' => $namespacedId,
            'name' => $this->handle,
        ]);
        $view->registerJs('new Garnish.LinkitField(' . $jsVariables . ');');

        // Render the input template
        return $view->renderTemplate(
            'linkit/fields/_input',
            [
                'id' => $id,
                'name' => $this->handle,
                'field' => $this,
                'element' => $element,
                'currentLink' => $value,
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getElementValidationRules(): array
    {
        return ['validateLinkValue'];
    }

    public function isValueEmpty(mixed $value, ElementInterface $element): bool
    {
        return empty($value->value ?? '');
    }

    public function validateLinkValue(ElementInterface $element): void
    {
        $fieldValue = $element->getFieldValue($this->handle);
        if ($fieldValue && !$fieldValue->validate()) {
            $element->addModelErrors($fieldValue, $this->handle);
        }
    }

    public function getSearchKeywords(mixed $value, ElementInterface $element): string
    {
        if ($value instanceof Link) {
            return $value->getText();
        }
        return '';
    }

    public function getTableAttributeHtml(mixed $value, ElementInterface $element): string
    {
        if ($value instanceof Link) {
            return '<span title="Link ' . ($value->isAvailable() ? 'Enabled' : 'Disabled') . '" class="status ' . ($value->isAvailable() ? 'enabled' : 'disabled') . '"></span>' . $value->getLinkPreview();
        }
        return '';
    }

    public function getAvailableLinkTypes()
    {
        if (null !== $this->_availableLinkTypes) {
            return $this->_availableLinkTypes;
        }

        $linkTypes = [];
        foreach (Linkit::$plugin->service->getAvailableLinkTypes() as $linkType) {
            $linkTypes[] = $this->_populateLinkTypeModel($linkType);
        }

        return $this->_availableLinkTypes = $linkTypes;
    }

    public function getEnabledLinkTypes()
    {
        if (null !== $this->_enabledLinkTypes) {
            return $this->_enabledLinkTypes;
        }

        $enabledLinkTypes = [];
        if (is_array($this->types)) {
            foreach ($this->types as $type => $settings) {
                if ($settings['enabled'] ?? false) {
                    $linkType = $this->_getLinkTypeModelByType($type);
                    if ($linkType) {
                        $enabledLinkTypes[] = $linkType;
                    }
                }
            }
        }
        return $this->_enabledLinkTypes = $enabledLinkTypes;
    }

    /**
     * @return array<int, array{label: mixed, value: mixed}>
     */
    public function getEnabledLinkTypesAsOptions(): array
    {
        $options = [];
        $enabledLinkTypes = $this->getEnabledLinkTypes();
        if ($enabledLinkTypes) {
            $options = [
                [
                    'label' => $this->selectLinkText != '' ? $this->selectLinkText : static::defaultSelectLinkText(),
                    'value' => '',
                ],
            ];

            foreach ($enabledLinkTypes as $enabledLinkType) {
                $options[] = [
                    'label' => $enabledLinkType->label,
                    'value' => $enabledLinkType->type,
                ];
            }
        }

        return $options;
    }

    // Private Methods
    // =========================================================================

    private function _getLinkTypeModelByType(string $type, bool $populate = true)
    {
        try {
            $linkType = Craft::createObject($type);
            if ($populate) {
                $linkType = $this->_populateLinkTypeModel($linkType);
            }
            return $linkType;
        } catch (ErrorException $exception) {
            $error = $exception->getMessage();
            return false;
        }
    }

    private function _populateLinkTypeModel(Link $linkType): \presseddigital\linkit\base\Link
    {
        // Get Type Settings
        $linkType->setAttributes($this->types[$linkType->type] ?? [], false); // TODO: Get Rules added for these and remove false
        // Craft::configure($linkType, $attributes); // Want to use but only if we can confirm that we can't get passed invalid attributes here
        $linkType->fieldSettings = $this->getSettings(); // TODO: remove and ust use the field now set below
        $linkType->setField($this);
        return $linkType;
    }

    private function _normalizeValueCraft2($content)
    {
        if (!$content) {
            return null;
        }

        $newContent = [
            'customText' => $content['customText'] ?? null,
            'target' => ($content['target'] ?? false) ? true : false,
        ];

        if ($content['type'] ?? false) {
            switch ($content['type']) {
                case 'email':
                    $newContent['type'] = Email::class;
                    $newContent['value'] = $content['email'] ?? '';
                    break;

                case 'custom':
                    $newContent['type'] = Url::class;
                    $newContent['value'] = $content['custom'] ?? '';
                    break;

                case 'tel':
                    $newContent['type'] = Phone::class;
                    $newContent['value'] = $content['tel'] ?? '';
                    break;

                case 'entry':
                    $newContent['type'] = Entry::class;
                    $newContent['value'] = $content['entry'][0] ?? '';
                    break;

                case 'category':
                    $newContent['type'] = Category::class;
                    $newContent['value'] = $content['category'][0] ?? '';
                    break;

                case 'asset':
                    $newContent['type'] = Asset::class;
                    $newContent['value'] = $content['asset'][0] ?? '';
                    break;

                case 'product':
                    $newContent['type'] = Product::class;
                    $newContent['value'] = $content['product'][0] ?? '';
                    break;

                default:
                    return $content;
                    break;
            }
        }

        return $newContent;
    }
}

class_alias(LinkitField::class, \fruitstudios\linkit\fields\LinkitField::class);
