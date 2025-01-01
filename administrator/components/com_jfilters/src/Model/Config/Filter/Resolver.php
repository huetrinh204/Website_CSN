<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\ConfigInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ConfigContextCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection as ConfigFilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\SectionInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;

/**
 * Class Resolver
 *
 * Resolves the config attributes, to usable Fields
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter
 */
class Resolver
{
    /**
     * @var FilterInterface
     * @since 1.0.0
     */
    protected $filter;

    /**
     * @var ConfigFilterCollection
     * @since 1.0.0
     */
    protected $configFilterCollection;

    /**
     * @var ConfigContextCollection
     * @since 1.0.0
     */
    protected $configContextCollection;

    /**
     * Store the configs that were resolved
     *
     * @var array
     * @since 1.0.0
     */
    protected $resolved = [];

    /**
     * Resolver constructor.
     * @param Collection $configFilterCollection
     * @param ConfigContextCollection $configContextCollection
     */
    public function __construct(ConfigFilterCollection $configFilterCollection, ConfigContextCollection $configContextCollection)
    {
        $this->configFilterCollection = $configFilterCollection;
        $this->configContextCollection = $configContextCollection;
    }

    /**
     * @param FilterInterface $filter
     * @return \Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface
     * @since 1.0.0
     */
    public function getFilterConfig(FilterInterface $filter)
    {
        $this->filter = $filter;
        $configName = $this->filter->getConfigName();
        if(!isset($this->resolved[$configName])) {
            $this->resolveAttributes();
            $this->resolved[$configName] = true;
        }
        return $this->configFilterCollection->getByNameAttribute($configName);
    }

    /**
     * Iterates through the attributes of each Field and tries to resolve them
     *
     * @return $this
     * @since 1.0.0
     */
    protected function resolveAttributes()
    {
        $configItem = $this->configFilterCollection->getByNameAttribute($this->filter->getConfigName());

        /** @var SectionInterface $section */
        foreach ($configItem->getSections() as $section) {
            foreach ($section->getFields() as $field) {
                foreach ($field->getAttributes() as $key =>$attribute) {
                    $attribute = $this->resolve($attribute);
                    $methodName = 'set'.ucfirst($key);
                    if(method_exists($field, $methodName)) {
                        //set the new Field attribute
                        $field->{$methodName}($attribute);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Resolve the attribute of a Field.
     *
     * If the attribute refers to another Field (e.g. 'reference' returns that Field)
     * Otherwise the attribute's string value
     *
     * @param $attribute
     * @return Field|string
     * @since 1.0.0
     */
    protected function resolve($attribute) {
        preg_match('/^{(this|context)+\.([a-z]+)\.([a-z]+)}$/i', $attribute, $matches);

        //is valid
        if(isset($matches[0])) {
            $group = $matches[1];
            if($group && $group == 'this') {
                /** @var ConfigInterface $configItem */
                $configItem = $this->configFilterCollection->getByNameAttribute($this->filter->getConfigName());
            } elseif ($group == 'context') {
                $configItem = $this->configContextCollection->getByNameAttribute($this->filter->getContext());
            }

            $section = $matches[2];
            if($configItem && $section) {
                $getSectionMethodName = 'get'.ucfirst($section);

                //Does the Section exist. i.e. function exist for that
                if(method_exists($configItem, $getSectionMethodName)) {
                    $sectionItem = $configItem->{$getSectionMethodName}();
                }
            }
            $field = $matches[3];
            if(isset($sectionItem) && $field) {
                $getFieldMethodName = 'get'.ucfirst($field);
                //Does the Field exist. i.e. function exist for that
                if(method_exists($sectionItem, $getFieldMethodName)) {
                    //return the Field
                    return $sectionItem->{$getFieldMethodName}();
                }

            }
        }
        return $attribute;
    }
}
