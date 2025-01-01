<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\ObjectManager;

class OptionFactory
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
     * @param   array   $data
     * @param   string  $className
     *
     * @return OptionInterface
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function create($data = [], $className = 'Bluecoder\\Component\\Jfilters\\Administrator\\Model\\Filter\\OptionInterface') : OptionInterface
    {
        $option =$this->objectManager->createObject($className, $data);
        if (!$option instanceof OptionInterface) {
            throw new \InvalidArgumentException(
                $className .
                ' does not implement \Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option'
            );
        }
        return $option;
    }
}
