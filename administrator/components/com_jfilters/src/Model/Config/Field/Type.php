<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;

/**
 * Class Type for field "type". See filters.xml definition section
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config\Field
 */
class Type extends Field
{
    /**
     * @var array
     */
    protected $excluded;

    /**
     * @var array
     */
    protected $included;

    /**
     * Get the excluded types
     *
     * @return array|null
     * @since 1.0.0
     */
    public function getExcluded(): ?array
    {
        return $this->excluded;
    }

    /**
     * Set the excluded types.
     *
     * @param   string|null  $excluded
     *
     * @return $this
     * @since 1.0.0
     */
    public function setExcluded(?string $excluded): Type
    {
        if ($excluded !== null) {
            // We can use comma separated values
            $excluded = explode(',', $excluded);
            $excluded = array_map('trim', $excluded);
            $this->excluded = $excluded;
        }

        return $this;
    }

    /**
     * Get the included types
     *
     * @return array|null
     * @since 1.0.0
     */
    public function getIncluded(): ?array
    {
        return $this->included;
    }

    /**
     * Set the included types.
     *
     * @param   string|null  $included
     *
     * @return $this
     * @since 1.0.0
     */
    public function setIncluded(?string $included): Type
    {
        if ($included !== null) {
            // We can use comma separated values
            $included = explode(',', $included);
            $included = array_map('trim', $included);
            $this->included = $included;
        }

        return $this;
    }
}