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

jimport('joomla.application.component.modelAdmin');

/**
 * Class ccommentModelEmailQueue
 *
 * @since  5.0
 */
class CcommentModelEmailQueue extends JModelAdmin
{
	/**
	 * Function to load a specified table.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return JTable  A JTable object
	 */
	public function getTable($name = 'queue', $prefix = 'ccommentTable', $options = array())
	{
		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Method to delete a selected items or all of them.
	 *
	 * @param   integer  &$cid       The id of the primary key.
	 *
	 * @param   boolean  $deleteAll  Only the selected items or all of them.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function delete(&$cid, $deleteAll = false)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		if ($deleteAll == false)
		{
			return parent::delete($cid);
		}
		else
		{
			$query->delete('#__comment_queue');
			$this->_db->setQuery($query);

		}

		try
		{
			$this->_db->execute();
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage('An error has occured', 'error');

			return false;
		}
		JFactory::getApplication()->enqueueMessage('Successfully deleted');

		return true;


	}

	/**
	 * Dummy to get around a php error. There is no single view so we don't need the form.
	 *
	 * @param   array    $data      An empty array.
	 * @param   boolean  $loadData  Boolean with a default value of true.
	 *
	 * @return  void.
	 */
	public function getForm($data = array(), $loadData = true)
	{
	}
}
