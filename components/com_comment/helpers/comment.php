<?php
/**
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       28.02.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class CcommentHelperComment
 *
 * @since  5.0
 */
class CcommentHelperComment
{
	/**
	 * Manipulates the comments for output
	 *
	 * @param   array      $comments  - array with comments
	 * @param   JRegistry  $config    - the plugin configuration
	 *
	 * @return mixed
	 */
	public static function prepareComments($comments, $config)
	{
		JPluginHelper::importPlugin('compojoomcomment');
        $appl = JFactory::getApplication();

		$user = JFactory::getUser();
		$avatars = array();
		$json = array();
		$supportAvatars = $config->get('integrations.support_avatars', 0);
		$supportProfiles = $config->get('integrations.support_profiles', 0);
		$userIds = ccommentHelperUsers::getUserIds($comments);

		// This stinks...
		// TODO: find a better way to do this
		$contentId = count($comments) ? $comments[0]->contentid : 0;

		$userGroups = ccommentHelperUsers::getUserGroups($userIds);

		$customFields = self::getCustomFieldsArray($config);

		// Get the comment ids
		$commentIds = array_map(
			function($o) {
				return $o->id;
			},
			$comments
		);
		$galleria = CompojoomGalleria::getData($commentIds, 'com_comment.comment', false);

		if ($config->get('integrations.support_avatars', 0))
		{
			$avatars = ccommentHelperAvatars::buildUserAvatars($userIds, $config->get('integrations.support_avatars', 0));
		}

		// There is no point of making a tree representation when we have just 1 comment
		if ($config->get('layout.tree', 0) && count($comments) > 1)
		{
			$comments = ccommentHelperTree::build($comments);
		}

		$bbcode = new ccommentHelperBBcode($config);

		$moderator = ccommentHelperSecurity::isModerator($contentId) &&	ccommentHelperSecurity::canPost($config);

		foreach ((array) $comments as $key => $comment)
		{
			$isCommentModerator = false;
			$avatar = '';
			$class = array();
			$json[$key] = new stdClass;
			$json[$key]->id = (int) $comment->id;
			$json[$key]->parentid = (int) $comment->parentid;
			$json[$key]->children = isset($comment->children) ? $comment->children : array();
			$json[$key]->level = isset($comment->wrapnum) ? (int) $comment->wrapnum : 0;

			if ($comment->userid)
			{
				if ($config->get('layout.use_name', 0))
				{
					$json[$key]->name = $comment->user_realname;
				}
				else
				{
					$json[$key]->name = $comment->user_username;
				}
			}
			else
			{
				$json[$key]->name = ccommentHelperUtils::censorText($comment->name, $config);
			}

			// Censor text if necessary, convert to html output and check for words that are too long
			$json[$key]->comment = $bbcode->parse(ccommentHelperUtils::censorText($comment->comment, $config));

			if (!$moderator)
			{
				$isCommentModerator = (ccommentHelperSecurity::isCommentModerator($comment->userid) &&
					ccommentHelperSecurity::restrictModerationByTime($comment->date));
			}

			$json[$key]->commentModerator = $moderator || $isCommentModerator;

			$comment->userGroups = '';

			if ($comment->userid)
			{
				if (isset($userGroups[$comment->userid]))
				{
					foreach ($userGroups[$comment->userid] as $group)
					{
						$groupName = strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $group['title'])));
						$class[] = 'ccomment-' . $groupName;
					}

					$comment->userGroups = $userGroups[$comment->userid];
				}

