<?php
/**
 * @package    CComment
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       05.01.15
 *
 * @copyright  Copyright (C) 2008 - 2015 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modeladmin');

/**
 * Class CcommentModelComment
 *
 * @since  5.0
 */
class CcommentModelComment extends JModelAdmin
{
	protected $option = 'com_comment';

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 */
	public function getTable($name = 'Comment', $prefix = 'ccommentTable', $options = array())
	{
		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Get the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_comment.comment', 'comment', array('control' => 'jform', 'load_data' => $loadData));

		$component = $form->getValue('component') ?  $form->getValue('component') : $data['component'];

		// Add the custom fields to the form
		$model = JModelLegacy::getInstance('Customfieldsconfig', 'CompojoomModel');
		$config = ccommentConfig::getConfig($component);
		$customfieldsConfig = $model->getFields('com_comment', $config->id);
		$form->load(CompojoomFormCustom::generateFormXML($customfieldsConfig));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if (property_exists($item, 'customfields'))
		{
			$registry = new JRegistry;
			$registry->loadString($item->customfields);
			$item->customfields = $registry->toArray();
		}

		return $item;
	}


	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array    The default data is an empty array.
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_comment.edit.comment.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}
}
