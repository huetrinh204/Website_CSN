<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Field;

\defined('_JEXEC') or die();

trait RenderFieldTrait
{
    /**
     * Change the layout data such as the label.
     * Print the indication of the PRO version fields in the FREE edition
     * 
     * @return array
     * @since 1.0.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        if ($this->__get('data-locked') && isset($data['label'])) {
            $data['label'] .= ' <span class="badge bg-secondary"><span class="icon-lock" aria-hidden="true"></span>PRO</span>';
        }

        return $data;
    }
}