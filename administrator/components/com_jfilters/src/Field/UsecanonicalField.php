<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Field;

\defined('_JEXEC') or die();

class UsecanonicalField extends JfilterslistField
{
    public function renderField($options = [])
    {
        if($this->getFilter() && $this->getFilter()->getConfig()->getValue()->getRequests()) {
            return parent::renderField($options);
        }
        return '';
    }
}