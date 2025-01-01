<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\Registry\Registry as JRegistry;

class Registry extends JRegistry
{
    /**
     * @var PropertyHandler
     */
    protected $propertyHandler;

    /**
     * Registry constructor.
     *
     * @param   null  $data
     *
     * @throws \ReflectionException
     */
    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->propertyHandler = ObjectManager::getInstance()->getObject(PropertyHandler::class);
    }

    /**
     * Get a value after applying the rules.
     *
     * @param   string  $path
     * @param   null    $default
     *
     * @return mixed|\stdClass|void
     * @since 1.0.0
     */
    public function get($path, $default = null)
    {
        $value = parent::get($path, $default);
        $value = $this->propertyHandler->get($path, $value);
        return $value;
    }
}