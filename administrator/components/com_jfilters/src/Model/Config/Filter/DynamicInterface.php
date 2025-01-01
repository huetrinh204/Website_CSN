<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter;

\defined('_JEXEC') or die();

interface DynamicInterface
{
    /**
     * Get the name of the field/filter
     *
     * @return string
     * @since 1.0.0
     */
    public function getName() : string ;

    /**
     * Set the name of the field/filter
     *
     * @param   string  $name
     *
     * @return DynamicInterface
     * @since 1.0.0
     */
    public function setName(string $name) : DynamicInterface;

    /**
     * Get the data type of the field/filter
     *
     * @return string|null
     * @since 1.3.0
     */
    public function getDataType() : ?string ;

    /**
     * Set the data type of the field/filter
     *
     * @param   string  $dataType
     *
     * @return DynamicInterface
     * @since 1.3.0
     */
    public function setDataType(?string $dataType) : DynamicInterface;

    /**
     * Generate filter or not
     *
     * @return bool
     * @since 1.0.0
     */
    public function getGenerateFilter() : bool;

    /**
     * Set generate filter
     *
     * @param   bool  $generateFilter
     *
     * @return DynamicInterface
     * @since 1.0.0
     */
    public function setGenerateFilter(bool $generateFilter) : DynamicInterface;

    /**
     * Get the available displays for that filter
     *
     * @return array|null
     * @since 1.0.0
     */
    public function getDisplays() : ?array;

    /**
     * Set the available displays for that filter
     * @param   array  $displays
     *
     * @return DynamicInterface
     * @since 1.0.0
     */
    public function setDisplays(array $displays) : DynamicInterface;

    /**
     * Generate the displays from a passed xml containing the displays node
     *
     * @param   \SimpleXMLElement  $data
     *
     * @return DynamicInterface
     * @since 1.0.0
     */
    public function generateDisplays(\SimpleXMLElement $data) : DynamicInterface;

    /**
     * Set the additional sort fields for that filter
     * @param   array  $sortFields
     *
     * @return DynamicInterface
     * @since 1.3.0
     */
    public function setAdditionalSortFields(array $sortFields) : DynamicInterface;

    /**
     * Get the additional sort fields for that filter
     *
     * @return array|null
     * @since 1.3.0
     */
    public function getAdditionalSortFields() : ?array;

    /**
     * Generate the Additional Order Fields from a passed xml containing the 'additionalOrderingFields' node
     *
     * @param   \SimpleXMLElement  $data
     *
     * @return DynamicInterface
     * @since 1.0.0
     */
    public function generateAdditionalSortFields(\SimpleXMLElement $data) : DynamicInterface;
}
