<?php
/**
 * @package    Com_Comment
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       07.10.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class CCommentHelperBasic
 *
 * @since  5.1
 */
class CCommentHelperBasic
{
	/**
	 * Generates the copyright footer
	 *
	 * @return  array
	 */
	public static function getFooterText()
	{
		return '<p class="copyright" style="text-align: center; margin-top: 15px;">' . JText::sprintf('COM_COMMENT_POWERED_BY',
				' <a href="https://compojoom.com" title="Joomla extensions, modules and plugins">compojoom.com</a></p>');
	}
}
