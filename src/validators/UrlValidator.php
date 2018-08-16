<?php
namespace fruitstudios\linkit\validators;

use Craft;
use yii\validators\UrlValidator as YiiUrlValidator;

class UrlValidator extends YiiUrlValidator
{
    // Public Methods
    // =========================================================================

    public function __construct(array $config = [])
    {

        // Override the $pattern regex so that a TLD is not required, and the protocol may be relative and can be a #
        if (!isset($config['pattern'])) {
            $config['pattern'] = '/^(\/|#|(?:(?:{schemes}:)?\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)?|\/)[^\s]*$)/i';
        }

        // Enable support for validating international domain names if the intl extension is available.
        if (!isset($config['enableIDN']) && Craft::$app->getI18n()->getIsIntlLoaded() && defined('INTL_IDNA_VARIANT_UTS46')) {
            $config['enableIDN'] = true;
        }

        parent::__construct($config);
    }

    public function validateValue($value)
    {
        if($value === '#' || $value === '/') {
            $this->defaultScheme = null;
            return null;
        }

        // Add support for protocol-relative URLs, # or /
        if ($this->defaultScheme !== null && strpos($value, '/') === 0) {
            $this->defaultScheme = null;
        } else {
            $this->pattern = '/^{schemes}:\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(?::\d{1,5})?(?:$|[?\/#])/i';
        }

        return parent::validateValue($value);
    }

}