				if ($supportAvatars)
				{
					$avatar = isset($avatars[$comment->userid]) ? $avatars[$comment->userid] : '';
				}
			}

			if ($comment->customfields)
			{
				$json[$key]->customfields = self::prepareCustomFields($customFields, $comment->customfields);
			}

			// If we don't have an avatar and if gravatar is enabled, let us look for an image!
			if ($avatar == '' && $config->get('integrations.gravatar', 0))
			{
				$avatar = ccommentHelperAvatars::getUserGravatar($comment->email);
			}

			// Still no avatar? Get the noAvatar image
			if ($avatar == '' && $supportAvatars)
			{
				$avatar = ccommentHelperAvatars::noAvatar();
			}

			$json[$key]->avatar = $avatar;

			$json[$key]->date = self::getLocalDate($comment->date, $config->get('layout.date_format', 'age'));
			$json[$key]->votes = (int) ($comment->voting_yes - $comment->voting_no);

			if ($supportProfiles)
			{
				$json[$key]->profileLink = ccommentHelperProfiles::profileLink($comment->userid, $supportProfiles);
			}

			$json[$key]->published = (int) $comment->published;

			if ($comment->parentid != -1)
			{
				$class[] = 'ccomment-parent-is-' . $comment->parentid;
			}

			$class[] = ($key % 2) ? 'ccomment-even' : 'ccomment-odd';
			$class[] = 'ccomment-comment';
			$class[] = (ccommentHelperSecurity::groupHasAccess($comment->userGroups, $config->get('security.moderators'))) ? 'ccomment-moderator' : '';

			if (ccommentHelperSecurity::ownComment($comment->userid))
			{
				$class[] = 'ccomment-own';
			}

			$json[$key]->class = implode(' ', $class);

			// Load the galleria information
			if (isset($galleria[$comment->id]))
			{
				$json[$key]->galleria = $galleria[$comment->id];
			}
		}

		$appl->triggerEvent('onAfterPrepareComments', array(&$json, $config, $comments));

		return $json;
	}

	/**
	 * Get the customFieldsConfig array depending on the comment config
	 *
	 * @param   object  $config  - the comment config
	 *
	 * @return array
	 */
	public static function getCustomFieldsArray($config)
	{
		$customFieldsModel = JModelLegacy::getInstance('Customfields', 'CcommentModel');
		$dbCustomFields = $customFieldsModel->getFields('com_comment', $config->id);
		$customFields = array();

		// Make a new array that has the slug for key
		foreach ($dbCustomFields as $field)
		{
			$customFields[$field->slug] = $field;
		}

		return $customFields;
	}

	/**
	 * Prepare the custom fields for each comment for output
	 *
	 * @param   array  $customFieldsArray  - the custom fields data for the current fields
	 * @param   array  $customFieldsDb     - the custom fields config from the db
	 * @param   bool   $edit               - is this for edit or for display?
	 *
	 * @return array
	 */
	public static function prepareCustomFields($customFieldsArray, $customFieldsDb, $edit = false)
	{
		$customFieldRegistry  = new JRegistry($customFieldsDb);
		$customfieldsDecoded = $customFieldRegistry->toArray();
		$customFieldsToOutput = array();

		foreach ($customFieldsArray as $customFieldsKey => $customFieldsData)
		{
			if (isset($customfieldsDecoded[$customFieldsKey]))
			{
				if (!$edit)
				{
					if ($customfieldsDecoded[$customFieldsKey])
					{
						$customFieldsToOutput[] = array(
							'title' => JText::_($customFieldsArray[$customFieldsKey]->title),
							'value' => CompojoomFormCustom::render($customFieldsArray[$customFieldsKey], $customfieldsDecoded[$customFieldsKey]),
							'name' => $customFieldsKey
						);
					}
				}
				else
				{
					$customFieldsToOutput[] = array(
						'value' => $customfieldsDecoded[$customFieldsKey],
						'name' => $customFieldsKey
					);
				}
			}
		}

		return $customFieldsToOutput;
	}

	/**
	 * Prepares the comments for preview view
	 *
	 * @param   object  $plugin    - the plugin object
	 * @param   array   $comments  - array with comments
	 *
	 * @return mixed
	 */
	public static function prepareCommentForPreview($plugin, $comments)
	{
		foreach ($comments as $key => $comment)
		{
			$comments[$key]->link = ccommentHelperUtils::fixUrl($plugin->getLink($comment->contentid, $comment->id));
		}

		return $comments;
	}

	/**
	 * Function to display the Date in the right format with Offset
	 *
	 * @param   string  $strDate  - string date
	 * @param   string  $format   - string format
	 *
	 * @return string
	 */
	public static function getLocalDate($strDate, $format = 'age')
	{
		if ($format == 'age')
		{
			$formatDate = JHtml::_('date.relative', $strDate);
		}
		else
		{
			$formatDate = JHtml::_('date', $strDate, $format, true);
		}

		return $formatDate;
	}

	/**
	 * Gets the ordering for the comments
	 *
	 * @param   object  $config  - the config object
	 *
	 * @return string - the ordering for the query
	 */
	public static function getOrdering($config)
	{
		$ordering = $config->get('layout.sort', 0);

		$orders = array(
			'id ASC',
			'id DESC',
			'voting_yes - voting_no ASC',
			'voting_yes - voting_no DESC',
		);

		return $orders[$ordering];
	}
}
