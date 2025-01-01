<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;

class FilterController extends FormController
{
    /**
     * Method override to check if you can edit an existing record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  boolean
     *
     * @since   1.0.0
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        $recordId = (int) isset($data[$key]) ? $data[$key] : 0;

        // Since there is no asset tracking, fallback to the component permissions.
        if (!$recordId)
        {
            return parent::allowEdit($data, $key);
        }

        $user = $this->app->getIdentity();

        // Check "edit" permission on record asset (explicit or inherited)
        if ($user->authorise('core.edit', 'com_jfilters.filter.' . $recordId))
        {
            return true;
        }

        return false;
    }
}