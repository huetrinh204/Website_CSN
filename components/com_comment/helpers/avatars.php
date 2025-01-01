<?php
/**
 * @package    Ccomment
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       22.02.13
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class ccommentHelperAvatars
 *
 * @package  CComment
 * @since    5
 */
class ccommentHelperAvatars
{
	/**
	 * Gets the user avatar from a component that we support
	 *
	 * @param   int     $userId  - the user id
	 * @param   string  $type    - the component that we are going to use to get the avatar
	 *
	 * @return string
	 */
	public static function getUserAvatar($userId, $type)
	{
		$avatar = '';
		$avatars = self::buildUserAvatars(array($userId), $type);

		if (isset($avatars[$userId]))
		{
			$avatar = $avatars[$userId];
		}

		return $avatar;
	}

	/**
	 * gets the noAvatar image
	 *
	 * @return string
	 */
	public static function noAvatar()
	{
		$appl = JFactory::getApplication();
		$component = $appl->input->getCmd('component');
		$config = ccommentConfig::getConfig($component);

		$template = $config->get('template.template');
		$jTemplate = $appl->getTemplate();
		$templateMedia = JPATH_BASE . '/media/com_comment/templates/' . $template . '/images/nophoto.png';
		$templateMediaOverride = JPATH_BASE . '/templates/' . $jTemplate . '/html/com_comment/templates/' . $template . '/images/nophoto.png';

		if (is_file($templateMediaOverride))
		{
			$noAvatar = JUri::base() . 'templates/' . $jTemplate . '/html/com_comment/templates/' . $template . '/images/nophoto.png';
		}
		elseif (is_file($templateMedia))
		{
			$noAvatar = JUri::base() . 'media/com_comment/templates/' . $template . '/images/nophoto.png';
		}
		else
		{
			$noAvatar = JURI::base() . 'media/com_comment/images/noavatar.png';
		}

		return $noAvatar;
	}

	/**
	 * Gets the gravatar image
	 *
	 * @param   string  $email  - the user's email
	 *
	 * @return string - url to the gravatar image
	 */
	public static function getUserGravatar($email)
	{
		$default = self::noAvatar();
		$url = Juri::getInstance($default);

		if ($url->toString(array('scheme')) === 'https://')
		{
			/**
			 * For some reason it is no longer possible to load default gravatar image
			 * over SSL. That is why we'll fall back to a standard gravatar image here
			 */
			$default = 'mm';
		}

		$size = 64;

		// Prepare the gravatar image
		$path = "https://secure.gravatar.com/avatar/" . md5(strtolower($email)) .
			"?default=" . urlencode($default) . "&s=" . $size;

		return $path;
	}


	/**
	 * Builds an array with all users Ids and calls the appropriate function
	 *
	 * @param   array   $userIds  - the user id
	 * @param   string  $type     - the component
	 *
	 * @return array
	 */
	public static function buildUserAvatars($userIds, $type)
	{
		$avatars = array();

		if ($type)
		{
			$avatarSystem = CompojoomAvatars::getInstance($type);
			$avatars = $avatarSystem->getAvatars($userIds);
		}

		return $avatars;
	}
}
