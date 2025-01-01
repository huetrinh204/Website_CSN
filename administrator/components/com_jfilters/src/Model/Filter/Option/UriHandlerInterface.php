<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionInterface;
use Joomla\CMS\Uri\Uri;

/**
 * Interface UriInterface
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option
 *
 * @since 1.0.0
 */
interface UriHandlerInterface
{
    /**
     * The component that performs the filtering
     */
    public const COMPONENT = 'com_jfilters';

    /**
     * The view for showing the results in the front-end (com_jfilters/src/View)
     */
    public const VIEW = 'results';

    /**
     * @param OptionInterface $option
     * @return UriHandler
     * @since 1.0.0
     */
    public function getBase(OptionInterface $option): Uri;

    /**
     * @param   OptionInterface  $option
     * @param   bool             $toggleVar  Create the toggle effect for the multi-select by adding and removing the var in each request.
     *
     * @return UriHandler
     * @since 1.0.0
     */
    public function get(OptionInterface $option, bool $toggleVar = true): Uri;
}
