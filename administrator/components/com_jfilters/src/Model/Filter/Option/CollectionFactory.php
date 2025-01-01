<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\ObjectManager;

/**
 * Class CollectionFactory
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option
 */
class CollectionFactory
{
    /**
     * @var ObjectManager
     * @since 1.0.0
     */
    protected $objectManager;

    /**
     * CollectionFactory constructor.
     */
    public function __construct()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * Create a Collection for the Options
     *
     * @param string $className
     * @return Collection
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function create($className = 'Bluecoder\\Component\\Jfilters\\Administrator\\Model\\Filter\\Option\\Collection', $params = [])
    {
        $collection = $this->objectManager->createObject($className, $params);
        if (!$collection instanceof Collection) {
            throw new \InvalidArgumentException(
                $className .
                ' does not implement \Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection'
            );
        }

        return $collection;
    }
}
