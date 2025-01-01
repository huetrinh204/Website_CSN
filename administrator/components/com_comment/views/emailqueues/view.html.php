<?php
/**
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       27.02.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.viewlegacy');
/**
 * Class CcommentViewEmailQueue
 *
 * @since  5.0
 */
class CcommentViewEmailQueues extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$this->items = $this->get('items');
		$this->state = $this->get('state');
		$this->pagination = $this->get('Pagination');
		parent::display($tpl);
	}
}
