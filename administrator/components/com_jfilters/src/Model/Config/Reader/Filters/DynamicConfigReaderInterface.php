<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\Filters;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\ConfigReaderInterface;

interface DynamicConfigReaderInterface extends ConfigReaderInterface
{
    /**
     * Get the Dynamic Filters config
     *
     * @return array
     * @since 1.0.0
     */
    public function getDynamicFiltersConfig(): array;
}
