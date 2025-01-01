<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\BaseObject;

/**
 * Class Field
 *
 * The config field class
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config
 */
class Field extends BaseObject
{
    /**
     * @var string
     * @since 1.0.0
     */
    protected $name;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $value = '';

    /**
     * @var int
     * @since 1.3.0
     */
    protected $edition;

    /**
     * @var SectionInterface
     * @since 1.0.0
     */
    protected $parentSection;

    /**
     * The attributes of the Field
     *
     * @var array
     * @since 1.0.0
     */
    protected $attribute = [];

    /**
     * The children of the Field
     *
     * @var array
     * @since 1.0.0
     */
    protected $children = [];

    /**
     * Return the name of the field
     *
     * @return string
     * @throws \RuntimeException
     * @since 1.0.0
     */
    public function getName(): string
    {
        if ($this->name === null) {
            throw new \RuntimeException('No name is specified for a field. Check the filters.xml');
        }
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     * @since 1.0.0
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set the parent Section of that field
     *
     * @param SectionInterface $parentSection
     * @since 1.0.0
     */
    public function setParentSection(SectionInterface $parentSection)
    {
        $this->parentSection = $parentSection;
    }

    /**
     * Get the parent Section of that field
     *
     * @return SectionInterface
     * @since 1.0.0
     */
    public function getParentSection() : SectionInterface
    {
        if ($this->parentSection === null) {
            throw new \RuntimeException('No parent Section is specified for a field. Check the filters.xml');
        }
        return $this->parentSection;
    }

    /**
     * @return string
     * @since 1.0.0
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return $this
     * @since 1.0.0
     */
    public function setValue(string $value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Set the edition of the Field
     *
     * @param int|null $edition
     * @return $this
     * @since 1.3.0
     */
    public function setEdition(?int $edition)
    {
        $this->edition = $edition;
        return $this;
    }

    /**
     * Get the edition of the Field
     *
     * @return int|null
     * @since 1.3.0
     */
    public function getEdition() : ?int
    {
        return $this->edition;
    }

    /**
     * Get the database column of a field
     * @return string|null
     * @since 1.0.0
     */
    public function getDbColumn(): ?string
    {
        return isset($this->attribute['dbColumn']) ? $this->attribute['dbColumn'] : null;
    }

    /**
     * @param string $dbColumn
     * @return $this
     * @since 1.0.0
     */
    public function setDbColumn(string $dbColumn)
    {
        $this->attribute['dbColumn'] = $dbColumn;
        return $this;
    }

    /**
     * Returns the reference. Should be a Field from another Section
     *
     * @return Field|null
     * @since 1.0.0
     */
    public function getReference(): ?Field
    {
        return $this->attribute['reference'];
    }

    /**
     * Set the reference field
     * Proxy to get the value of the 'reference' attribute
     *
     * @param Field|string $referenceField
     * @return $this
     * @since 1.0.0
     */
    public function setReference($referenceField)
    {
        if($referenceField instanceof Field) {
            $this->attribute['reference'] = $referenceField;
            return $this;
        }

        /*
         * The reference attribute should have the format
         * {group.section.field}
         *
         * The group can be either "this" or "context" (these are the groups at the moment of writing this).
         * "this" refers to the current group. "context" refers to the context that will be used
         *
         * e.g. {context.item.id}
         * In that case the context can be resolved only in the filter object
         * as we are not aware of the filter's context in that class
         */
        preg_match('/^{(this|context)+\.[a-z]+\.[a-z]+}$/i', $referenceField, $matches);
        if ($matches[0]) {
            $this->attribute['reference'] = $matches[0];
        } else {
            throw new \RuntimeException(sprintf('Invalid \'reference\' attribute for the field with the name: %', $this->getName()));
        }
        return $this;
    }

    /**
     * Proxy to get the value of the 'translate' attribute
     *
     * @return bool
     * @since 1.0.0
     */
    public function getTranslate(): bool
    {
        return isset($this->attribute['translate']) ? $this->attribute['translate'] : false;
    }

    /**
     * @param bool $translate
     * @return $this
     * @since 1.0.0
     */
    public function setTranslate(bool $translate)
    {
        $this->attribute['translate'] = $translate;
        return $this;
    }

    /**
     * Proxy to get the value of the 'type' attribute
     *
     * @return string
     * @since 1.0.0
     */
    public function getType(): string
    {
        return isset($this->attribute['type']) ? $this->attribute['type'] : 'string';
    }

    /**
     * Set the 'type' attribute of the Field
     *
     * @param string $type
     * @return $this
     * @since 1.0.0
     */
    public function setType(string $type)
    {
        $this->attribute['type'] = $type;
        return $this;
    }

    /**
     * Get the attributes of the Field
     *
     * @return array
     * @since 1.0.0
     */
    public function getAttributes(): array
    {
        return $this->attribute;
    }

    /**
     * Set the children of the Field
     *
     * @param array $children
     * @return $this
     * @since 1.0.0
     */
    public function setChildren(array $children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * Get the children of the Field
     *
     * @return array
     * @since 1.0.0
     */
    public function getChildren() : array
    {
        return $this->children;
    }
}
