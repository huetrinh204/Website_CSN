<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;

class Display extends Field
{
    /**
     * @var bool
     * @since 1.0.0
     */
    protected $multiselect = false;

    /**
     * @var bool
     * @since 1.14.0
     */
    protected $isRange = false;

    /**
     * Set if a display field is multiselect
     *
     * @param   bool  $multiselect
     *
     * @return $this
     * @since 1.0.0
     */
    public function setMultiselect(bool $multiselect) : Display
    {
        $this->multiselect = $multiselect;
        return $this;
    }

    /**
     * Get if is multi-select or not
     *
     * @return bool
     * @since 1.0.0
     */
    public function getMultiselect() : bool
    {
        return $this->multiselect;
    }

    /**
     * Set if a display field is multiselect
     *
     * @param   bool  $multiselect
     *
     * @return $this
     * @since 1.0.0
     */
    public function setRange(bool $isRange) : Display
    {
        $this->isRange = $isRange;
        return $this;
    }

    /**
     * Get if is multi-select or not
     *
     * @return bool
     * @since 1.0.0
     */
    public function getRange() : bool
    {
        return $this->isRange;
    }
}
