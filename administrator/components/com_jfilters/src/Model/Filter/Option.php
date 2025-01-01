<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\BaseObject;
use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Clear;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\NestedInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\UriHandlerInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

class Option extends BaseObject implements OptionInterface
{
    /**
     * @var UriHandlerInterface
     * @since 1.0.0
     */
    protected $uri;

    /**
     * @var bool
     * @since 1.7.2
     */
    protected $uriToggleVar;

    /**
     * @var string
     * @since 1.0.0
     */
    protected string $value = '';

    /**
     * Indicates if we use part of the value.
     *
     * @var bool
     */
    protected bool $isValueStripped = false;

    /**
     * Indicates if the label is resolved.
     * I.e. Has its final format.
     *
     * @var bool
     * @since 1.13.0
     */
    protected bool $labelResolved = false;

    /**
     * @var string
     * @since 1.0.0
     */
    protected string $label = '';

    /**
     * @var string
     * @since 1.0.0
     */
    protected $alias;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $metaDescription;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $metaKeywords;

    /**
     * @var int
     * @since 1.0.0
     */
    protected $count;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $url;

    /**
     * The component configuration class
     *
     * @var ComponentConfig
     * @since 1.0.0
     */
    protected $componentConfig;

    /**
     * @var FilterInterface
     * @since 1.0.0
     */
    protected $filter;

    /**
     * @var bool
     * @since 1.0.0
     */
    protected $selected;

    /**
     * Option constructor.
     *
     * @param   UriHandlerInterface  $uri
     * @param   ComponentConfig      $componentConfig
     * @param array $properties
     * @since 1.0.0
     */
    public function __construct(UriHandlerInterface $uri, ComponentConfig $componentConfig, array $properties = [])
    {
        $this->uri = $uri;
        $this->componentConfig = $componentConfig;
        parent::__construct($properties);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): OptionInterface
    {
        $valueInit = $value;
        $value = $this->subString($value, $this->componentConfig->get('max_option_value_length', 35));

        if ($valueInit != $value) {
            $this->isValueStripped = true;
        }
        $this->value = $value;

        return $this;
    }

    public function getLabel(): string
    {
        if ($this->labelResolved === false) {
            $filter = $this->getParentFilter();
            if ($filter->getAttributes()->get('dataType') == 'date' && !$this instanceof Clear) {
                $dateFormat = $filter->getAttributes()->get('date_format', 'd - M - Y');
                $dateFormat .= $filter->getAttributes()->get('show_time', 0) ? ' G:i' : '';
                try {
                    $this->setLabel(HTMLHelper::date($this->getValue(), $dateFormat));
                } catch (\Exception $exception) {
                    // Suck it. Invalid date
                }
            }

            $this->labelResolved = true;
        }


        return $this->label;
    }

    public function setLabel(string $label): OptionInterface
    {
        $labelInit = $label;
        $label = $this->subString($label, $this->componentConfig->get('max_option_label_length', 55));

        if ($label != $labelInit) {
            // Add 3 dots at the end. Indicate that it is stripped.
            $label .= '...';
        }

        $this->label = $label;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): OptionInterface
    {
        $this->alias = $alias;
        return $this;
    }

    public function getMetadescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetadescription(string $metadescription): OptionInterface
    {
        $this->metaDescription = $metadescription;
        return $this;
    }

    public function getMetakeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setMetakeywords(string $metakeywords): OptionInterface
    {
        $this->metaKeywords = $metakeywords;
        return $this;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function setCount(int $count): OptionInterface
    {
        $this->count = $count;
        return $this;
    }

    public function getParentFilter(): FilterInterface
    {
        return $this->filter;
    }

    public function setParentFilter(FilterInterface $filter): OptionInterface
    {
        $this->filter = $filter;
        return $this;
    }

    public function getLink(bool $clone = true, bool $toggleVar = true): Uri
    {
        /**
         * The use of uriToggleVar as a var of this class is a cacophony.
         * We should create a new Uri class that extends the J Uri, where this var should reside.
         */
        if ($this->url == null || $this->uriToggleVar != $toggleVar) {
            $this->url = $this->uri->get($this, $toggleVar);
            $this->uriToggleVar = $toggleVar;
        }
        $return = $this->url;

        /**
         * We are forced to clone the url,
         * otherwise its query vars are being deleted by \Joomla\CMS\Router\Route::_(), when it creates the urls.
         * Hence, we cannot read its query vars in subsequent calls.
         */
        if ($clone) {
            $return = clone $this->url;
        }

        return $return;
    }

    public function isSelected(): bool
    {
        if($this->selected === null) {
            $request = $this->getParentFilter()->getRequest();

            // Do not get fooled by the letter case
            $request = array_map(function ($requestValue) {
                return mb_strtolower($requestValue);
            }, $request);

            $this->selected = in_array(mb_strtolower($this->getValue()), $request);
        }

        return $this->selected;
    }

    public function isNested(): bool
    {
        return $this instanceof NestedInterface;
    }

    /**
     * Returns a part of string.
     *
     * @param   string  $string
     * @param   int     $maxStrLength
     *
     * @return string
     * @since 1.0.0
     */
    protected function subString(string $string, int $maxStrLength)
    {
        $strLength = mb_strlen($string);
        if(!is_numeric($string) && $strLength > $maxStrLength) {
            $string = mb_substr($string, 0, -1*($strLength-$maxStrLength));
        }
        return $string;
    }

    /**
     * Set if the label is resolved (i.e. final) or not
     *
     * @param bool $isResolved
     * @return OptionInterface
     * @since 1.13.0
     */
    public function setIsLabelResolved(bool $isResolved) : OptionInterface
    {
        $this->labelResolved = $isResolved;

        return $this;
    }
}
