<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option;
use Joomla\CMS\Uri\Uri;

class Clear extends Option
{
    public function getLink(bool $clone = false, bool $toggleVar = true): Uri
    {
        if ($this->url == null) {
            $this->url = $this->uri->getBase($this);
        }

        return $this->url;
    }
}
