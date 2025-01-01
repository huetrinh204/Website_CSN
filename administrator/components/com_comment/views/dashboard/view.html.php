<?php
/**
 * @author     Daniel Dimitrov - compojoom.com
 * @date       02.04.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */


defined('_JEXEC') or die('Restricted access');

/**
 * Description of viewhtml
 *
 * @author Daniel Dimitrov
 */
jimport('joomla.application.component.viewlegacy');
class ccommentViewDashboard extends JViewLegacy
{
	public function display($tpl = null)
	{
		$updateModel = JModelLegacy::getInstance('Updates', 'CCommentModel');
		$statsModel = JModelLegacy::getInstance('Stats', 'CCommentModel');

		// Run the automatic database check
		$updateModel->checkAndFixDatabase();

		$this->currentVersion = $updateModel->getVersion();
		$this->updatePlugin = $updateModel->isUpdatePluginEnabled('Ccomment');

		$this->needsdlid = $updateModel->needsDownloadID();
		$this->needscoredlidwarning = $updateModel->mustWarnAboutDownloadIDInCore();
		$this->updateStats = $statsModel->needsUpdate();

		// Run the automatic update site refresh
		$updateModel->refreshUpdateSite();

		$model = $this->getModel();
		$stats = $model->getStats('engagement');
		$this->latest = $model->getLatest();

		$this->statsArray = array(
			array(JText::_('COM_COMMENT_DATE'), JText::_('COM_COMMENT_COMMENTS'), JText::_('COM_COMMENT_USERS'), JText::_('COM_COMMENT_USERS_IP'))
		);

		foreach ($stats as $stat)
		{
			$this->statsArray[] = array($stat->date, (int) $stat->count, (int) $stat->users, (int) $stat->ip);
		}

		parent::display($tpl);
	}
}
