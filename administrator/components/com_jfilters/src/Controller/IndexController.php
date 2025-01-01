<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Controller;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\IndexModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Session\Session;

class IndexController extends AdminController
{
	/**
	 * Get the model.
	 *
	 * @param   string        $name
	 * @param   string        $prefix
	 * @param   array|bool[]  $config
	 *
	 * @return bool|\Joomla\CMS\MVC\Model\BaseDatabaseModel
	 * @since 1.0.0
	 */
	public function getModel($name = 'Index', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Delete previous indexes and re-index
	 *
	 * @throws \ReflectionException
	 * @since 1.0.0
	 */
	public function delete()
	{
		// Check for a valid token. If invalid, send a 403 with the error message.
		if (!Session::checkToken('request'))
		{
			$this->app->setHeader('status', '403');
			$response = new \stdClass();
			$response->error =Text::_('JINVALID_TOKEN_NOTICE');
			echo json_encode($response);
			return;
		}

		// Remove the script time limit.
		@set_time_limit(0);
		/** @var IndexModel $model */
		$model = $this->getModel('Index', 'Administrator');
		$deletedPks = $model->delete();
		$response = new \stdClass();
		$response->deleted = count($deletedPks);
		Factory::getApplication()->getLanguage()->load('com_finder');
		$response->message = Text::sprintf('COM_FINDER_N_ITEMS_DELETED', $response->deleted);
		echo json_encode($response);
	}
}