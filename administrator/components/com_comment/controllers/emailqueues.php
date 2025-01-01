<?php
/**
 * @package    CComment
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       11.06.15
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */


defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controlleradmin');

/**
 * ccommentControllerEmailQueue
 *
 * @since  5
 */
class CcommentControllerEmailQueues extends JControllerAdmin
{
	protected $option = 'com_comment';

	protected $text_prefix = 'COM_COMMENT_EMAIL_QUEUE';

	/**
	 * Function to get a specified model
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The model prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return Object the model
	 */
	public function getModel($name = 'emailQueue', $prefix = 'ccommentModel', $config = array())
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Function to delete all records
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	public function deleteAll()
	{
		$array = array();
		$model = $this->getModel();
		$model->delete($array, true);
		$this->setRedirect(JRoute::_('index.php?option=com_comment&view=emailQueues', false));
	}

	/**
	 * Function to send mails to the selected users
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	public function sendMail()
	{
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else {
			$model=$this->getModel('$emailqueues');
			$model->setState('ids', $cid);
			CcommentHelperQueue::sendMail($model->getItems());
		}
		$this->setRedirect(JRoute::_('index.php?option=com_comment&view=emailQueues', false));

	}
}
