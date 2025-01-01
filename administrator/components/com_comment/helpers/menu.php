<?php
/**
 * @package    Com_Comment
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       07.10.2014
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class HotspotsHelperMenu
 *
 * @since  4.0
 */
class CcommentHelperMenu
{
	/**
	 * Generates the menu
	 *
	 * @return  array
	 */
	public static function getMenu()
	{
		$menu = array();

		$menu['dashboard'] = array(
			'link' => 'index.php?option=com_comment&view=dashboard',
			'title' => 'COM_COMMENT_DASHBOARD',
			'icon' => 'fa-dashboard',
			'anchor' => '',
			'children' => array(),
			'label' => '',
			'keywords' => 'dashboard home overview cpanel'
		);
		$menu['comments'] = array(
			'link' => 'index.php?option=com_comment&view=comments',
			'title' => 'COM_COMMENT_MANAGE_COMMENTS',
			'icon' => 'fa-comments',
			'anchor' => '',
			'children' => array(),
			'label' => '',
			'keywords' => 'lists comments'
		);
		$menu['queue'] = array(
			'link' => 'index.php?option=com_comment&view=emailqueues',
			'title' => 'COM_COMMENT_EMAIL_QUEUE',
			'icon' => 'fa fa-envelope',
			'anchor' => '',
			'children' => array(),
			'label' => '',
			'keywords' => 'lists emails'
		);

		$menu['settings'] = array(
			'link' => 'index.php?option=com_comment&view=settings',
			'title' => 'COM_COMMENT_SETTINGS',
			'icon' => 'fa-cogs',
			'anchor' => '',
			'children' => array(),
			'label' => '',
			'keywords' => 'categories'
		);

		$menu['cutomfields'] = array(
			'link' => 'index.php?option=com_comment&view=customfields',
			'title' => 'COM_COMMENT_CUSTOM_FIELDS',
			'icon' => 'fa-puzzle-piece',
			'anchor' => '',
			'children' => array(),
			'label' => '',
			'keywords' => 'customfields'
		);

		$menu['import'] = array(
			'link' => 'index.php?option=com_comment&view=import',
			'title' => 'COM_COMMENT_IMPORT',
			'icon' => 'fa-exchange',
			'anchor' => '',
			'children' => array(),
			'label' => '',
			'keywords' => 'import'
		);

		return $menu;
	}
}
