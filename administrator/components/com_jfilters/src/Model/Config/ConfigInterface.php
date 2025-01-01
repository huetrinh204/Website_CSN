<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config;

\defined('_JEXEC') or die();

interface ConfigInterface
{
    /**
     * Get the name of the config item
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set the name of the config item
     *
     * @param string $name
     * @return ConfigInterface
     */
    public function setName(string $name): ConfigInterface;

    /**
     * Get the sections (SectionInterface) of the config item
     *
     * @return SectionInterface[]
     */
    public function getSections(): array;
}
