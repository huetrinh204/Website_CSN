<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2021 - 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Field;

\defined('_JEXEC') or die();

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Editor\Button\Button;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Session\Session;
use SimpleXMLElement;

class FiltersModalButtonField extends FormField
{
    /**
     * Enabled actions: select, clear, edit, new
     *
     * @var    boolean[]
     * @since  1.15.0
     */
    protected $canDo = [];

    /**
     * Method to attach a Form object to the field.
     *
     * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value.
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   1.15.0
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);

        if (!$result) {
            return $result;
        }

        // Prepare enabled actions
        $this->__set('clear', (string) $this->element['clear'] != 'false');

        return $result;
    }

    /**
     * @param $name
     * @return bool|mixed|string
     * @since 1.15.0
     */
    public function __get($name)
    {
        switch ($name) {
            case 'clear':
                $return = $this->canDo['clear'] ?? true;
                break;
            default:
                $return = parent::__get($name);
        }

        return $return;
    }

    /**
     * Method to set certain otherwise inaccessible properties of the form field object.
     *
     * @param   string  $name   The property name for which to set the value.
     * @param   mixed   $value  The value of the property.
     *
     * @return  void
     *
     * @since   1.15.0
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'clear':
                $this->canDo['clear'] = (bool) $value;
                break;
            default:
                parent::__set($name, $value);
        }
    }


    protected function getInput()
    {
        // Check if `com_jfilters` is enabled
        if (!ComponentHelper::isEnabled('com_jfilters')) {
            return '';
        }

        // Guess the field context based on view.
        $jinput = Factory::getApplication()->input;
        $context = 'amp;context=' . $jinput->get('option') . '.' . $jinput->get('view');
        $client = 'editor';


        if ( $jinput->get('option') == 'com_menus') {
            $client = 'com_menus';
            $context = ''; //'amp;context=' . $jinput->get('filter[context]', 'com_content.article');
        }
        elseif ( $jinput->get('option') == 'com_modules') {
            $context = '';
            $client = 'com_modules';
        }

        // Load the JFilters language files. This usually called outside the com_jfilters context (e.g. from a module).
        Factory::getApplication()->getLanguage()->load('com_jfilters', JPATH_ADMINISTRATOR);

        $link = 'index.php?option=com_jfilters&view=filters&layout=modal&tmpl=component&JFClient=' . $client
            . $context . '&' . Session::getFormToken() . '=1&' . $this->value;

        $data = $this->getLayoutData();
        $btnName =  $this->type . '_' . $this->name;
        $btnId = 'jfilters_filters-modal-' . md5($this->name);

        // Joomla 4
        if (version_compare(\JVersion::MAJOR_VERSION, '5') == -1) {
            $button = new CMSObject();
            $button->modal = true;
            $button->link = $link;
            // The Modal Heading
            $button->text = Text::_('COM_JFILTERS_FILTERS');
            $button->name = $btnName;
            $button->id = $btnId;
            $button->options = [
                'confirmCallback' => 'applyFilters(\'module\');',
                'confirmText' => Text::_('COM_JFILTERS_APPLY_FILTERS'),
            ];
        }
        // Joomla 5
        else {
            $button = new Button( $btnName, ['name' => $btnName, 'modal' => true, 'link' => $link, 'text' => Text::_('COM_JFILTERS_FILTERS'), 'id' => $btnId],
                [
                'confirmCallback' => 'applyFilters(\'module\');',
                'confirmText'     => Text::_('COM_JFILTERS_APPLY_FILTERS'),
            ]);
        }

        /* @todo a use layout for extracting the html. @see libraries/src/Form/Field/ModalSelectField.php */
        $placeholder = $this->hint ? 'placeholder = "' . htmlspecialchars(Text::_($this->hint)) .'"' : '';
        $html = '
        <div class="js-modal-content-select-field">
        <div class="input-group">
        <input type="text" class="jfilters_filters_selected form-control js-input-value" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" name="' . $this->name . '" readonly '. $placeholder . '/>
        <button value="' . $link . '" class="button-preview btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#' . $btnId . '">
        <span class="icon-list" aria-hidden="true"></span>
    ' . Text::_('COM_JFILTERS_FILTERS_SET_FILTERS') . '</button>';

        if ($this->clear ?? true) :
            $html.= '
            <button class="jfiltersClearBtn btn btn-secondary" type="button" data-button-action="clear" data-show-when-value="1">
            <span class="icon-times" aria-hidden="true"></span> ' . Text::_('JCLEAR') .'</button>';
        endif;

        $html.= '</div></div>';

        $html .= LayoutHelper::render('joomla.editors.buttons.modal', $button);

        /** @var \Joomla\CMS\Document\Document $document */
        $document = Factory::getApplication()->getDocument();
        $wa = $document->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_jfilters');
        $wa->useScript('com_jfilters.menu-item');

        return $html;
    }
}