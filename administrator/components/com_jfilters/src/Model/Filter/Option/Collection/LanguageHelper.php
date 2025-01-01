<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\Database\DatabaseInterface;

class LanguageHelper
{
    /**
     * @var FilterInterface
     * @since 1.0.0
     */
    protected $filterConfig;

    /**
     * @var DatabaseInterface
     * @since 1.0.0
     */
    protected $database;

    /**
     * LanguageHelper constructor.
     *
     * @param   FilterInterface         $filterConfig
     * @param   DatabaseInterface|null  $database
     *
     * @throws \ReflectionException
     */
    public function __construct(FilterInterface $filterConfig, ?DatabaseInterface $database)
    {
        $this->filterConfig = $filterConfig;
        $this->database     = $database ?? ObjectManager::getInstance()->getObject(DatabaseInterface::class);
    }

    /**
     * Get the languages of the options.
     * This is useful only for the non-dynamic filters, where each option can have it's own language (e.g. categories).
     * The dynamic filters declare their language in the definition section for the entire filter (and it's options).
     *
     * @return null | array
     * @since 1.0.0
     */
    public function getLanguages()
    {
        $languages   = $this->filterConfig->getValue()->getLanguage() && $this->filterConfig->getValue()->getLanguage()->getValue() ? [$this->filterConfig->getValue()->getLanguage()->getValue()] : ['*'];
        $valuesTable = $this->filterConfig->getValue()->getDbTable();

        if ($this->filterConfig->getValue()->getLanguage() && $this->filterConfig->getValue()->getLanguage()->getDbColumn()) {
            $languageColumn = $this->filterConfig->getValue()->getLanguage()->getDbColumn();
            $query          = $this->database->getQuery(true);
            $query->select('DISTINCT ' . $this->database->quoteName($languageColumn))->from($this->database->quoteName($valuesTable));
            $this->database->setQuery($query);
            $languages = $this->database->loadColumn();
        }

        return $languages;
    }
}
