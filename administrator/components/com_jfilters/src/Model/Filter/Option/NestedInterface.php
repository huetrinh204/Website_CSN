<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionInterface;

/**
 * Interface NestedInterface
 * Used for nested Options (e.g. categories that have parent/child elements)
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option
 */
interface NestedInterface extends OptionInterface
{
    /**
     * Get the parent id
     * Useful in tree/nested options
     *
     * @return int|null
     * @since 1.0.0
     */
    public function getParentId(): ?int;

    /**
     * Set the parent id
     * Useful in tree/nested options
     *
     * @param int $parentId
     * @return OptionInterface
     * @since 1.0.0
     */
    public function setParentId(int $parentId): NestedInterface;

    /**
     * Get the parent option
     * Useful in tree/nested options
     *
     * @return OptionInterface|null
     * @since 1.0.0
     */
    public function getParentOption(): ?NestedInterface;

    /**
     * Set the parent option
     * Useful in tree/nested options
     *
     * @param OptionInterface $parent
     * @return OptionInterface
     * @since 1.0.0
     */
    public function setParentOption(OptionInterface $parent): NestedInterface;

    /**
     * Get the children.
     * Useful in tree/nested options
     *
     * @return Collection|null
     * @since 1.0.0
     */
    public function getChildren(): ?Collection;

    /**
     * Set the children collection
     *
     * @param Collection $children
     * @return OptionInterface
     * @since 1.0.0
     */
    public function setChildren(Collection $children): NestedInterface;

    /**
     * Indicates if any of the children of the current node have a selection (isSelected)
     *
     * @return bool
     * @since 1.0.0
     */
    public function hasChildSelected() : bool;

    /**
     * Set if any of the children has a selection
     *
     * @param   bool  $hasChildSelection
     *
     * @return NestedInterface
     * @since 1.0.0
     */
    public function setHasChildSelected(bool $hasChildSelection): NestedInterface;
}
