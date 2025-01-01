<?php
/**
 * @package    com_hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       23.01.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class HotspotsViewHotspots
 *
 * @since  2.0
 */
class ccommentViewCustomfields extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->items = $this->get('items');
		$this->state = $this->get('state');
		$this->pagination = $this->get('pagination');
		$this->canDo = CompojoomComponentHelper::getActions('', 'component', 'com_comment');

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Adds a toolbar
	 *
	 * @return void
	 */
	public function addToolbar()
	{
		if (CCOMMENT_PRO)
		{
			if ($this->canDo->get('core.admin'))
			{
				JToolBarHelper::addNew('customfield.add');
				JToolBarHelper::editList('customfield.edit');
				JToolBarHelper::publishList('customfields.publish');
				JToolBarHelper::unpublishList('customfields.unpublish');
			}

			JToolBarHelper::deleteList(JText::_('LIB_COMPOJOOM_DO_YOU_REALLY_WANTO_TO_REMOVE_THIS_CUSTOMFIELD'), 'customfields.delete');
		}


		JToolBarHelper::help('screen.comment', false, 'https://compojoom.com/support/documentation/ccomment?tmpl=component');

	}
}
