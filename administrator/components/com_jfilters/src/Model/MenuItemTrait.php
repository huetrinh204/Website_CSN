<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model;

\defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\Menu\SiteMenu;

/**
 * Used to fetch the most relevant JFilters menu item
 * @since 1.16.0
 */
trait MenuItemTrait
{
    /**
     * @var MenuItem|null
     * @since 1.16.0
     */
    protected ?MenuItem $jfMenuItem = null;

    /**
     * Get the menu item we are going to use.
     *
     * @return ?MenuItem
     * @throws \Exception
     * @since 1.16.0
     */
    public function getMenuItem() : ?MenuItem
    {
        if ($this->jfMenuItem === null) {
            // Get the itemId fallback
            $itemId = Factory::getApplication()->getInput()->getInt('Itemid', 0);
            try {
                /** @var SiteMenu $menu */
                $menu = Factory::getApplication()->getMenu();
                if (!$menu || !$menu->getItem($itemId)) {
                    $this->jfMenuItem = null;
                } else {
                    $menuItem = $menu->getItem($itemId);
                    // Check if the actual menu item is of jfilters results. Otherwise use the fallback.
                    $this->jfMenuItem = $menuItem->component == 'com_jfilters' && $menuItem->query['view'] == 'results' ? $menuItem : null;
                }
            } catch (\Exception $exception) {
                // suck it. No menu.
            }
        }

        return $this->jfMenuItem;
    }
}