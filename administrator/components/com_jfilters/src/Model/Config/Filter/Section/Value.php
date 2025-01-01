<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\AbstractCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Section;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\SectionInterface;

class Value extends Section implements SectionInterface, ValueInterface
{
    /**
     * @var Field
     * @since 1.0.0
     */
    protected $valueField;

    /**
     * @var Field
     * @since 1.0.0
     */
    protected $labelField;

    /**
     * @var Field
     * @since 1.2.0
     */
    protected $state;

    /**
     * @var boolean
     * @since 1.0.0
     */
    protected $isTree;

    /**
     * @var Field
     * @since 1.0.0
     */
    protected $aliasField;

    /**
     * @var Field
     * @since 1.0.0
     */
    protected $parentId;

    /**
     * @var Field
     * @since 1.0.0
     */
    protected $parentValueId;

    /**
     * The 'lft' (left) field of a tree value.
     * Check "Nested set model"
     *
     * @var Field
     * @since 1.6.0
     */
    protected $lft;

    /**
     * The 'rgt' (right) field of a tree value
     * Check "Nested set model"
     *
     * @var Field
     * @since 1.6.0
     */
    protected $rgt;

    /**
     * @var Field
     * @since 1.0.0
     */
    protected $extension;

    /**
     * @var Field
     * @since 1.0.0
     */
    protected $language;

    /**
     * @var array
     * @since 1.0.0
     */
    protected $requests;

    /**
     * @var Field
     */
    protected $metaDescription;

    /**
     * @var Field
     */
    protected $metaKeywords;

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        $class = parent::getClass();
        if (!empty($class) && !is_subclass_of($class, AbstractCollection::class)) {
            throw new \UnexpectedValueException(sprintf('The class: \'%s\' attribute of the Value config section, does not implement the \'AbstractCollection\'', $class));
        }
        return $class;
    }

    /**
     * {@inheritDoc}
     */
    public function setValue(Field $valueField): ValueInterface
    {
        $this->valueField = $valueField;
        $this->fields[] = $this->valueField;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(): Field
    {
        return $this->valueField;
    }

    /**
     * {@inheritDoc}
     */
    public function setLabel(Field $labelField): ValueInterface
    {
        $this->labelField = $labelField;
        $this->fields[] = $this->labelField;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel(): Field
    {
        return $this->labelField;
    }

    /**
     * {@inheritDoc}
     */
    public function setState(Field $state): ValueInterface
    {
        $this->state = $state;
        $this->fields[] = $this->state;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getState(): ?Field
    {
        return $this->state;
    }

    /**
     * {@inheritDoc}
     */
    public function setAlias(Field $aliasField): ValueInterface
    {
        $this->aliasField = $aliasField;
        $this->fields[] = $this->aliasField;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias(): ?Field
    {
        return $this->aliasField;
    }

    /**
     * {@inheritDoc}
     */
    public function setParentId(Field $parentId): ValueInterface
    {
        $this->parentId = $parentId;
        $this->fields[] = $this->parentId;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getParentId(): ?Field
    {
        return $this->parentId;
    }

    /**
     * {@inheritDoc}
     */
    public function setParentValueId(Field $parentValueId): ValueInterface
    {
        $this->parentValueId = $parentValueId;
        $this->fields[] = $this->parentValueId;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getParentValueId(): ?Field
    {
        return $this->parentValueId;
    }

    /**
     * {@inheritDoc}
     */
    public function setLft(Field $lft): ValueInterface
    {
        $this->lft = $lft;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getLft(): ?Field
    {
        return $this->lft;
    }

    /**
     * {@inheritDoc}
     */
    public function setRgt(Field $rgt): ValueInterface
    {
        $this->rgt = $rgt;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRgt(): ?Field
    {
        return $this->rgt;
    }

    /**
     * {@inheritDoc}
     */
    public function setExtension(Field $extension): ValueInterface
    {
        $this->extension = $extension;
        $this->fields[] = $this->extension;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getExtension(): ?Field
    {
        return $this->extension;
    }

    public function setIsTree(bool $tree): ValueInterface
    {
        $this->isTree = $tree;
        return $this;
    }

    public function getIsTree()
    {
        return $this->isTree;
    }

    /**
     * {@inheritDoc}
     */
    public function getLanguage(): ?Field
    {
        return $this->language;
    }

    /**
     * {@inheritDoc}
     */
    public function setLanguage(Field $language): ValueInterface
    {
        $this->language = $language;
        $this->fields[] = $this->language;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setRequests(Field $requests): ValueInterface
    {
        $this->requests = $requests;
        $this->fields[] = $this->requests;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequests(): ?Field
    {
       return $this->requests;
    }

    /**
     * {@inheritDoc}
     */
    public function setMetadescription(Field $metaDescription): ValueInterface
    {
        $this->metaDescription = $metaDescription;
        $this->fields[] = $this->metaDescription;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadescription(): ?Field
    {
        return $this->metaDescription;
    }

    /**
     * {@inheritDoc}
     */
    public function setMetakeywords(Field $metakeywords): ValueInterface
    {
        $this->metaKeywords = $metakeywords;
        $this->fields[] = $this->metaKeywords;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetakeywords(): ?Field
    {
        return $this->metaKeywords;
    }
}
