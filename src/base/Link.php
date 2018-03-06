<?php
namespace fruitstudios\linkit\base;

use Craft;
use craft\base\Component;
use craft\helpers\Template as TemplateHelper;

use fruitstudios\linkit\helpers\LinkItHelper;

abstract class Link extends Component implements LinkInterface
{
    // Public
    // =========================================================================

    public $type;

    public $value = '';
    public $customText = '';
    public $target = false;

    // Public Methods
    // =========================================================================

	public function __toString(): string
    {
        return (string) $this->getLink();
    }

    public function getLinkType(): string
    {
        return $this->type; //TODO: Does this need to be a more useful handle for processing
    }

    public function getLink($raw = true)
    {
    	$html = LinkItHelper::getLinkHtml($this->getUrl(), $this->text, $this->getLinkAttributes());
        return $raw ? TemplateHelper::raw($html) : $html;
    }

    public function getUrl(): string
    {
		return (string) $this->value;
    }

    public function getText(): string
    {
        if($this->customText != '')
        {
            return $this->customText;
        }
        return $this->getUrl() ?? '';
    }

    public function getLinkAttributes(): array
    {
    	$attributes = [];
    	if($this->target)
    	{
    		$attributes['target'] = '_blank';
    	}
        return $attributes;
    }

}
