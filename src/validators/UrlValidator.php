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

        parent::__construct($config);
    }

    public function validateValue($value)
    {
        // Add support for protocol-relative URLs, # or /
        if ($this->defaultScheme !== null && ( strpos($value, '/') === 0 || $value === '#' || $value === '/')) {
            $this->defaultScheme = null;
        }

        return parent::validateValue($value);
    }

}
