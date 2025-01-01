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

class Id extends Field
{
    /**
     * @var array
     */
    protected $included;

    /**
     * Get the included ids.
     *
     * @return array|null
     * @since 1.0.0
     */
    public function getIncluded(): ?array
    {
        return $this->included;
    }

    /**
     * Set the included ids.
     *
     * @param   string|null  $included
     *
     * @return $this
     * @since 1.0.0
     */
    public function setIncluded(?string $included): Id
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