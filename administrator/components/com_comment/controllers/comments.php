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
 * ccommentControllerComments
 *
 * @since  5
 */
class CcommentControllerComments extends JControllerAdmin
{
	protected $option = 'com_comment';

	protected $text_prefix = 'COM_COMMENT';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws  Exception
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('notifypublish', 'changeStateAndNotify');
		$this->registerTask('notifyunpublish', 'changeStateAndNotify');
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 */
	public function getModel($name = 'Comment', $prefix = 'ccommentModel', $config = array())
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Changes the comment state and sends a mail to the user about the change
	 *
	 * @return void
	 */
	public function changeStateAndNotify()
	{
		$task = $this->getTask() == 'notifyunpublish' ? 'unpublish' : 'publish';
		$status = $this->changeState($task);
		$appl   = JFactory::getApplication();

		if ($status)
		{
			$cid          = JFactory::getApplication()->input->get('cid', array(), 'array');
			$notification = new ccommentHelperNotify($cid[0]);
			$sentemail    = $notification->notify($task);

			if ($sentemail)
			{
				$appl->enqueueMessage(JText::sprintf('COM_COMMENT_MAILTO_SENT', implode('; ', $sentemail)));
			}
			else
			{
				$appl->enqueueMessage(JText::_('COM_COMMENT_COULD_NOT_SEND_MAIL'));
			}
		}

		$this->setRedirect(JRoute::_('index.php?option=com_comment&view=comments', false));
	}

	/**
	 * Change the state of the comment
	 *
	 * @param   string  $task  - publish/unpublish
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function changeState($task)
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$cid  = JFactory::getApplication()->input->get('cid', array(), 'array');
		$data = array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3);

		$value = Joomla\Utilities\ArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid))
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), Joomla\CMS\Log::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			Joomla\Utilities\ArrayHelper::toInteger($cid);

			// Publish the items.
			if (!$model->publish($cid, $value))
			{
				JLog::add($model->getError(), Joomla\CMS\Log::WARNING, 'jerror');

				return false;
			}
			else
			{
				if ($value == 1)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
				}
				elseif ($value == 0)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
				}
				elseif ($value == 2)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_ARCHIVED';
				}
				else
				{
					$ntext = $this->text_prefix . '_N_ITEMS_TRASHED';
				}

				$this->setMessage(JText::plural($ntext, count($cid)));
			}
		}

		return true;
	}
}
