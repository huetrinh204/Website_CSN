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

class SlideshowckViewStyles extends CKView {

	protected $items;

	protected $pagination;

	protected $state;

	protected $toolbar;

	/**
	 * Display the view
	 */
	public function display($tpl = null) {

		$user = \Joomla\CMS\Factory::getUser();
		$authorised = ($user->authorise('core.edit', 'com_slideshowck') || (count($user->getAuthorisedCategories('com_slideshowck', 'core.edit'))));

		if ($authorised !== true)
		{
			throw new Exception(\Joomla\CMS\Language\Text::_('JERROR_ALERTNOAUTHOR'), 403);
			return false;
		}

		$this->items = $this->get('Items');

		$this->toolbar = $this->getToolbar();

//		$this->input->set('tmpl', 'component');
//		$this->input->set('layout', 'modal');

		parent::display();
	}

	private function getToolbar() {
		// Get the toolbar object instance
		$bar = \Joomla\CMS\Toolbar\Toolbar::getInstance('toolbar');
		if (CKFof::userCan('create')) {
			\Joomla\CMS\Toolbar\ToolbarHelper::addNew('style.add', 'JTOOLBAR_NEW');
			\Joomla\CMS\Toolbar\ToolbarHelper::custom('style.copy', 'copy', 'copy', 'CK_COPY');
			// Render the popup button
//				$html = '<button class="btn btn-small btn-success" onclick="CKBox.open({handler:\'iframe\', fullscreen: true, url:\'' . \Joomla\CMS\Uri\Uri::root(true) . '/administrator/index.php?option=com_slideshowck&view=style&layout=modal&tmpl=component&id=0\'})">
//						<span class="icon-new icon-white"></span>
//						' . \Joomla\CMS\Language\Text::_('JTOOLBAR_NEW') . '
//						</button>';
//				$bar->appendButton('Custom', $html);
			
		}
		if (CKFof::userCan('edit')) {
			\Joomla\CMS\Toolbar\ToolbarHelper::custom('style.edit', 'edit', 'edit', 'CK_EDIT');
			\Joomla\CMS\Toolbar\ToolbarHelper::trash('style.delete');
		}

		return $bar;
	}
}
