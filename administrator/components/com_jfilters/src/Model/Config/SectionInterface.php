<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config;

\defined('_JEXEC') or die();

/**
 * Interface DefinitionInterface
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config
 */
interface SectionInterface
{
    /**
     * Set the database table, related with that section
     *
     * @param string $dbTableName
     * @return SectionInterface
     * @since 1.0.0
     */
    public function setDbTable(string $dbTableName): SectionInterface;

    /**
     * Get the database table, related with that section
     *
     * @return null|string
     * @since 1.0.0
     */
    public function getDbTable();

    /**
     * Set the type class that the objects will be mapped upon
     *
     * @param string $class
     * @return SectionInterface
     * @since 1.0.0
     */
    public function setClass(string $class): SectionInterface;

    /**
     * Get the type class that the objects will be mapped upon
     *
     * @return null|string
     * @since 1.0.0
     */
    public function getClass();

    /**
     * Set the fields for that section from the xml data
     *
     * @param \SimpleXMLElement $data
     * @return SectionInterface
     * @since 1.0.0
     */
    public function setFields(\SimpleXMLElement $data): SectionInterface;

    /**
     * Get the fields of the section.
     * Make sure that the fields are stored in the $fields property in each SectionInterface
     *
     * @return Field[]
     * @since 1.0.0
     */
    public function getFields(): array;
}
