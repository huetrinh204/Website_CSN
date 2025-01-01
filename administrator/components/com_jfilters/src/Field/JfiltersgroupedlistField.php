<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Field;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Form\Field\GroupedlistField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class JfiltersgroupedlistField extends GroupedlistField
{
    use SetupTrait;
    use RenderFieldTrait;

    /**
     * @var FilterInterface
     * @since 1.0.0
     */
    protected $filter;

    public function getGroups()
    {
        $groups = [];
        $label  = $this->layout === 'joomla.form.field.groupedlist-fancy-select' ? '' : 0;
        foreach ($this->element->children() as $element) {
            switch ($element->getName()) {
                case 'option':
                    // The element is an <option />
                    // Initialize the group if necessary.
                    if (!isset($groups[$label])) {
                        $groups[$label] = [];
                    }

                    $disabled = (string) $element['disabled'];
                    $disabled = ($disabled === 'true' || $disabled === 'disabled' || $disabled === '1');

                    // Create a new option object based on the <option /> element.
                    $tmp = HTMLHelper::_(
                        'select.option',
                        ($element['value']) ? (string) $element['value'] : trim((string) $element),
                        Text::alt(trim((string) $element), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)),
                        'value',
                        'text',
                        $disabled
                    );

                    // Added attributes for JFilters
                    $tmp->multiselect = $element['multiselect'] == 'true';
                    $tmp->range = $element['range'] == 'true';
                    $tmp->edition = $element['edition']  ? (string) $element['edition'] : 0;
                    $tmp->dataType = $element['dataType']  ? (string) $element['dataType'] : '';

                    // Set some option attributes.
                    $tmp->class = (string) $element['class'];

                    // Set some JavaScript option attributes.
                    $tmp->onclick = (string) $element['onclick'];

                    // Add the option.
                    $groups[$label][] = $tmp;
                    break;

                case 'group':
                    // The element is a <group />
                    // Get the group label.
                    if ($groupLabel = (string) $element['label']) {
                        $label = Text::_($groupLabel);
                    }

                    // Initialize the group if necessary.
                    if (!isset($groups[$label])) {
                        $groups[$label] = [];
                    }

                    // Iterate through the children and build an array of options.
                    foreach ($element->children() as $option) {
                        // Only add <option /> elements.
                        if ($option->getName() !== 'option') {
                            continue;
                        }

                        $disabled = (string) $option['disabled'];
                        $disabled = ($disabled === 'true' || $disabled === 'disabled' || $disabled === '1');

                        // Create a new option object based on the <option /> element.
                        $tmp = HTMLHelper::_(
                            'select.option',
                            ($option['value']) ? (string) $option['value'] : Text::_(trim((string) $option)),
                            Text::_(trim((string) $option)),
                            'value',
                            'text',
                            $disabled
                        );

                        // Added attributes for JFilters
                        $tmp->multiselect = $option['multiselect'] == 'true';
                        $tmp->range = $option['range'] == 'true';
                        $tmp->edition = $option['edition']  ? (string) $option['edition'] : 0;
                        $tmp->dataType = $option['dataType']  ? (string) $option['dataType'] : '';

                        // Set some option attributes.
                        $tmp->class = (string) $option['class'];

                        // Set some JavaScript option attributes.
                        $tmp->onclick = (string) $option['onclick'];

                        // Add the option.
                        $groups[$label][] = $tmp;
                    }

                    if ($groupLabel) {
                        $label = \count($groups);
                    }
                    break;

                default:
                    // Unknown element type.
                    throw new \UnexpectedValueException(sprintf('Unsupported element %s in GroupedlistField', $element->getName()), 500);
            }
        }

        $this->adjustProOptions($groups);

        reset($groups);

        return $groups;
    }

    /**
     * Disable the PRO options in FREE edition and add the 'PRO' label besides each PRO option.
     *
     * @param array $groups
     *
     * @return $this
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function adjustProOptions($groups)
    {
        /** @var ComponentConfig $componentConfig */
        $componentConfig = ObjectManager::getInstance()->getObject(ComponentConfig::class);

        foreach ($groups as &$group) {
            foreach ($group as &$option) {
                // Disable options in no Pro edition.
                if (!$componentConfig->get('isPro') && isset($option->edition) && (int)$option->edition == 100) {
                    $option->disable = true;
                    $option->text .= ' [PRO]';
                }
            }
        }

        return $this;
    }
}