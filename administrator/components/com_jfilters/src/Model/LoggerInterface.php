<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model;

\defined('_JEXEC') or die();

/**
 * Interface LoggerInterface
 * The only reason for that interface is to declare dependency based on that interface,
 * as the LoggerInterface is already instantiated in the apps container
 * @package Bluecoder\Component\Jfilters\Administrator\Model
 */
interface LoggerInterface extends \Psr\Log\LoggerInterface
{

}
