<?php
/**
 * @name		Slideshow CK
 * @package		com_slideshowck
 * @copyright	Copyright (C) 2019. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @author		Cedric Keiflin - https://www.template-creator.com - https://www.joomlack.fr
 */
 
// No direct access
defined('_JEXEC') or die;

use \Slideshowck\CKView;
use \Slideshowck\CKFof;

class SlideshowckViewStyle extends CKView {

	protected $interface;

	function display($tpl = null) {
		$user = \Joomla\CMS\Factory::getUser();
		$authorised = ($user->authorise('core.edit', 'com_slideshowck') || (count($user->getAuthorisedCategories('com_slideshowck', 'core.edit'))));

		if ($authorised !== true)
		{
			throw new Exception(\Joomla\CMS\Language\Text::_('JERROR_ALERTNOAUTHOR'), 403);
			return false;
		}

		// dislay the page title
		\Joomla\CMS\Toolbar\ToolbarHelper::title(\Joomla\CMS\Language\Text::_('COM_SLIDESHOWCK') . ' - ' . \Joomla\CMS\Language\Text::_('CK_EDITION'), 'logo_slideshowck_large.png');

		// load the styles helper and the interface
		require_once JPATH_SITE . '/administrator/components/com_slideshowck/helpers/ckstyles.php';
		require_once(JPATH_SITE . '/administrator/components/com_slideshowck/helpers/ckinterface.php');

		$this->interface = new \Slideshowck\CKInterface();
		$this->item = $this->get('Item');

		$this->input->set('tmpl', 'component');
		$this->input->set('layout', 'modal');

		parent::display();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar() {
		SlideshowckHelper::loadCkbox();

		\Joomla\CMS\Factory::getApplication()->input->set('hidemainmenu', true);
		$user		= \Joomla\CMS\Factory::getUser();
		$userId		= $user->get('id');
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		$state = $this->get('State');
		$canDo = SlideshowckHelper::getActions();

		// For new records, check the create permission.
		if ($isNew && $user->authorise('core.create', 'com_slideshowck'))
		{
			\Joomla\CMS\Toolbar\ToolbarHelper::apply('style.apply');
			\Joomla\CMS\Toolbar\ToolbarHelper::save('style.save');
			// \Joomla\CMS\Toolbar\ToolbarHelper::save2new('page.save2new');
			\Joomla\CMS\Toolbar\ToolbarHelper::cancel('style.cancel');
		} else
		{
			// Can't save the record if it's checked out.
			if (!$checkedOut)
			{
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId))
				{
					\Joomla\CMS\Toolbar\ToolbarHelper::apply('style.apply');
					\Joomla\CMS\Toolbar\ToolbarHelper::save('style.save');
//					\Joomla\CMS\Toolbar\ToolbarHelper::custom('style.restore', 'archive', 'archive', 'CK_RESTORE', false);
					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ($canDo->get('core.create'))
					{
						// \Joomla\CMS\Toolbar\ToolbarHelper::save2new('page.save2new');
					}
				}
			}

			// If checked out, we can still save
			if ($canDo->get('core.create'))
			{
				// \Joomla\CMS\Toolbar\ToolbarHelper::save2copy('page.save2copy');
			}

			\Joomla\CMS\Toolbar\ToolbarHelper::cancel('style.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
