<?php
/**
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       30.09.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\String\StringHelper;

jimport('joomla.application.component.controllerlegacy');

/**
 * Class CcommentControllerComment
 *
 * @since  5.0
 */
class CcommentControllerComment extends JControllerLegacy
{
    protected $errorToken = array('status' => 'error', 'message' => 'invalid token');

    /**
     * Publish/unpublishes a comment
     *
     * @return void
     */
    public function changestate()
    {
        if (!JSession::checkToken('get')) {
            ccommentHelperUtils::sendJsonResponse($this->errorToken);
            jexit();
        }

        $model = $this->getModel('comment', 'ccommentModel');
        $input = JFactory::getApplication()->input;
        $comment = $model->getComment($input->getInt('id'));
        $input->set('component', $comment->component);
        $json['status'] = 'error';

        if (ccommentHelperSecurity::isModerator($comment->contentid)) {
            try {
                $model->changeState($input->getInt('state', 0), $input->getInt('id', 0));
                $json['status'] = 'success';
            } catch (Exception $e) {
                $json['status'] = 'error';
            }
        } else {
            $json['message'] = 'You are not allowed to edit this resource';
        }

        ccommentHelperUtils::sendJsonResponse($json);
        jexit();
    }

    /**
     * Vote up/down a comment
     *
     * @return void
     */
    public function vote()
    {
        if (!JSession::checkToken('get')) {
            ccommentHelperUtils::sendJsonResponse($this->errorToken);
            jexit();
        }

        $model = $this->getModel('comment', 'ccommentModel');
        $input = JFactory::getApplication()->input;

        try {
            $success = $model->vote($input->getInt('vote', 0), $input->getInt('id', 0));
            $comment = $model->getComment($input->getInt('id', 0));
            $json = array(
                'votes' => (int)($comment->voting_yes - $comment->voting_no),
                'id' => $comment->id
            );
        } catch (Exception $e) {
            $json = array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }

        echo json_encode($json);
        jexit();
    }

    /**
     * Gets a comment for quoting
     *
     * @return void
     */
    public function quote()
    {
        $model = $this->getModel('comment', 'ccommentModel');

        $id = JFactory::getApplication()->input->getInt('id');
        $comment = $model->getComment($id);
        $config = ccommentConfig::getConfig($comment->component);

        // Use the entered name in the comment
        $name = $comment->name;

        // If the username is logged in, then use either real or user name
        if ($comment->userid) {
            $name = $config->get('layout.use_name', 0) ? $comment->user_realname : $comment->user_username;
        }

        $json = array(
            'id' => $comment->id,
            'name' => $name,
            'title' => $comment->title,
            'comment' => $comment->comment
        );

        echo json_encode($json);
        jexit();
    }

