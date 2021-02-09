<?php
namespace fruitstudios\linkit\fields;

use fruitstudios\linkit\Linkit;
use fruitstudios\linkit\assetbundles\field\FieldAssetBundle;
use fruitstudios\linkit\assetbundles\fieldsettings\FieldSettingsAssetBundle;
use fruitstudios\linkit\services\LinkitService;
use fruitstudios\linkit\base\Link;
use fruitstudios\linkit\models\Email;
use fruitstudios\linkit\models\Phone;
use fruitstudios\linkit\models\Url;
use fruitstudios\linkit\models\Entry;
use fruitstudios\linkit\models\Category;
use fruitstudios\linkit\models\Asset;
use fruitstudios\linkit\models\Product;

use Craft;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use craft\base\Field;
use craft\helpers\Json as JsonHelper;
use craft\helpers\Db as DbHelper;
use yii\db\Schema;
use yii\base\ErrorException;
use craft\validators\ArrayValidator;

class LinkitField extends Field implements PreviewableFieldInterface
{
    // Constants
    // =========================================================================

    /**
     * @event RegisterComponentTypesEvent The event that is triggered when registering field types.
     */
    const EVENT_REGISTER_LINKIT_LINK_TYPES = 'registerLinkitLinkTypes';

    // Private Properties
    // =========================================================================

    private $_availableLinkTypes;
    private $_enabledLinkTypes;
    private $_columnType = Schema::TYPE_TEXT;


    //  Properties
    // =========================================================================

    public $selectLinkText = '';
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

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['types'], ArrayValidator::class, 'min' => 1, 'tooFew' => Craft::t('linkit', 'You must select at least one link type.'), 'skipOnEmpty' => false];
        return $rules;
    }

    public function getContentColumnType(): string
    {
        return $this->_columnType;
    }

    public static function hasContentColumn(): bool
    {
        return true;
    }

    public function normalizeValue($value, ElementInterface $element = null)
    {
        if($value instanceof Link)
        {
            return $value;
        }

        if(is_string($value))
        {
            $value = JsonHelper::decodeIfJson($value);
        }

        // Handle any Craft2 content
        if(!isset($value['value']))
        {
            $value = $this->_normalizeValueCraft2($value);
        }

        if(isset($value['type']) && $value['type'] != '' )
        {
            if(isset($value['value']) && $value['value'] == '')
            {
                return null;
            }

            if(isset($value['values']))
            {
                $postedValue = $value['values'][$value['type']] ?? '';
                $value['value'] = is_array($postedValue) ? $postedValue[0] : $postedValue;
                unset($value['values']);
            }

            $link = $this->_getLinkTypeModelByType($value['type']);
            $link->setAttributes($value, false); // TODO: Get Rules added for these and remove false
            $link->ownerElement = $element;
            return $link;
        }

        return null;
    }

    public function serializeValue($value, ElementInterface $element = null)
    {
        $serialized = [];
        if($value instanceof Link)
        {
            $serialized = [
                'type' => $value->type,
                'value' => $value->value,
                'customText' => $value->customText,
                'target' => $value->target,
                'siteId' => $value->siteId,
            ];
        }

        return parent::serializeValue($serialized, $element);
    }

    public function getSettingsHtml()
    {
        $view = Craft::$app->getView();
        $view->registerAssetBundle(FieldSettingsAssetBundle::class);

        return $view->renderTemplate(
            'linkit/fields/_settings',
            [
                'field' => $this,
            ]
        );
    }

    public function getInputHtml($value, ElementInterface $element = null): string
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
        $view->registerJs('new Garnish.LinkitField('.$jsVariables.');');

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

    public function getElementValidationRules(): array
    {
        return ['validateLinkValue'];
    }

    public function isValueEmpty($value, ElementInterface $element): bool
    {
        return empty($value->value ?? '');
    }

    public function validateLinkValue(ElementInterface $element)
    {
        $fieldValue = $element->getFieldValue($this->handle);
        if($fieldValue && !$fieldValue->validate())
        {
            $element->addModelErrors($fieldValue, $this->handle);
        }
    }

    public function getSearchKeywords($value, ElementInterface $element): string
    {
        if($value instanceof Link)
        {
            return $value->getText();
        }
        return '';
    }

    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        if($value instanceof Link)
        {
           return '<span title="Link '.($value->isAvailable() ? 'Enabled' : 'Disabled').'" class="status '.($value->isAvailable() ? 'enabled' : 'disabled').'"></span>'.$value->getLinkPreview();
        }
        return '';
    }

    public function getAvailableLinkTypes()
    {
        if(is_null($this->_availableLinkTypes))
        {
            $linkTypes = Linkit::$plugin->service->getAvailableLinkTypes();
            if($linkTypes)
            {
                foreach ($linkTypes as $linkType)
                {
                   $this->_availableLinkTypes[] = $this->_populateLinkTypeModel($linkType);
                }
            }
        }
        return $this->_availableLinkTypes;
    }

    public function getEnabledLinkTypes()
    {
        if(is_null($this->_enabledLinkTypes))
        {
            $this->_enabledLinkTypes = [];
            if(is_array($this->types))
            {
                foreach ($this->types as $type => $settings)
                {
                    if($settings['enabled'] ?? false) {
                        $linkType = $this->_getLinkTypeModelByType($type);
                        if($linkType)
                        {
                            $this->_enabledLinkTypes[] = $linkType;
                        }
                    }
                }
            }
        }
        return $this->_enabledLinkTypes;
    }

    public function getEnabledLinkTypesAsOptions()
    {
        $options = [];
        $enabledLinkTypes = $this->getEnabledLinkTypes();
        if($enabledLinkTypes)
        {
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
            if($populate)
            {
                $linkType = $this->_populateLinkTypeModel($linkType);
            }
            return $linkType;
        } catch(ErrorException $exception) {
            $error = $exception->getMessage();
            return false;
        }
    }

    private function _populateLinkTypeModel(Link $linkType)
    {
        // Get Type Settings
        $attributes = $this->types[$linkType->type] ?? [];
        $linkType->setAttributes($attributes, false);
        $linkType->fieldSettings = $this->getSettings();
        return $linkType;
    }

    private function _normalizeValueCraft2($content)
    {
        if(!$content)
        {
            return null;
        }

        $newContent = [
            'customText' => $content['customText'] ?? null,
            'target' => ($content['target'] ?? false) ? true : false,
        ];

        if($content['type'] ?? false)
        {
            switch ($content['type'])
            {
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
