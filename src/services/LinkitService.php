<?php
namespace fruitstudios\linkit\services;

use fruitstudios\linkit\Linkit;
use fruitstudios\linkit\events\RegisterLinkTypesEvent;

use fruitstudios\linkit\models\Phone;
use fruitstudios\linkit\models\Url;
use fruitstudios\linkit\models\Email;
use fruitstudios\linkit\models\Asset;
use fruitstudios\linkit\models\Entry;
use fruitstudios\linkit\models\Category;
use fruitstudios\linkit\models\User;
use fruitstudios\linkit\models\Product;
use fruitstudios\linkit\models\Twitter;
use fruitstudios\linkit\models\Facebook;
use fruitstudios\linkit\models\LinkedIn;
use fruitstudios\linkit\models\Instagram;

use Craft;
use craft\base\Component;
use craft\helpers\Component as ComponentHelper;

class LinkitService extends Component
{
    // Constants
    // =========================================================================

    const EVENT_REGISTER_LINKIT_FIELD_TYPES = 'registerLinkitFieldTypes';

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
        if(Craft::$app->getPlugins()->getPlugin('commerce'))
        {
            $linkTypes[] = new Product();
        }

        // Third Party
        $event = new RegisterLinkTypesEvent([
            'types' => $linkTypes
        ]);
        $this->trigger(self::EVENT_REGISTER_LINKIT_FIELD_TYPES, $event);
        return $event->types;
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



    public function getSourceOptions($elementType): array
    {
        $sources = Craft::$app->getElementIndexes()->getSources($elementType, 'modal');
        $options = [];
        $optionNames = [];

        foreach ($sources as $source) {
            // Make sure it's not a heading
            if (!isset($source['heading'])) {
                $options[] = [
                    'label' => $source['label'],
                    'value' => $source['key']
                ];
                $optionNames[] = $source['label'];
            }
        }

        // Sort alphabetically
        array_multisort($optionNames, SORT_NATURAL | SORT_FLAG_CASE, $options);

        return $options;
    }



}
