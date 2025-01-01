<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Collection;

\defined('_JEXEC') or die();

class ConditionGroup
{
    /**
     * @var string
     */
    protected $id;

    /**
     * The AND conditions to the collection
     *
     * @var array
     * @since 1.0.0
     */
    protected $conditionsAnd = [];

    /**
     * The OR conditions to the collection
     *
     * @var array
     * @since 1.0.0
     */
    protected $conditionsOR = [];

    /**
     * @var string
     */
    protected $glue = 'AND';

    /**
     * ConditionGroup constructor.
     *
     * @param           $id
     * @param   string  $glue
     */
    public function __construct(string $id, $glue = 'AND')
    {
        $this->id = $id;
        $this->glue = $glue;
    }

    /**
     * Return the glue
     *
     * @return string
     * @since 1.0.0
     */
    public function getGlue()
    {
        return $this->glue;
    }

    /**
     * Set a condition
     *
     * @param   string  $id
     * @param   string  $condition
     * @param   string  $type
     *
     * @return $this
     * @since 1.0.0
     */
    public function setCondition(string $id, string $condition, string $type = 'AND')
    {
        $type = strtoupper(trim($type));
        if ($type == 'OR') {
            $this->conditionsOR[$id] = $condition;
        } else {
            $this->conditionsAnd[$id] = $condition;
        }

        return $this;
    }

    /**
     * Get condition
     *
     * @param   string  $id
     * @param   string  $type
     *
     * @return string|null
     * @since 1.0.0
     */
    public function getCondition(string $id, $type = 'AND')
    {
        $type = strtoupper(trim($type));
        $return = null;
        if ($type == 'OR' && isset($this->conditionsOR[$id])) {
            $return = $this->conditionsOR[$id];
        } elseif (isset($this->conditionsAnd[$id])) {
            $return = $this->conditionsAnd[$id];
        }

        return $return;
    }

    /**
     * Get the conditions by type
     *
     * @param   string  $type
     *
     * @return array
     * @since 1.0.0
     */
    public function getConditions($type = 'AND')
    {
        $type = strtoupper(trim($type));
        if ($type == 'OR') {
            $return = $this->conditionsOR;
        } else {
            $return = $this->conditionsAnd;
        }

        return $return;
    }

    /**
     * Clear all the conditions
     *
     * @return $this
     * @since 1.0.0
     */
    public function clearConditions ()
    {
        $this->conditionsOR = [];
        $this->conditionsAnd = [];
        return $this;
    }
}