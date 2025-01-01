<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field\Id;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field\Request;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field\Type;

/**
 * Class Section
 *
 * Each Section is represented as the final group of nodes in the xml
 * e.g. definition, value valueRefItem, found in the filters.xml are transformed to Sections
 *
 * Each section has Fields (each row) and each field has several properties (e.g. dbColumn) as represented in the xml
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config
 */
class Section implements SectionInterface
{
    /**
     * @var Field[]
     */
    protected $fields;

    /**
     * @var string
     */
    protected $dbTableName;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var FieldFactory
     */
    protected $fieldFactory;

    /**
     * Section constructor.
     * @param \Bluecoder\Component\Jfilters\Administrator\Model\Config\FieldFactory $fieldFactory
     */
    public function __construct(FieldFactory $fieldFactory)
    {
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function setDbTable(string $dbTableName): SectionInterface
    {
        $this->dbTableName = $dbTableName;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDbTable()
    {
        return $this->dbTableName;
    }

    /**
     * {@inheritdoc}
     */
    public function setClass(string $class): SectionInterface
    {
        if (\class_exists($class)) {
            $this->class = $class;
        } else {
            throw new \InvalidArgumentException(sprintf("The class attribute: '%s' ,does not exist.", $class));
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function setFields(\SimpleXMLElement $data): SectionInterface
    {
        foreach ($data->children() as $key => $subnode) {

            /*
             * Remove any '_' and convert their parts to ucfirst.
             * The method names follow this pattern setPart1Part2()
             */
            $keyNormalized = array_map(function ($keyPart) {
                return ucfirst($keyPart);
            }, explode('_', $key));

            $methodName = 'set' . implode($keyNormalized);
            if (method_exists($this, $methodName)) {
                $field = $this->generateField($subnode, $key);
                if ($field) {
                    $this->$methodName($field);
                }
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Generate a Field from xml
     *
     * @param \SimpleXMLElement $node
     * @param string $fieldName
     * @return Field
     * @since 1.0.0
     */
    protected function generateField(\SimpleXMLElement $node, string $fieldName): Field
    {
        $children = [];
        $className = Field::class;

        if($fieldName === 'id') {
            $className = Id::class;
        }
        elseif($fieldName === 'request') {
            $className = Request::class;
        }
        elseif($fieldName === 'type') {
            $className = Type::class;
        }

        /** @var Field $field */
        $field = $this->fieldFactory->create([], $className);
        $field->setName($fieldName);
        $field->setParentSection($this);

        $value = (string)$node;
        $value = trim($value);
        if (!empty($value) || $value == "0") {
            $field->setValue($value);
        }

        foreach ($node->attributes() as $attrName => $attrValue) {
            $methodName2 = 'set' . ucfirst($attrName);
            $attrValue = (string)$attrValue;
            if (method_exists($field, $methodName2) && $attrValue) {
                $field->$methodName2($attrValue);
            }
        }
        foreach ($node->children() as $key => $subnode) {
            $children [] = $this->generateField($subnode, $key);
        }

        if($children) {
            $field->setChildren($children);
        }
        return $field;
    }
}
