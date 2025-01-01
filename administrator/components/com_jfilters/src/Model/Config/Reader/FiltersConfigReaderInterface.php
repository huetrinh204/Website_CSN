<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader;

\defined('_JEXEC') or die();

interface FiltersConfigReaderInterface extends ConfigReaderInterface
{
    /**
     * Get Filters config
     *
     * @return array
     * @since 1.0.0
     */
    public function getFiltersConfig():array;
}
