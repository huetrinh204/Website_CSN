<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Joomla\CMS\Factory;
use Joomla\Filter\InputFilter;
use Joomla\Input\Input;

/**
 * Class Request
 * Handles the requests in each filter.
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Filter
 */
class Request
{
    /**
     * @var Input
     * @since 1.0.0
     */
    protected $input;

    /**
     * @var InputFilter
     * @since 1.0.0
     */
    protected $filter;

    /**
     * Limit of request values per variable.
     * Protection from DOS attacks.
     * @since 1.0.0
     */
    const MAX_ALLOWED_FILTER_VARS = 15;

    /**
     * Request constructor.
     * @param Input|null $input
     * @throws \Exception
     * @since 1.0.0
     */
    public function __construct(?Input $input)
    {
        $this->input = !empty($input->inputs) ? $input : Factory::getApplication()->getInput();
        $this->filter = new InputFilter();
    }

    /**
     * Get request values of a filter.
     *
     * @param FilterInterface $filter
     * @param array $default
     * @return array
     * @since 1.0.0
     */
    public function getVar(FilterInterface $filter, array $default = [])
    {
        $this->setAlternativeRequests($filter);
        $requestValuesTmp = $this->input->get($filter->getRequestVarName(), $default, 'ARRAY');
        $requestValuesTmp = array_slice($requestValuesTmp, 0, self::MAX_ALLOWED_FILTER_VARS);

        // clean the values based on their datatype
        $dataType = $filter->getOptions()->getOptionDataType();
        $dataType = !empty($dataType) ? strtoupper($dataType) : 'STRING';
        $requestValues = array_map(function ($value) use ($dataType, $filter) {
            // We allow empty values for the ranges. One of 2 range values can be empty.
            $cleanValue = $filter->getIsRange() && $value === '' ? $value : $this->filter->clean($value, $dataType) ;
            if ($dataType == 'DATE') {
                $format = $filter->getDisplay() != 'calendar' && $filter->getAttributes()->get('show_time', 0) ? 'Y-m-d H:i:s' : 'Y-m-d';
                $dateTmp = date_create($cleanValue);
                $cleanValue = $dateTmp ? @date_format($dateTmp, $format) : false;
            }
            return $cleanValue;
        }, $requestValuesTmp);

        // Make the keys numeric and linear
        $requestValues = array_values($requestValues);

        // Clear empty request vars (except ranges). Could cause DOS
        if (!$filter->getIsRange()) {
            $requestValues = array_filter($requestValues, function ($value) {
                return trim($value) != '';
            });
        }

        // We only want 2 values when we have ranges and 1 in single. Protect the app from DOS attacks.
        if ($filter->getIsRange() && $requestValues) {
            $requestValuesTmp = [];
            // Make sure that we only have 2 values when it is a range
            if (count($requestValues) > 1) {
                for ($i = 0; $i < 2; $i++) {
                    // Transform empty to null. Nulls are checked and not used in sql queries.
                    $requestValuesTmp[] = $requestValues[$i] != '' ? $requestValues[$i] : null;
                }
            }
            // Single (can be range with 1 date too)
            else {
                $requestValuesTmp = [reset($requestValues)];
            }
            $requestValues = $requestValuesTmp;
        }

        return $requestValues;
    }

    /**
     * Set requests from other extensions (i.e. alternative) for a given filter.
     *
     * @param FilterInterface $filter
     * @return $this
     * @since 1.0.0
     */
    protected function setAlternativeRequests(FilterInterface $filter)
    {
        $configRequests = $filter->getConfig()->getValue()->getRequests();
        if($configRequests !== null && $configRequests->getChildren()) {
            $requests = $configRequests->getChildren();
            /** @var Field\Request $request */
            foreach ($requests as $request) {
                if($this->getExtension() == $request->getExtension() && $this->getView() == $request->getView()) {
                    $currentVarName = $request->getValue();
                    $value = $this->input->get($currentVarName);
                    if($value) {
                        $filterVarName = $filter->getRequestVarName();
                        $this->input->set($filterVarName, $value);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Return the request extension name
     *
     * @return string
     * @since 1.0.0
     */
    public function getExtension() : string
    {
        return $this->input->getCmd('option', '');
    }

    /**
     * Return the request view name
     *
     * @return string
     * @since 1.0.0
     */
    public function getView() : string
    {
        return $this->input->getCmd('view', '');
    }
}
