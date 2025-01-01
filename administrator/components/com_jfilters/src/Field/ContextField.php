<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Field;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ContextCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\ContextInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;

/**
 * Class ContextFieldField- used for displaying the existing contexts
 *
 */
class ContextField extends ListField
{
    /**
     * Method to get the field options.
     *
     * @return array
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function getOptions()
    {
        $objectMager = ObjectManager::getInstance();
        /** @var  ContextCollection $contextCollection */
        $contextCollection = $objectMager->getObject(ContextCollection::class);

        $contextItems = $contextCollection->getItems();
        $options = [];
        $options_buffer = [];
        /** @var ContextInterface $context */
        foreach ($contextItems as $context) {
            $contextName = $context->getName();
            if(isset($options_buffer[$contextName])) {
                continue;
            }
            $options[] = ['value' => $contextName, 'text' => Text::_($context->getAlias())];
            $options_buffer[$contextName] = $context;
        }
        return array_merge(parent::getOptions(), $options);
    }
}
