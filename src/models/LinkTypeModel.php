<?php
namespace fruitstudios\linkit\models;

use fruitstudios\linkit\LinkIt;

use Craft;
use craft\base\Model;

class LinkTypeModel extends Model
{
    // Public Properties
    // =========================================================================

    public $displayName;
    public $handle;
    public $type;

    // Public Methods
    // =========================================================================

    public function __construct(string $displayName, string $handle, string $type)
    {
        $this->displayName = Craft::t('link-it', $displayName);
        $this->handle = $handle;
        $this->type = $type;
    }

    /*
     *
     * @return string|null
     */
    public function getSettingsHtml()
    {
        // Render the settings template
        return '<p>Settings Here</p>';
        // return Craft::$app->getView()->renderTemplate(
        //     'link-it/fields/_settings',
        //     [
        //         'field' => $this,
        //     ]
        // );
    }


    public function hasSettings(): bool
    {
        switch ($this->handle)
        {
            case 'email':
            case 'url':
            case 'tel':
                return false;
                break;

            case 'asset':
            case 'entry':
            case 'category':
            case 'product':
                return true;
                break;

            default:
                return false;
                break;
        }
    }


}
