<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;

interface DynamicFilterInterface extends FilterInterface
{
    /**
     * Set the dynamic type of the filter
     *
     * @param   string  $type
     *
     * @return FilterInterface
     * @since 1.0.0
     */
    public function setType(string $type) : FilterInterface;

    /**
     * Get the dynamic type of the filter
     *
     * @return string|null
     * @since 1.0.0
     */
    public function getType() : ?string;
}
