<?php
/**
 * @package    CComment
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       06.09.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die();

/**
 * Plugins Component Controller
 *
 * @since  1.5
 */
class CcommentControllerSettings extends ccommentController
{
	/**
	 * The constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->registerTask('apply', 'save');
	}

	/**
	 * Create the choose view
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	public function choose()
	{
		$view = $this->getView('Settings', 'html', 'ccommentView');

		// Get/Create the model
		if ($model = $this->getModel('settings'))
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		$view->choose();
	}

	/**
	 * Edit a setting
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	public function edit()
	{
		$input     = JFactory::getApplication()->input;
		$component = $input->getCmd('component');
		$view      = $this->getView('Settings', 'html', 'ccommentView');
		$model     = $this->getModel('Settings', 'ccommentModel');
		$data      = '';
		$view->setModel($model, true);
		$setting = $model->getItem($component);

		if ($setting)
		{
			$data = new JRegistry($setting->params);
		}
		else
		{
			$setting            = new stdClass;
			$setting->id        = 0;
			$setting->component = $component;
			$setting->note      = '';
		}

		$path = JPATH_COMPONENT_ADMINISTRATOR . '/models/forms/settings.xml';
		$form = new JForm('ccommentSettings', array('control' => 'jform'));

		$form->loadFile($path);
		$form->bind($data);

		$view->form = $form;
		$view->item = $setting;
		$view->setLayout('edit');
		$view->display();
	}

	/**
	 * Function to save the configuration
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	public function save()
	{
		JSession::checkToken() or jexit('Invalid Token');
		$appl  = JFactory::getApplication();
		$input = JFactory::getApplication()->input;
		$data  = $input->post->get('jform', array(), 'array');

		$id = $input->getInt('id');

		$registry = new JRegistry($data);

		$saveData = array(
			'id'        => $id,
			'note'      => $input->getString('note'),
			'component' => $input->getString('component'),
			'params'    => $registry->toString()
		);

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_comment/tables');
		$row = JTable::getInstance('Setting', 'CommentTable');
		$row->load($id);

		if (!$row->bind($saveData))
		{
			throw new Exception('Error binding data');
		}

		if (!$row->store())
		{
			throw new Exception('Error binding saving data');
		}

		switch ($input->getCmd('task'))
		{
			case 'apply' :
				$link = JRoute::_('index.php?option=com_comment&task=settings.edit&component=' . $row->component, false);
				break;
			case 'save':
				$link = JRoute::_('index.php?option=com_comment&view=settings', false);
				break;
		}

		$appl->enqueueMessage(JText::_('COM_COMMENT_SETTING_SAVED'));
		$appl->redirect($link);
	}

	/**
	 * Revemo a setting
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	public function remove()
	{
		JSession::checkToken() or jexit('Invalid Token');
		$mainframe = JFactory::getApplication();
		$cid       = JRequest::getVar('cid', array(), '', 'array');
		$database  = JFactory::getDBO();

		if (count($cid))
		{
			$cids  = implode(',', $cid);
			$query = 'DELETE FROM ' . $database->qn('#__comment_setting')
				. ' WHERE id IN (' . $cids . ')';
			$database->setQuery($query);

			if (!$database->execute())
			{
				echo "<script> alert('" . $database->getErrorMsg() . "');
		    window.history.go(-1); </script>";
			}
		}

		$mainframe->redirect('index.php?option=com_comment&view=settings');
	}

	/**
	 * Cancel button redirect
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	public function cancel()
	{
		$mainframe = JFactory::getApplication();
		$mainframe->redirect('index.php?option=com_comment&view=settings');
	}
}
