<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface as FilterConfigInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection as OptionCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Registry;
use Joomla\Registry\Registry as JRegistry;

/**
 * Interface FilterInterface
 * @package Bluecoder\Component\Jfilters\Administrator\Model
 */
interface FilterInterface
{
    /**
     * The var name used in url for the filter in non-sef urls
     * @since 1.0.0
     */
    const URL_FILTER_VAR_NAME = 'filter';

    /**
     * Get the filter id.
     * The id is a mandatory field
     *
     * @return int
     * @since 1.0.0
     */
    public function getId(): int;

    /**
     * @param int $id
     * @return FilterInterface
     * @since 1.0.0
     */
    public function setId(int $id): FilterInterface;

    /**
     * @return int
     * @since 1.0.0
     */
    public function getParentId(): int;

    /**
     * @param int $parentId
     * @return FilterInterface
     * @since 1.0.0
     */
    public function setParentId(int $parentId): FilterInterface;

    /**
     * @return string
     * @throws \RuntimeException
     * @since 1.0.0
     */
    public function getConfigName(): string;

    /**
     * @param string $configName
     * @return FilterInterface
     * @since 1.0.0
     */
    public function setConfigName(string $configName): FilterInterface;


    /**
     * The context that the filter is related to.
     * Mandatory field
     *
     * @return string
     * @throws \RuntimeException
     * @since 1.0.0
     */
    public function getContext(): string;

    /**
     * @param string $context
     * @return FilterInterface
     */
    public function setContext(string $context): FilterInterface;

    /**
     * @return string
     * @since 1.0.0
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return FilterInterface
     * @since 1.0.0
     */
    public function setName(string $name): FilterInterface;

    /**
     * @return string
     * @since 1.0.0
     */
    public function getLabel(): string;

    /**
     * @param string $label
     * @return FilterInterface
     * @since 1.0.0
     */
    public function setLabel(string $label): FilterInterface;


    /**
     * Get the filter alias.
     *
     * @return string|null
     * @since 1.0.0
     */
    public function getAlias();

    /**
     * @param string $alias
     * @return FilterInterface
     * @since 1.0.0
     */
    public function setAlias($alias): FilterInterface;

    /**
     * @return string
     * @since 1.0.0
     */
    public function getDisplay(): string;

    /**
     * @param string $layout
     * @return FilterInterface
     * @since 1.0.0
     */
    public function setDisplay(string $layout): FilterInterface;

    /**
     * @return int
     * @since 1.0.0
     */
    public function getState(): int;

    /**
     * @param int $state
     * @return FilterInterface
     * @since 1.0.0
     */
    public function setState(int $state): FilterInterface;

    /**
     * Get if root filter or not
     *
     * @return bool
     * @since 1.0.0
     */
    public function getRoot(): bool;

    /**
     * Set if root filter or not
     *
     * @param bool $layout
     * @return FilterInterface
     * @since 1.0.0
     */
    public function setRoot(bool $isRoot): FilterInterface;

    /**
     * Returns all the filter attributes or only those specified in the $names
     *
     * @param array $names
     * @return Registry
     * @since 1.0.0
     */
    public function getAttributes(array $names = []): Registry;

    /**
     * Set the attributes
     *
     * @param JRegistry $attributes
     * @return FilterInterface
     * @since 1.0.0
     */
    public function setAttributes(JRegistry $attributes): FilterInterface;

    /**
     * Get the Option Collection
     *
     * @return OptionCollection
     * @since 1.0.0
     */
    public function getOptions(): OptionCollection;

    /**
     * Set the Option Collection
     *
     * @param OptionCollection $optionCollection
     * @return FilterInterface
     * @since 1.0.0
     */
    public function setOptions(OptionCollection $optionCollection): FilterInterface;

    /**
     * Get the config of the filter
     *
     * @return FilterConfigInterface
     * @since 1.0.0
     */
    public function getConfig(): FilterConfigInterface;

    /**
     * Get the name of the request var
     *
     * @return string
     * @since 1.0.0
     */
    public function getRequestVarName(): string;

    /**
     * Get the request values of that filter
     *
     * @return array
     * @throws \Exception
     * @since 1.0.0
     */
    public function getRequest(): array;


    /**
     * Set the request values of that filter
     *
     * @param array $requestValues
     * @return FilterInterface
     * @since 1.0.0
     */
    public function setRequest(array $requestValues) : FilterInterface;

    /**
     * Set the language
     *
     * @param string $language
     * @return FilterInterface
     * @since 1.0.0
     */
    public function setLanguage(string $language): FilterInterface;

    /**
     * Get the language
     *
     * @return string
     * @since 1.0.0
     */
    public function getLanguage(): string;

    /**
     * Returns if the filter is multi-select or not, based in its display type.
     *
     * @return bool
     * @since 1.0.0
     */
    public function getIsMultiSelect() : bool;

    /**
     * Returns if the filter is a range or not, based in its display type.
     *
     * @return bool
     * @since 1.14.0
     */
    public function getIsRange() : bool;

    /**
     * Returns the data in raw format as properties of the \stdClass
     *
     * @return \stdClass
     * @since 1.0.0
     */
    public function getData();

    /**
     * A filter maybe published but is not visible due to a condition or has no options, relevant to the other selections.
     * We still need to fetch this filter's assets in the FE modules,
     * in case it is loaded through ajax later on.
     *
     * @return bool
     * @since 1.12.0
     */
    public function isVisible() : bool;

    /**
     * @param bool $visible
     * @return FilterInterface
     * @since 1.12.0
     */
    public function setIsVisible(bool $visible) : FilterInterface;

}
