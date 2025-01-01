<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\ObjectManager;

class FieldFactory
{
    /**
     * Create the object and pass it data
     *
     * @param   array   $data
     * @param   string  $instanceName
     *
     * @return object
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function create($data= [], $instanceName = 'Bluecoder\\Component\\Jfilters\\Administrator\\Model\\Config\\Field')
    {
        $field = ObjectManager::getInstance()->createObject($instanceName);
        if (!$field instanceof Field) {
            throw new \InvalidArgumentException(
                $instanceName .
                ' does not implement \Bluecoder\Component\Jfilters\Administrator\Model\Config\Field'
            );
        }
        $field->setData($data);
        return $field;
    }
}
