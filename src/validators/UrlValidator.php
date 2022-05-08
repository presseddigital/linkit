<?php

namespace presseddigital\linkit\validators;

use Craft;
use yii\validators\EmailValidator;
use yii\validators\UrlValidator as YiiUrlValidator;

class UrlValidator extends YiiUrlValidator
{
    // Properties
    // =========================================================================

    public $allowAlias = true;
    public $allowHash = true;
    public $allowPaths = true;
    public $allowMailto = true;

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
        if ($this->allowAlias && strncmp($value, '@', 1) === 0) {
            try {
                $value = Craft::getAlias($value);
                $this->defaultScheme = null;
            } catch (\Exception $e) {
                return [Craft::t('linkit', 'Please enter a valid alias'), []];
            }
        }

        if ($this->allowHash && substr($value, 0, 1) === '#') {
            $this->defaultScheme = null;
            return null;
        }

        if ($this->allowHash && substr($value, 0, 1) === '/') {
            $this->defaultScheme = null;
            return null;
        }

        if ($this->allowMailto && substr($value, 0, 7) === 'mailto:') {
            $emailValidator = new EmailValidator();
            if ($emailValidator->validateValue(str_replace('mailto:', '', $value))) {
                return [Craft::t('linkit', 'Please enter a valid email address'), []];
            }
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
