<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Exception\MissingNodeException;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section\DefinitionInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section\ValueInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section\ValueItemRefInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;

/**
 * Class Filter
 *
 * The config class for the Filter
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Model
 */
class Filter implements FilterInterface
{
    /**
     * @var ObjectManager
     * @since 1.0.0
     */
    protected $objectManager;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $name;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $label;

    /**
     * @var bool
     * @since 1.0.0
     */
    protected $dynamic;

    /**
     * @var bool
     * @since 1.0.0
     */
    protected $root;

    /**
     * @var DefinitionInterface
     * @since 1.0.0
     */
    protected $definition;

    /**
     * @var ValueInterface
     * @since 1.0.0
     */
    protected $value;

    /**
     * @var ValueItemRefInterface
     * @since 1.0.0
     */
    protected $valueItemRef;

    /**
     * Filter constructor.
     */
    public function __construct()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * {@inheritDoc}
     */
    public function getSections(): array
    {
        return [
            $this->getDefinition(),
            $this->getValue(),
            $this->getValueItemRef()
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function setName(string $name): ConfigInterface
    {
        $this->name = $name;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * {@inheritDoc}
     */
    public function setLabel(string $label): FilterInterface
    {
        $this->label = $label;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isDynamic(): bool
    {
        if ($this->dynamic == null) {
            $this->dynamic = false;
        }
        return $this->dynamic;
    }

    /**
     * {@inheritDoc}
     */
    public function setIsDynamic(bool $isDynamic = false): FilterInterface
    {
        $this->dynamic = $isDynamic;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setIsRoot(bool $isRoot): FilterInterface
    {
        $this->root = $isRoot;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isRoot(): bool
    {
        if ($this->root == null) {
            $this->root = false;
        }
        return $this->root;
    }


    /**
     * {@inheritDoc}
     */
    public function setDefinition(DefinitionInterface $definition): FilterInterface
    {
        $this->definition = $definition;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefinition() : DefinitionInterface
    {
        return $this->definition;
    }

    /**
     * {@inheritDoc}
     */
    public function setValue(ValueInterface $value): FilterInterface
    {
        $this->value = $value;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(): ValueInterface
    {
        return $this->value;
    }

    /**
     * {@inheritDoc}
     */
    public function setValueItemRef(ValueItemRefInterface $valueItemRef): FilterInterface
    {
        $this->valueItemRef = $valueItemRef;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getValueItemRef(): ValueItemRefInterface
    {
        return $this->valueItemRef;
    }

    /**
     * {@inheritDoc}
     */
    public function generateDefinition(\SimpleXMLElement $data): DefinitionInterface
    {
        if (!isset($data->definition)) {
            throw new MissingNodeException(sprintf('The \'definition\' node is missing from the configuration of the filter: %s',
                $data['name']));
        }
        /** @var  DefinitionInterface $definitionConfig */
        $definitionConfig = $this->objectManager->createObject(DefinitionInterface::class);
        $definitionData = $data->definition;

        if (!empty($definitionData['dbTable'])) {
            $definitionConfig->setDbTable($definitionData['dbTable']);
        }

        if (!empty($definitionData['class'])) {
            $definitionConfig->setClass($definitionData['class']);
        }

        $definitionConfig->setFields($definitionData);
        return $definitionConfig;
    }

    public function generateValue(\SimpleXMLElement $data): ValueInterface
    {
        if (!isset($data->value)) {
            throw new MissingNodeException(sprintf('The \'value\' node is missing from the configuration of the filter: %s',
                $data['name']));
        }
        /** @var ValueInterface $valueConfig */
        $valueConfig = $this->objectManager->createObject(ValueInterface::class);
        $valueData = $data->value;

        if (!empty($valueData['dbTable'])) {
            $valueConfig->setDbTable($valueData['dbTable']);
        }

        if (!empty($valueData['tree'])) {
            $isTree = false;
            if (!empty($valueData['tree']) && $valueData['tree'] == 'true') {
                $isTree = true;
            }
            $valueConfig->setIsTree($isTree);
        }

        if (!empty($valueData['class'])) {
            $valueConfig->setClass($valueData['class']);
        }

        $valueConfig->setFields($valueData);
        return $valueConfig;
    }

    public function generateValueItemRef(\SimpleXMLElement $data): ValueItemRefInterface
    {
        if (!isset($data->valueRefItem)) {
            throw new MissingNodeException(sprintf('The \'valueRefItem\' node is missing from the configuration of the filter: %s',
                $data['name']));
        }
        /** @var  ValueItemRefInterface $valueItemRefConfig */
        $valueItemRefConfig = $this->objectManager->createObject(ValueItemRefInterface::class);
        $valueItemRefData = $data->valueRefItem;

        if (!empty($valueItemRefData['dbTable'])) {
            $valueItemRefConfig->setDbTable($valueItemRefData['dbTable']);
        }

        if (!empty($valueItemRefData['class'])) {
            $valueItemRefConfig->setClass($valueItemRefData['class']);
        }

        $valueItemRefConfig->setFields($valueItemRefData);
        return $valueItemRefConfig;
    }
}
