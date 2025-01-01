<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Field;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Helper\LayoutHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

class MenuitemField extends ListField
{
    /**
     * @var string
     * @since 1.0.0
     */
    protected $component;

    /**
     * Cache the options
     *
     * @var array
     */
    protected $options;

    /**
     * @var string|null
     */
    protected ?string $menuType;

    /**
     * @var int|null
     */
    protected ?int $clientId;

    /**
     * @var array|string[]
     */
    protected ?array $published;

    /**
     * @var array|string[]
     */
    protected ?array $disable;

    /**
     * @var array|string[]
     */
    protected ?array $language;

    /**
     * Method to attach a Form object to the field.
     *
     * @param \SimpleXMLElement $element The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param mixed $value The form field value to validate.
     * @param string $group The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since 1.0.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);

        if ($result === true) {
            $this->menuType = (string)$this->element['menu_type'];
            $this->clientId = (int)$this->element['client_id'];
            $this->published = $this->element['published'] ? explode(',',
                (string)$this->element['published']) : array();
            $this->disable = $this->element['disable'] ? explode(',', (string)$this->element['disable']) : array();
            $this->language = $this->element['language'] ? explode(',', (string)$this->element['language']) : array();
            $this->component = (string)$this->element['component'];
        }

        return $result;
    }

    protected function getInput()
    {
        $input = parent::getInput();
        $attributes = $this->element->attributes();
        // If we use the 'createbtn' attribute, get it into account. Otherwise default to 1.
        $createMenuButton = isset($attributes['createbtn']) ? (int)$attributes['createbtn'] : 1;

        if ($input && $createMenuButton) {
            // Create a modal for the menu item creation
            $modalId = 'mod_jfilters_Menu_Modal_' . $this->id;
            $modalUrl = Route::_(
                'index.php?option=com_menus&amp;view=item&amp;layout=modal&amp;client_id=0&amp;tmpl=component&amp;task=item.add&amp;' . Session::getFormToken(
                ) . '=1'
            );
            Factory::getApplication()->getLanguage()->load('com_menus');
            $modalTitle = Text::_('COM_MENUS_NEW_MENUITEM');
            LayoutHelper::setModal($modalId, $modalTitle, $modalUrl, true);

            // Button to create a menu item
            $button = '<button'
                . ' class="btn btn-primary"'
                . ' id="' . $this->id . '_select"'
                . ' data-bs-toggle="modal"'
                . ' type="button"'
                . ' data-bs-target="#' . $modalId . '">'
                . '<span class="icon-file" aria-hidden="true"></span> ' . Text::_('JACTION_CREATE')
                . '</button>';

            $inputId = 'field-wrapper-' . $this->id;
            $input = '<div id="' . $inputId . '">
            <div class="input-group">' . $input . $button . '</div>
            </div>';
        }

        // Show a warning message if no menu item exists (1 item means the "select an option" empty option)
        if (count($this->getOptions()) <= 1 && $inputId) {
            $label = !empty($this->element['label']) ? (string)$this->element['label'] : '';
            $message = Text::sprintf('MOD_JFILTERS_CREATE_ITEMID_WARNING', Text::_($label));
            $script = "<script>
            document.addEventListener('DOMContentLoaded', () => {
                Joomla.renderMessages({warning: ['" . $message . "']}, '#" . $inputId . "');
            });</script>";
            $input .= $script;
        }

        return $input;
    }

    /**
     * Method to get the field options.
     * This method is called outside the form as well.
     *
     * @return array|bool
     * @throws \Exception
     * @since 1.0.0
     */
    public function getOptions()
    {
        if ($this->options === null) {
            /**
             * The code snippet has meaning in the filters modal, where the selection is not saved in the db
             * and is not part of the filters.
             * Hence, we have to give the value explicitly, based on what is saved in the session by our model.
             */
            $fieldId = $this->id ?? '';
            // That key is used in \Bluecoder\Component\Jfilters\Administrator\Model\FiltersModel to store the value in the session.
            $sesionKey = 'com_jfilters.filters.'. str_replace('_', '.', $fieldId);
            $storedValue = Factory::getApplication()->getUserState($sesionKey);

            if($storedValue) {
                $this->setValue($storedValue);
            }

            $db = Factory::getContainer()->get(DatabaseInterface::class);
            try {
                // Get the options.
                $db->setQuery($this->getMenuItemsQuery());
                $items = $db->loadObjectList();
            } catch (\RuntimeException $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }

            foreach ($items as $item) {
                // Set selected reading the session var

                // Displays language code if not set to All
                if ($item->language !== '*') {
                    $item->text .= ' (' . $item->language . ')';
                }
            }

            $this->options = $items;

            // $this->element is null, if we call the function directly (outside the form)
            if ($this->element) {
                $this->options = array_merge(parent::getOptions(), $items);
            }

        }
        return $this->options;
    }

    /**
     * Get the db query for the menu items.
     *
     * @return QueryInterface
     * @since 1.0.0
     */
    protected function getMenuItemsQuery(): QueryInterface
    {
        /** @var  DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
                    ->select(
                        [
                            'DISTINCT ' . $db->quoteName('a.id', 'value'),
                            $db->quoteName('a.title', 'text'),
                            $db->quoteName('a.alias'),
                            $db->quoteName('a.client_id'),
                            $db->quoteName('a.type'),
                            $db->quoteName('a.published'),
                            $db->quoteName('a.language'),
                            $db->quoteName('e.element'),
                        ]
                    )
                    ->from($db->quoteName('#__menu', 'a'))
                    ->join('LEFT', $db->quoteName('#__extensions', 'e'),
                        $db->quoteName('e.extension_id') . ' = ' . $db->quoteName('a.component_id'));

        if (Multilanguage::isEnabled()) {
            $query->select(
                [
                    $db->quoteName('l.title', 'language_title'),
                    $db->quoteName('l.image', 'language_image'),
                    $db->quoteName('l.sef', 'language_sef'),
                ]
            )
                  ->join('LEFT', $db->quoteName('#__languages', 'l'),
                      $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'));
        }

        if (isset($this->component)) {
            $query->where($db->quoteName('e.name') . ' = :extension_name')
                  ->bind(':extension_name', $this->component, ParameterType::STRING);
        }

        if (isset($this->clientId)) {
            $query->where($db->quoteName('a.client_id') . ' = :clientId')
                  ->bind(':clientId', $this->clientId, ParameterType::INTEGER);
        }

        $query->where($db->quoteName('a.published') . ' != -2');
        $query->order($db->quoteName('a.lft') . ' ASC');

        return $query;
    }
}
