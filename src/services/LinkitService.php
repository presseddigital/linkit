<?php

namespace presseddigital\linkit\services;

use Craft;
use craft\base\Component;
use craft\helpers\ArrayHelper;

use presseddigital\linkit\base\ElementLink;
use presseddigital\linkit\events\RegisterLinkTypesEvent;
use presseddigital\linkit\models\Asset;
use presseddigital\linkit\models\Category;
use presseddigital\linkit\models\Email;
use presseddigital\linkit\models\Entry;
use presseddigital\linkit\models\Facebook;
use presseddigital\linkit\models\Instagram;
use presseddigital\linkit\models\LinkedIn;
use presseddigital\linkit\models\Phone;
use presseddigital\linkit\models\Product;
use presseddigital\linkit\models\Twitter;
use presseddigital\linkit\models\Url;
use presseddigital\linkit\models\User;

class LinkitService extends Component
{
    // Constants
    // =========================================================================

    public const EVENT_REGISTER_LINKIT_FIELD_TYPES = 'registerLinkitFieldTypes';

    // Public Methods
    // =========================================================================

    public function getAvailableLinkTypes()
    {
        $linkTypes = [];

        // Basic link types
        $linkTypes[] = new Email();
        $linkTypes[] = new Phone();
        $linkTypes[] = new Url();

        // Social link types
        $linkTypes[] = new Twitter();
        $linkTypes[] = new Facebook();
        $linkTypes[] = new Instagram();
        $linkTypes[] = new LinkedIn();

        // Element link types
        $linkTypes[] = new Entry();
        $linkTypes[] = new Category();
        $linkTypes[] = new Asset();
        $linkTypes[] = new User();

        // Product link
        if (Craft::$app->getPlugins()->getPlugin('commerce')) {
            $linkTypes[] = new Product();
        }

        // Third Party
        $event = new RegisterLinkTypesEvent([
            'types' => $linkTypes,
        ]);
        $this->trigger(self::EVENT_REGISTER_LINKIT_FIELD_TYPES, $event);
        return $event->types;
    }

    public function getAvailableElementLinkTypes(): ?array
    {
        return array_filter($this->getAvailableLinkTypes(), fn($type) => $type instanceof ElementLink);
    }

    public function getLinkTypeByHandle(string $handle)
    {
        return ArrayHelper::firstWhere($this->getAvailableLinkTypes(), 'typeHandle', $handle);
    }

    // Thrid Party Field Types
    //
    // public function getAllFieldTypes(): array
    // {
    //     $fieldTypes = [
    //         AssetsField::class,
    //         CategoriesField::class,
    //         CheckboxesField::class,
    //         ColorField::class,
    //         DateField::class,
    //         DropdownField::class,
    //         EmailField::class,
    //         EntriesField::class,
    //         LightswitchField::class,
    //         MatrixField::class,
    //         MultiSelectField::class,
    //         NumberField::class,
    //         PlainTextField::class,
    //         RadioButtonsField::class,
    //         TableField::class,
    //         TagsField::class,
    //         UrlField::class,
    //         UsersField::class,
    //     ];
    //     $event = new RegisterComponentTypesEvent([
    //         'types' => $fieldTypes
    //     ]);
    //     $this->trigger(self::EVENT_REGISTER_FIELD_TYPES, $event);
    //     return $event->types;
    // }

    /**
     * @return array<int, array{label: mixed, value: mixed}>
     */
    public function getSourceOptions($elementType): array
    {
        $sources = Craft::$app->getElementSources()->getSources($elementType, 'modal');
        $options = [];
        $optionNames = [];

        foreach ($sources as $source) {
            // Make sure it's not a heading
            if (!isset($source['heading'])) {
                $options[] = [
                    'label' => $source['label'],
                    'value' => $source['key'],
                ];
                $optionNames[] = $source['label'];
            }
        }

        // Sort alphabetically
        array_multisort($optionNames, SORT_NATURAL | SORT_FLAG_CASE, $options);

        return $options;
    }
}