    /**
     * Saves the comment to the db
     *
     * @return void
     */
    public function insert()
    {
        if (!JSession::checkToken('get')) {
            ccommentHelperUtils::sendJsonResponse($this->errorToken);
            jexit();
        }

        $input = JFactory::getApplication()->input;
        $data = $input->get('jform', '', 'array');
        $input->set('component', $data['component']);
        $component = $data['component'];
        $itemId = $data['contentid'];
        $config = ccommentConfig::getConfig($component);
        $pageNum = $data['page'] ? $data['page'] : 0;
        $user = JFactory::getUser();

        $form = new JForm('ccommentComment');

        $ip = ccommentHelperSecurity::getRealIpAddr();

        if (ccommentHelperSecurity::isBlocked($ip)) {
            ccommentHelperUtils::sendJsonResponse(
                array(
                    'status' => 'error',
                    'message' => JText::_('COM_COMMENT_YOUR_IP_IS_BLOCKED')
                )
            );
            jexit();
        }

        if (!ccommentHelperSecurity::canPost($config)) {
            ccommentHelperUtils::sendJsonResponse(
                array(
                    'status' => 'error',
                    'message' => JText::_('COM_COMMENT_NOT_AUTHORISED_TO_POST')
                )
            );
            jexit();
        }

        if ($config->get('security.captcha') && ccommentHelperSecurity::groupHasAccess($user->getAuthorisedGroups(), $config->get('security.captcha_usertypes'))) {
            $result = ccommentHelperCaptcha::captchaResult(
                $config->get('security.captcha_type'),
                $config->get('security.recaptcha_private_key')
            );

            if (!$result) {
                ccommentHelperUtils::sendJsonResponse(
                    array(
                        'status' => 'error',
                        'message' => JText::_('COM_COMMENT_INVALID_CAPTCHA_TRY_AGAIN')
                    )
                );
                jexit();
            }
        }

        if (trim($data['comment']) === '') {
            ccommentHelperUtils::sendJsonResponse(
                array(
                    'status' => 'error',
                    'message' => JText::_('COM_COMMENT_EMPTY_COMMENTS_NOT_ALLOWED')
                )
            );
            jexit();
        }

        // Set username, email only when not modifying a comment
        if (!isset($data['id'])) {
            if (!$user->get('guest')) {
                $data['userid'] = $user->id;
                $data['name'] = $config->get('layout.use_name', 0) ? $user->get('name') : $user->get('username');
                $data['email'] = $user->email;
            } else {
                $data['userid'] = 0;
                $data['name'] = $data['name'] ? $data['name'] : JText::_('COM_COMMENT_ANONYMOUS');
            }
        } else {
            // Unset the name that comes here - we are obviously editing a comment
            unset($data['name']);

            $model = $this->getModel('comment', 'ccommentModel');
            $comment = $model->getComment($data['id']);

            // If the notify option is set, we have to find an email for this user
            // If we don't have an email for the user who left the comment, then we set notify to 0
            if (isset($data['notify'])) {
                if ($comment->email) {
                    $data['email'] = $comment->email;
                } elseif ($comment->userid) {
                    $data['email'] = JFactory::getUser($comment->userid)->email;
                } else {
                    $data['notify'] = 0;
                }
            }
        }

        if (!isset($data['email'])) {
            $data['email'] = null;
        }

        // If notify is set, then the user has to provide something that resembles a valid e-mail
        if (isset($data['notify'])) {
            if (!JMailHelper::isEmailAddress($data['email'])) {
                ccommentHelperUtils::sendJsonResponse(
                    array(
                        'status' => 'error',
                        'message' => JText::_('COM_COMMENT_PLEASE_PROVIDE_A_VALID_NOTIFICATION_EMAIL')
                    )
                );
                jexit();
            }
        } else {
            // If notify is not set, then the checkbox in the form was not send. If we are dealing with editing
            // comments then we won't unsubscribe the user, that's why we explicitly set it to 0 here
            $data['notify'] = 0;
        }

        $published = $this->isPublished($ip, $data['email'], $data['comment'], $itemId);

        // TODO: move this to a plugin
        if ($config->get('integrations.akismet_use', 0)) {
            JLoader::register('Akismet', JPATH_SITE . '/components/com_comment/classes/akismet/Akismet.class.php');
            $apiKey = $config->get('integrations.akismet_key');
            $siteUrl = StringHelper::substr_replace(JURI::base(), '', -1);
            $akismet = new Akismet($siteUrl, $apiKey);
            $akismet->setCommentAuthor($data['name']);
            $akismet->setCommentAuthorEmail($data['email']);
            $akismet->setCommentContent($data['comment']);
            $akismet->setPermalink(JURI::current());

            if ($akismet->isCommentSpam()) {
                // Store the comment but mark it as spam (in case of a mis-diagnosis)
                $published = 0;
            }
        }

        $data['published'] = $published;
        $data['ip'] = $ip;

        $form->loadFile(JPATH_SITE . '/administrator/components/com_comment/models/forms/comment.xml');
        $customFieldsModel = $this->getModel('Customfields', 'CcommentModel');
        $form->load(
            CompojoomFormCustom::generateFormXML(
                $customFieldsModel->getFields('com_comment', $config->id)
            )
        );
        $data = $form->filter($data);
        $model = $this->getModel('comment', 'ccommentModel');

        // If we are editing we need to do some checks
        if (isset($data['id'])) {
            $moderator = ccommentHelperSecurity::isModerator($itemId);

            if (!$moderator) {
                $comment = $model->getComment($data['id']);
                $moderator = ccommentHelperSecurity::isCommentModerator($comment->userid) && ccommentHelperSecurity::restrictModerationByTime($comment->date);
            }

            if (!$moderator) {
                ccommentHelperUtils::sendJsonResponse(
                    array('status' => 'error',
                        'message' => JText::_('COM_COMMENT_YOU_DONT_HAVE_NECESSARY_RIGHTS'))
                );
                jexit();
            }
        }

        $commentId = $model->insert($data);

        if ($commentId) {
            $comment = $model->getComment($commentId);

            $this->saveMultimedia($comment->id);

            if ($published) {
                if ($config->get('layout.comments_per_page', 0)) {
                    if ($comment->parentid != -1) {
                        $comments = ccommentHelperComment::prepareComments(array($comment), $config);
                        ccommentHelperUtils::sendJsonResponse($comments[0]);
                    } else {
                        $pagination = new ccommentHelperPagination($commentId, $itemId, $component);
                        $page = $pagination->findPage();

                        // Are we on a different page? Then load the whole page.
                        if ($pageNum != $page) {
                            $countParents = $model->countComments($itemId, $component, true);
                            $total = $model->countComments($itemId, $component);
                            $comments = ccommentHelperComment::prepareComments($model->getComments($itemId, $component, $page), $config);
                            ccommentHelperUtils::sendJsonResponse(
                                array(
                                    'info' =>
                                        array(
                                            'page' => $page,
                                            'countParents' => $countParents,
                                            'total' => $total,
                                            'insertedId' => $comment->id
                                        ),
                                    'models' => $comments
                                )
                            );

                        } else {
                            $comments = ccommentHelperComment::prepareComments(array($comment), $config);
                            ccommentHelperUtils::sendJsonResponse($comments[0]);
                        }
                    }
                } else {
                    $comments = ccommentHelperComment::prepareComments(array($comment), $config);
                    ccommentHelperUtils::sendJsonResponse($comments[0]);
                }

                // Add notification to queue
                $notify = new ccommentHelperNotify($comment->id);
                $notify->notify($comment->published ? 'publish' : 'unpublish', 1);
            } else {
                ccommentHelperUtils::sendJsonResponse(
                    array('status' => 'info', 'message' => JText::_('COM_COMMENT_ADDED_BUT_NEEDS_MODERATION'))
                );

                // Add notification to queue
                $notify = new ccommentHelperNotify($comment->id);
                $notify->notifyModerators();
            }

            jexit();
        }

        ccommentHelperUtils::sendJsonResponse(
            array('status' => 'error', 'message' => JText::_('COM_COMMENT_SOMETHING_WENT_WRONG'))
        );

        jexit();
    }

