<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionInterface;

/**
 * Class Nested
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option
 */
class Nested extends Option implements NestedInterface
{
    /**
     * @var int
     * @since 1.0.0
     */
    protected $parentId;

    /**
     * @var OptionInterface
     * @since 1.0.0
     */
    protected $parentOption;

    /**
     * @var Collection
     * @since 1.0.0
     */
    protected $children;

    /**
     * @var boolean
     * @since 1.0.0
     */
    protected $hasChildSelected = false;

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(int $parentId): NestedInterface
    {
        $this->parentId = $parentId;

        return $this;
    }


    public function getParentOption(): ?NestedInterface
    {
        return $this->parentOption;
    }

    public function setParentOption(OptionInterface $parent): NestedInterface
    {
        $this->parentOption = $parent;

        return $this;
    }

    public function getChildren(): ?Collection
    {
        return $this->children;
    }

    public function setChildren(Collection $children): NestedInterface
    {
        $this->children = $children;

        return $this;
    }

    public function hasChildSelected(): bool
    {
        return $this->hasChildSelected;
    }

    public function setHasChildSelected(bool $hasChildSelection): NestedInterface
    {
        $this->hasChildSelected = $hasChildSelection;

        return $this;
    }
}
