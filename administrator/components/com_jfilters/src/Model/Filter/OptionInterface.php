<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Joomla\CMS\Uri\Uri;

/**
 * Interface OptionInterface
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Filter
 */
interface OptionInterface
{
    /**
     * @return string
     * @since 1.0.0
     */
    public function getValue(): string;

    /**
     * @param string $value
     * @return OptionInterface
     * @since 1.0.0
     */
    public function setValue(string $value): OptionInterface;

    /**
     * @return string
     * @since 1.0.0
     */
    public function getLabel(): string;

    /**
     * @param string $label
     * @return OptionInterface
     * @since 1.0.0
     */
    public function setLabel(string $label): OptionInterface;

    /**
     * Get the sef alias
     *
     * @return string
     * @since 1.0.0
     */
    public function getAlias(): ?string;

    /**
     * Set the sef alias
     *
     * @param string $alias
     * @return OptionInterface
     * @since 1.0.0
     */
    public function setAlias(string $alias): OptionInterface;

    /**
     * Get the meta-description
     *
     * @return string
     * @since 1.0.0
     */
    public function getMetadescription(): ?string;

    /**
     * Set the meta-description
     *
     * @param   string  $metadescription
     *
     * @return OptionInterface
     * @since 1.0.0
     */
    public function setMetadescription(string $metadescription): OptionInterface;

    /**
     * Get the meta-keywords
     *
     * @return string
     * @since 1.0.0
     */
    public function getMetakeywords(): ?string;

    /**
     * Set the meta-keywords
     *
     * @param   string  $metakeywords
     *
     * @return OptionInterface
     * @since 1.0.0
     */
    public function setMetakeywords(string $metakeywords): OptionInterface;

    /**
     * Get the count (number) of items returned.
     *
     * @return int|null
     * @since 1.0.0
     */
    public function getCount();

    /**
     * @param int $count
     * @return OptionInterface
     * @since 1.0.0
     */
    public function setCount(int $count): OptionInterface;

    /**
     * Get the filter where the option belongs
     *
     * @return FilterInterface
     * @since 1.0.0
     */
    public function getParentFilter(): FilterInterface;

    /**
     * Set the filter where the option belongs
     *
     * @param FilterInterface $filter
     * @return OptionInterface
     * @since 1.0.0
     */
    public function setParentFilter(FilterInterface $filter): OptionInterface;

    /**
     * Get the route of an option
     *
     * @param   bool  $clone      Clone or not the uri
     * @param   bool  $toggleVar  Create the toggle effect for the multi-select by adding and removing the var in each request.
     *
     * @return Uri
     * @since 1.0.0
     */
    public function getLink(bool $clone = true, bool $toggleVar = true): Uri;

    /**
     * Check if it has selection
     *
     * @return bool
     * @since 1.0.0
     */
    public function isSelected(): bool;

    /**
     * Get if the Option is of type Nested
     *
     * @return bool
     * @since 1.0.0
     */
    public function isNested() : bool;

    /**
     * Set if the label is resolved (i.e. final) or not
     *
     * @param bool $isResolved
     * @return OptionInterface
     * @since 1.13.0
     */
    public function setIsLabelResolved(bool $isResolved) : OptionInterface;
}
