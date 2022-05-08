<?php

namespace presseddigital\linkit\validators;

use Craft;
use yii\validators\EmailValidator;
use yii\validators\UrlValidator as YiiUrlValidator;

class UrlValidator extends YiiUrlValidator
{
    // Properties
    // =========================================================================

    public bool $allowAlias = true;
    public bool $allowHash = true;
    public bool $allowPaths = true;
    public bool $allowMailto = true;

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

    public function validateValue($value): ?array
    {
        if ($this->allowAlias && str_starts_with($value, '@')) {
            try {
                $value = Craft::getAlias($value);
                $this->defaultScheme = null;
            } catch (\Exception) {
                return [Craft::t('linkit', 'Please enter a valid alias'), []];
            }
        }

        if ($this->allowHash && str_starts_with($value, '#')) {
            $this->defaultScheme = null;
            return null;
        }

        if ($this->allowHash && str_starts_with($value, '/')) {
            $this->defaultScheme = null;
            return null;
        }

        if ($this->allowMailto && str_starts_with($value, 'mailto:')) {
            $emailValidator = new EmailValidator();
            if ($emailValidator->validateValue(str_replace('mailto:', '', $value))) {
                return [Craft::t('linkit', 'Please enter a valid email address'), []];
            }
            $this->defaultScheme = null;
            return null;
        }

        // Add support for protocol-relative URLs, # or /
        if ($this->defaultScheme !== null && str_starts_with($value, '/')) {
            $this->defaultScheme = null;
        } else {
            $this->pattern = '/^{schemes}:\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(?::\d{1,5})?(?:$|[?\/#])/i';
        }

        return parent::validateValue($value);
    }
}
