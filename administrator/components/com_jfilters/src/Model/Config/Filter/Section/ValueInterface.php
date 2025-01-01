<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\SectionInterface;

/**
 * Interface ValueInterface
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config
 */
interface ValueInterface extends SectionInterface
{
    /**
     * @param Field $value
     * @return ValueInterface
     * @since 1.0.0
     */
    public function setValue(Field $value): ValueInterface;

    /**
     * @return Field
     * @since 1.0.0
     */
    public function getValue(): Field;

    /**
     * @param Field $label
     * @return ValueInterface
     * @since 1.0.0
     */
    public function setLabel(Field $label): ValueInterface;

    /**
     * @return Field
     * @since 1.0.0
     */
    public function getLabel(): Field;

    /**
     * @param Field $state
     * @return ValueInterface
     * @since 1.2.0
     */
    public function setState(Field $state): ValueInterface;

    /**
     * Get the state of the value
     *
     * @return Field | null
     * @since 1.2.0
     */
    public function getState(): ?Field;

    /**
     * @param bool $tree
     * @return ValueInterface
     * @since 1.0.0
     */
    public function setIsTree(bool $tree): ValueInterface;

    /**
     * Is tree
     *
     * @return bool|null
     * @since 1.0.0
     */
    public function getIsTree();

    /**
     * @param Field $alias
     * @return ValueInterface
     * @since 1.0.0
     */
    public function setAlias(Field $alias): ValueInterface;

    /**
     * @return Field | null
     * @since 1.0.0
     */
    public function getAlias(): ?Field;

    /**
     * @param Field $parentId
     * @return ValueInterface
     * @since 1.0.0
     */
    public function setParentId(Field $parentId): ValueInterface;

    /**
     * The parent id represents the filter id to which a value belongs
     *
     * @return Field|null
     * @since 1.0.0
     */
    public function getParentId(): ?Field;

    /**
     * Set the parent value id (e.g. a category can have a parent category)
     *
     * @param Field $parenValueId
     * @return ValueInterface
     * @since 1.0.0
     */
    public function setParentValueId(Field $parenValueId): ValueInterface;

    /**
     * The parent value id (e.g. a category can have a parent category)
     *
     * @return Field|null
     * @since 1.0.0
     */
    public function getParentValueId(): ?Field;

    /**
     * Set the "lft" (left) field for a tree value
     *
     * @param Field $lft
     * @return ValueInterface
     * @since 1.6.0
     */
    public function setLft(Field $lft): ValueInterface;

    /**
     * Get the "lft" (left) field for a tree value
     *
     * @return Field|null
     * @since 1.6.0
     */
    public function getLft(): ?Field;

    /**
     * Set the "rgt" (right) field for a tree value
     *
     * @param Field $rgt
     * @return ValueInterface
     * @since 1.6.0
     */
    public function setRgt(Field $rgt): ValueInterface;

    /**
     * Get the "rgt" (right) field for a tree value
     *
     * @return Field|null
     * @since 1.6.0
     */
    public function getRgt(): ?Field;

    /**
     * @param Field $language
     * @return ValueInterface
     * @since 1.0.0
     */
    public function setLanguage(Field $language): ValueInterface;

    /**
     * @return Field|null
     * @since 1.0.0
     */
    public function getLanguage(): ?Field;

    /**
     * @param Field $extension
     * @return ValueInterface
     * @since 1.0.0
     */
    public function setExtension(Field $extension): ValueInterface;

    /**
     * The extension represents the extension that a value references
     *
     * @return Field|null
     * @since 1.0.0
     */
    public function getExtension(): ?Field;

    /**
     * Set the request vars
     *
     * @param Field $requests
     * @return ValueInterface
     * @since 1.0.0
     */
    public function setRequests(Field $requests): ValueInterface;

    /**
     * Get the request vars
     *
     * @return Field|null
     * @since 1.0.0
     */
    public function getRequests(): ?Field;

    /**
     * Set the meta-description
     *
     * @param   Field  $metaDescription
     *
     * @return ValueInterface
     * @since 1.0.0
     */
    public function setMetadescription(Field $metaDescription): ValueInterface;

    /**
     * Get the meta-description
     *
     * @return Field|null
     * @since 1.0.0
     */
    public function getMetadescription(): ?Field;

    /**
     * Set the meta-keywords
     *
     * @param   Field  $metakeywords
     *
     * @return ValueInterface
     * @since 1.0.0
     */
    public function setMetakeywords(Field $metakeywords): ValueInterface;

    /**
     * Get the meta-keywords
     *
     * @return Field|null
     * @since 1.0.0
     */
    public function getMetakeywords(): ?Field;

}
