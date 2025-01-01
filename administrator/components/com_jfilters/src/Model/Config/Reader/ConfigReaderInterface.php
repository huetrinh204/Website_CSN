<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;

interface ConfigReaderInterface
{
    /**
     * @param   \SimpleXMLElement  $xml
     *
     * @return ConfigReaderInterface
     * @since 1.0.0
     */
    public function setXML(\SimpleXMLElement $xml): ConfigReaderInterface;

    /**
     * Set a logger
     *
     * Useful for unit tests
     *
     * @param   LoggerInterface  $logger
     *
     * @return ConfigReaderInterface
     * @since 1.0.0
     */
    public function setLogger(LoggerInterface $logger): ConfigReaderInterface;
}
