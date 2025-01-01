<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model;

\defined('_JEXEC') or die();

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Log\LogEntry;
use Joomla\CMS\Log\Logger as CmsLogger;
use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;

/**
 * Class Logger
 * @package Bluecoder\Component\Jfilters\Administrator\Model
 */
class Logger extends AbstractLogger implements LoggerInterface
{
    /**
     * The log category used for critical, alert, emergency
     * @since  1.0
     */
    const LOG_CATEGORY_CRITICAL = 'jfilters.critical';

    /**
     * The log category used for error, warning, notice
     * @since  1.0
     */
    const LOG_CATEGORY_ERROR = 'jfilters.error';

    /**
     * The log category used for info, debug
     * @since  1.0.0
     */
    const LOG_CATEGORY_INFO = 'jfilters.info';

    /**
     * @var CmsLogger
     * @since  1.0.0
     */
    protected $errorLogger;

    /**
     * @var CmsLogger
     * @since  1.0.0
     */
    protected $infoLogger;

    /**
     * Array of PSR-3 levels, mapping to the Joomla's logger levels
     *
     * @var    array
     * @since  1.0.0
     */
    protected array $logLevelsMap = array(
        LogLevel::EMERGENCY => Log::EMERGENCY,
        LogLevel::ALERT     => Log::ALERT,
        LogLevel::CRITICAL  => Log::CRITICAL,
        LogLevel::ERROR     => Log::ERROR,
        LogLevel::WARNING   => Log::WARNING,
        LogLevel::NOTICE    => Log::NOTICE,
        LogLevel::INFO      => Log::INFO,
        LogLevel::DEBUG     => Log::DEBUG
    );

    /**
     * Map each level to the corresponding category
     *
     * @var array
     * @since 1.0.0
     */
    protected array $levelCategoryMap = [
        LogLevel::EMERGENCY => self::LOG_CATEGORY_CRITICAL,
        LogLevel::ALERT => self::LOG_CATEGORY_CRITICAL,
        LogLevel::CRITICAL => self::LOG_CATEGORY_CRITICAL,

        LogLevel::ERROR => self::LOG_CATEGORY_ERROR,
        LogLevel::WARNING => self::LOG_CATEGORY_ERROR,
        LogLevel::NOTICE => self::LOG_CATEGORY_ERROR,

        LogLevel::INFO => self::LOG_CATEGORY_INFO,
        LogLevel::DEBUG => self::LOG_CATEGORY_INFO,
    ];

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     * @throws \Exception
     * @since 1.0.0
     */
    public function log($level, $message, array $context = array()): void
    {
        // Make sure the log level is valid
        if (!\array_key_exists($level, $this->logLevelsMap)) {
            throw new InvalidArgumentException('An invalid log level has been given.');
        }

        // output as string, arrays
        if(is_array($message)) {
            // if the array (e.g. trace array) is too long, can lead to memory exhaustion
            $message = array_slice($message, 0 ,10);
            $message = var_export($message, true);
        }

        $category = Factory::getApplication()->getName();
        $category .= is_array($context) && !empty($context['category']) ? '.' . $context['category'] : '';
        $loggEntry = new LogEntry($message, $this->logLevelsMap[$level], $category);
        $logger = $this->getLogger($level);
        $logger->addEntry($loggEntry);
    }

    /**
     * Returns the proper logger for each level (we use 3 loggers, splitting the levels to 3 groups)
     *
     * @param string $level
     * @return CmsLogger
     * @since 1.0.0
     */
    protected function getLogger(string $level): CmsLogger
    {
        if (!isset($this->levelCategoryMap[$level])) {
            throw new \InvalidArgumentException('No log category exists for the supplied log level:' . $level);
        }

        switch ($this->levelCategoryMap[$level]) {
            case self::LOG_CATEGORY_CRITICAL:
                $datetime = new Date();
                $datetime = $datetime->format('Y-m-d\TH-i-T');
                $options = [
                    'text_file' => self::LOG_CATEGORY_CRITICAL . '.' . $datetime . '.report.php',
                ];
                /**
                 * Maybe we should print the error in the screen when debug is enabled
                 * If we do that we can use use the libraries/src/Log/Logger/EchoLogger.php
                 */
                $logger = new CmsLogger\FormattedtextLogger($options);
                break;

            case self::LOG_CATEGORY_ERROR:
                if ($this->errorLogger === null) {
                    $options = [
                        'text_file' => self::LOG_CATEGORY_ERROR . '.log.php',
                    ];
                    $this->errorLogger = new CmsLogger\FormattedtextLogger($options);
                }
                $logger = $this->errorLogger;
                break;

            case self::LOG_CATEGORY_INFO:
                if ($this->infoLogger === null) {
                    $options = [
                        'text_file' => self::LOG_CATEGORY_INFO . '.log.php',
                    ];
                    $this->infoLogger = new CmsLogger\FormattedtextLogger($options);
                }
                $logger = $this->infoLogger;
                break;
        }
        if ($logger === null) {
            throw new \RuntimeException('The logger is not created. Check if there is a logger process for that log level.');
        }
        return $logger;
    }
}