    /**
     * Determines whether we should automatically publish the comment
     *
     * @param $ip
     * @param $email
     * @param $comment
     * @param $contentId
     *
     * @return bool
     */
    private function isPublished($ip, $email, $comment, $contentId)
    {
        JPluginHelper::importPlugin('compojoomcomment');
        $appl = JFactory::getApplication();
        $config = ccommentConfig::getConfig(JFactory::getApplication()->input->getCmd('component'));
        $result = $appl->triggerEvent('onCommentAutoPublishing', array('comment' => $comment, 'ip' => $ip, 'email' => $email));
        $custom_filters = true;

        foreach ($result as $row) {
            $custom_filters = $custom_filters && $row;
        }

        return (ccommentHelperSecurity::autopublish($config) && $custom_filters) || ccommentHelperSecurity::isModerator($contentId);
    }

    /**
     * Edit comment
     *
     * @return void
     */
    public function edit()
    {
        if (!JSession::checkToken('get')) {
            ccommentHelperUtils::sendJsonResponse($this->errorToken);
            jexit();
        }

        $model = $this->getModel('comment', 'ccommentModel');
        $id = JFactory::getApplication()->input->getInt('id');
        $comment = $model->getComment($id);
        $component = JFactory::getApplication()->input->getCmd('component');
        $config = ccommentConfig::getConfig($component);
        $customFieldsConfigArray = CcommentHelperComment::getCustomFieldsArray($config);

        $moderator = ccommentHelperSecurity::isModerator($comment->contentid);

        if (!$moderator) {
            $moderator = ccommentHelperSecurity::isCommentModerator($comment->userid) && ccommentHelperSecurity::restrictModerationByTime($comment->date);
        }

        if ($moderator) {
            $json = array(
                'id' => $comment->id,
                'name' => $comment->name,
                'title' => $comment->title,
                'comment' => $comment->comment,
                'notify' => $comment->notify,
                'customfields' => CcommentHelperComment::prepareCustomFields($customFieldsConfigArray, $comment->customfields, true)
            );

            ccommentHelperUtils::sendJsonResponse($json);
            jexit();
        }

        ccommentHelperUtils::sendJsonResponse(array('status' => 'error', 'message' => 'You are not authorised to execute this action'));
        jexit();
    }

    /**
     * Save images if necessary
     *
     * @param JModelLegacy $model The data model object.
     * @param array $validData The validated data.
     *
     * @return void
     */
    protected function saveMultimedia($id, $validData = array())
    {
        $this->addModelPath(JPATH_ADMINISTRATOR . '/components/com_comment/models');
        $appl = JFactory::getApplication();
        $input = $appl->input;
        $formData = new JInput($input->get('jform', '', 'array'));
        $multimediaModel = JModelLegacy::getInstance('Multimedia', 'CcommentModel');
        $files = $formData->get('picture', array(), 'array');
        $meta = $formData->get('picture_data', array(), 'array');

        if (count($files)) {
            // Import the appropriate plugin group.
            JPluginHelper::importPlugin('compojoomcomment');

            // Do whatever you want with the images before they are saved on the server
            $appl->triggerEvent('onMultimediaBeforeUpload', array($id, $files, $meta));

            // Save the images
            $multimediaModel->uploadPermanent($id, $files, $meta);

            // Manipulate the images after they are uploaded
            $appl->triggerEvent('onMultimediaAfterUpload', array($id, $files, $meta));
        }
    }
}
