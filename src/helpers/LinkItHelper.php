<?php

namespace craft\helpers;

use Craft;

class LinkItHelper
{
    // Public Methods
    // =========================================================================

    public static function getLinkHtml(string $url = '#', string $text = '', array $attributes = [])
    {
        $attributesString = '';
        foreach ($attributes as $attribute => $value)
        {
        	$attributesString .= ' '.$attribute.'="'.$value.'"';
        }

        return '<a href="'.$link.'"'.$attributesString.'>'.$text.'</a>';
    }

}
