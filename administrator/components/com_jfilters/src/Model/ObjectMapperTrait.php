<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model;

\defined('_JEXEC') or die();

/**
 * Trait ObjectMapperTrait *
 * It is loaded within collections to map their items
 * We use a Trait here instead of adding this function to our AbstractCollection,
 * because we use this Trait in the Joomla Models as well.
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Model
 */
trait ObjectMapperTrait
{
    /**
     * Map the items to specific type object
     *
     * @param array $rawItems
     * @return $this
     * @since 1.0.0
     */
    public function setMappedItems(array $rawItems)
    {
        $items = $rawItems;
        $this->items = [];
        foreach ($items as $item) {
            //each item can carry it's own type as this is extended from the collection's itemObjectClass. If not use the itemObjectClass for all the items.
            $itemObjectClass = isset($item->objectClass) && is_a($item->objectClass, $this->itemObjectClass, true)? $item->objectClass : $this->itemObjectClass;

            //mapping is already done
            if ($item instanceof $itemObjectClass) {
                $this->items[] = $item;
                continue;
            }
            $newItem = $this->objectManager->createObject($itemObjectClass);
            $newItem->setData($item);
            $this->items[] = $newItem;
        }
        return $this;
    }
}
