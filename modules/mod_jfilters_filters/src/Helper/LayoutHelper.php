<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Module\JfiltersFilters\Site\Helper;

defined('_JEXEC') or die;

use Bluecoder\Component\Jfilters\Administrator\Model\MenuItemTrait;
use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class LayoutHelper
{
    use MenuItemTrait;

    protected array $loadedLayouts = [];

    /**
     * @var LayoutHelper|null
     * @since 1.16.0
     */
    protected static ?LayoutHelper $instance = null;

    /**
     *
     * @return LayoutHelper|null
     * @since 1.16.0
     */
    public static function getInstance(): ?LayoutHelper
    {
        if (self::$instance === null) {
            self::$instance = new LayoutHelper();
        }

        return self::$instance;
    }

    /**
     * Loaded assets (e.g. lang strings used by the layouts).
     * The use of that function allows us to defer their loading outside of a layout.
     * E.g. in case of ajax, we need to have those assets preloaded in the page, no matter if a layout is originally loaded.
     *
     * @param string $layout
     *
     * @throws Exception
     * @since 1.16.0
     */
    public function loadAsset($layout = '')
    {
        if (!isset($this->loadedLayouts[$layout]) || !$this->loadedLayouts[$layout]) {
            $lang = Factory::getApplication()->getLanguage();
            $strings = [];
            if ($layout == 'calendar') {
                // Add language strings
                $strings = [
                    // Days
                    'SUNDAY',
                    'MONDAY',
                    'TUESDAY',
                    'WEDNESDAY',
                    'THURSDAY',
                    'FRIDAY',
                    'SATURDAY',
                    // Short days
                    'SUN',
                    'MON',
                    'TUE',
                    'WED',
                    'THU',
                    'FRI',
                    'SAT',
                    // Months
                    'JANUARY',
                    'FEBRUARY',
                    'MARCH',
                    'APRIL',
                    'MAY',
                    'JUNE',
                    'JULY',
                    'AUGUST',
                    'SEPTEMBER',
                    'OCTOBER',
                    'NOVEMBER',
                    'DECEMBER',
                    // Short months
                    'JANUARY_SHORT',
                    'FEBRUARY_SHORT',
                    'MARCH_SHORT',
                    'APRIL_SHORT',
                    'MAY_SHORT',
                    'JUNE_SHORT',
                    'JULY_SHORT',
                    'AUGUST_SHORT',
                    'SEPTEMBER_SHORT',
                    'OCTOBER_SHORT',
                    'NOVEMBER_SHORT',
                    'DECEMBER_SHORT',
                    // Miscellaneous
                    'JLIB_HTML_BEHAVIOR_WK',
                ];

                // These are new strings. Make sure they exist.
                if ($lang->hasKey('JLIB_HTML_BEHAVIOR_AM')) {
                    Text::script('JLIB_HTML_BEHAVIOR_AM');
                }

                if ($lang->hasKey('JLIB_HTML_BEHAVIOR_PM')) {
                    Text::script('JLIB_HTML_BEHAVIOR_PM');
                }

                Text::script('MOD_JFILTERS_FILTER_DATE_RANGE_SEPARATOR');
                Text::script('MOD_JFILTERS_NUMBER_OF_ITEMS');

                // Indicate loaded
                $this->loadedLayouts[$layout] = true;
            }
            elseif ($layout == 'range_inputs') {
                $strings = [
                    'MOD_JFILTERS_FILTER_ALERT_MSG_ONLY_NUMERICAL_VALUES',
                    'MOD_JFILTERS_FILTER_ALERT_MSG_MAX_LOWER_TO_MIN',
                    'MOD_JFILTERS_FILTER_ALERT_MSG_INSERTED_VALUES_BETWEEN',
                    'MOD_JFILTERS_FILTER_ALERT_MSG_INSERTED_VALUES_HIGHER_THAN',
                    'MOD_JFILTERS_FILTER_ALERT_MSG_INSERTED_VALUES_LOWER_THAN'
                ];
                $this->loadedLayouts[$layout] = true;
            }
            // Non existent layout
            else {
                $this->loadedLayouts[$layout] = false;
            }

            foreach ($strings as $c) {
                Text::script($c);
            }
        }

        return $this->loadedLayouts[$layout];
    }

    /**
     * B/C function to load assets (e.g. lang strings used by the layouts).
     *
     * @param $layout
     * @return void
     * @throws Exception
     * @depracated
     * @since 1.16.0 Use LayoutHelper->getInstance()->load() instead
     */
    public static function load($layout = '')
    {
        self::$instance->loadAsset($layout);
    }
}