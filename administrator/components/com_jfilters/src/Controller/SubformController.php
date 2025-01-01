<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Controller;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\SubFormField\Indexer;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

class SubformController extends BaseController
{
    /**
     * @var Indexer
     */
    protected $indexer;

    /**
     * @var null|LoggerInterface
     */
    protected ?LoggerInterface $JFLogger = NULL;

    /**
     * Start the indexer and set some basic data (e.g. total records).
     *
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function startIndexer()
    {
        // Check for a valid token. If invalid, send a 403 with the error message.
        if (!Session::checkToken('request')) {
            $this->sendResponse(new \Exception(Text::_('JINVALID_TOKEN_NOTICE'), 403));

            return;
        }
        // Clear the state that may has previous session data.
        $this->getIndexer()->clearState();
        $state = $this->getIndexer()->getState();
        $state->start = 1;
        $this->sendResponse($state);
    }

    /**
     * Batch index carries out the indexing process.
     *
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function batchIndex()
    {
        // Check for a valid token. If invalid, send a 403 with the error message.
        if (!Session::checkToken('request')) {
            $this->sendResponse(new \Exception(Text::_('JINVALID_TOKEN_NOTICE'), 403));

            return;
        }

        $state = $this->getIndexer()->getState();
        if (!$state->complete) {
            $state->start = 0;
            $this->getIndexer()->setState($state);
            $this->getIndexer()->batchIndex();
        }
        $state = $this->getIndexer()->getState();
        if($state->complete) {
            $this->getIndexer()->clearState();
        }
        $this->sendResponse($state);
    }

    /**
     * Send the response
     *
     * @param   null  $data
     *
     * @throws \Exception
     * @since 1.0.0
     */
    public function sendResponse($data = null)
    {
        $app = Factory::getApplication();
        // Clear previously set header/msg
        $data->header = '';
        $data->msg = '';

        // Send the assigned error code if we are catching an exception.
        if ($data instanceof \Exception) {
            try {
                $this->getLogger()->error($data->getMessage(), 'com_jfilters-subformfield-index');
            } catch (\RuntimeException $exception) {
                // Informational log only
            }
            $data->header = Text::_('COM_JFILTERS_INDEXER_ERROR');
            $data->msg = $data->getMessage();
            $app->setHeader('status', $data->getCode());
        }
        // Add some info log
        else {
            if ($data->total > 0 && $data->batchOffset == 0) {
                $loggerMessage = 'Subform Field Values Indexing Started';
            } else {
                $loggerMessage = sprintf('%d of %d Subform Field Values Indexed', $data->batchOffset, $data->total);
            }

            $this->getLogger()->info($loggerMessage);
        }

        $data->endTime = Factory::getDate()->toSql();
        $data->start = !empty($data->start) ? (int)$data->start : 0;
        $data->complete = !empty($data->complete) ? (int)$data->complete : 0;

        if ($data->complete) {
            $data->header = $data->header ?: Text::_('COM_JFILTERS_INDEXER_COMPLETE');
            $data->msg = $data->msg ?: Text::_('COM_JFILTERS_INDEXER_COMPLETE');
        }

        $data->header = $data->header ?: Text::_('COM_JFILTERS_INDEXER_RUNNING');
        $data->msg = $data->msg ?: Text::_('COM_JFILTERS_INDEXER_RUNNING');

        // Send the JSON response.
        echo json_encode($data);
    }

    /**
     * Get the logger.
     *
     * @return LoggerInterface
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function getLogger()
    {
        if ($this->JFLogger === null) {
            $this->JFLogger = ObjectManager::getInstance()->getObject(LoggerInterface::class);
        }

        return $this->JFLogger;
    }

    /**
     * Get the indexer.
     *
     * @return Indexer
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function getIndexer()
    {
        if ($this->indexer === null) {
            $this->indexer = ObjectManager::getInstance()->getObject(Indexer::class);
        }

        return $this->indexer;
    }
}