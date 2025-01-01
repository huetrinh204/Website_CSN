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

/**
 * Interface FilterInterface
 *
 * The Interface for the Filter Configuration.
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config
 */
interface FilterInterface extends ConfigInterface
{

    /**
     * The section names
     */
    const SECTION_DEFINITION_NAME = 'definition';

    const SECTION_VALUE_NAME = 'value';

    const SECTION_VALUE_ITEM_REF_NAME = 'valueRefItem';

    /**
     * Get the config label (used in the views)
     *
     * @return string|null
     * @since 1.0.0
     */
    public function getLabel() : ?string;

    /**
     * Set the config label (used in the views)
     *
     * @param   string  $label
     *
     * @return FilterInterface
     * @since 1.0.0
     */
    public function setLabel(string $label) : FilterInterface;

    /**
     * Defines if the filter/'s is dynamic or not
     *
     * @param bool $isDynamic
     * @return FilterInterface
     */
    public function setIsDynamic(bool $isDynamic): FilterInterface;

    /**
     * Is Dynamic filter?
     *
     * @return bool
     * @since 1.0.0
     */
    public function isDynamic(): bool;

    /**
     * Defines if the filter is root or not
     *
     * @param bool $isRoot
     * @return FilterInterface
     * @since 1.0.0
     */
    public function setIsRoot(bool $isRoot): FilterInterface;

    /**
     * Is root filter?
     *
     * @return bool
     * @since 1.0.0
     */
    public function isRoot(): bool;

    /**
     * @param DefinitionInterface $definition
     * @return FilterInterface
     * @since 1.0.0
     */
    public function setDefinition(DefinitionInterface $definition): FilterInterface;

    /**
     * @return \Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section\DefinitionInterface
     * @since 1.0.0
     */
    public function getDefinition(): DefinitionInterface;

    /**
     * Generate the definition from a \SimpleXMLElement
     *
     * @param \SimpleXMLElement $data
     * @return DefinitionInterface
     * @throws MissingNodeException
     * @since 1.0.0
     */
    public function generateDefinition(\SimpleXMLElement $data): DefinitionInterface;

    /**
     * @param \Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section\ValueInterface $value
     * @return FilterInterface
     */
    public function setValue(ValueInterface $value): FilterInterface;

    /**
     * @return \Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section\ValueInterface
     * @since 1.0.0
     */
    public function getValue(): ValueInterface;

    /**
     * Generate the Value from a \SimpleXMLElement
     *
     * @param \SimpleXMLElement $data
     * @return ValueInterface
     * @throws MissingNodeException
     * @since 1.0.0
     */
    public function generateValue(\SimpleXMLElement $data): ValueInterface;

    /**
     * @param ValueItemRefInterface $value
     * @return FilterInterface
     * @since 1.0.0
     */
    public function setValueItemRef(ValueItemRefInterface $valueItemRef);

    /**
     * @return ValueItemRefInterface
     * @since 1.0.0
     */
    public function getValueItemRef(): ValueItemRefInterface;

    /**
     * Generate the ValueItemRef from a \SimpleXMLElement
     *
     * @param \SimpleXMLElement $data
     * @return ValueItemRefInterface
     * @throws MissingNodeException
     * @since 1.0.0
     */
    public function generateValueItemRef(\SimpleXMLElement $data): ValueItemRefInterface;

}
