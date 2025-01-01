<?php
/**
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       27.02.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');


/**
 * Class CcommentTableComment
 *
 * @since  5.0
 */
class CcommentTableComment extends JTable
{
	/**
	 * The constructor
	 *
	 * @param   JDatabaseDriver  &$db  - JDatabaseDriver object.
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__comment', 'id', $db);
	}

	/**
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $array   An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return bool
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['customfields']) && is_array($array['customfields']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['customfields']);
			$array['customfields'] = (string) $registry;
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Stores a row in the database
	 *
	 * @param   bool  $updateNulls  - True to update fields even if they are null.
	 *
	 * @return bool
	 */
	public function store($updateNulls = false)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		if ($this->id)
		{
			$this->modified = $date->toSql();
			$this->modified_by = $user->get('id');
		}
		else
		{
			$this->date = $date->toSql();
			$this->unsubscribe_hash = md5(JSession::getFormToken() . time() . mt_rand(1, 100));
			$this->moderate_hash = md5($this->ip . JVERSION . JSession::getFormToken() . time() . mt_rand(1, 10000));

		}

		return parent::store($updateNulls);
	}
}
