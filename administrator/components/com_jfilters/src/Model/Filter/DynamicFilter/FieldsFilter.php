<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter\DynamicFilter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Resolver as ConfigResolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\TypeResolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\AttributesResolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\DynamicFilter;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\CollectionFactory as OptionCollectionFactory;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Request;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

class FieldsFilter extends DynamicFilter
{
    /**
     * @var DatabaseInterface
     * @since 1.0.0
     */
    protected $database;

    /**
     * @var Registry
     * @since 1.0.0
     */
    protected $params;

    /**
     * FieldsFilter constructor.
     *
     * @param   Request                  $request
     * @param   ConfigResolver           $configResolver
     * @param   TypeResolver             $typeResolver
     * @param   OptionCollectionFactory  $optionCollectionFactory
     * @param   FormFactoryInterface     $formFactory
     * @param   AttributesResolver       $attributesResolver
     * @param   DatabaseInterface        $database
     * @param   LoggerInterface          $logger
     * @param   null                     $properties
     * @since 1.0.0
     */
    public function __construct(
        Request $request,
        ConfigResolver $configResolver,
        TypeResolver $typeResolver,
        OptionCollectionFactory $optionCollectionFactory,
        FormFactoryInterface $formFactory,
        AttributesResolver $attributesResolver,
        DatabaseInterface $database,
        LoggerInterface $logger,
        $properties = null)
    {
        $this->database = $database;
        parent::__construct($request, $configResolver, $typeResolver, $optionCollectionFactory, $formFactory, $attributesResolver, $logger, $properties);
    }

    /**
     * Function that gets the field params for each field/filter.
     *
     * The field params are essential as they contain the label of each value
     *
     * @return Registry
     * @since 1.0.0
     */
    public function getParams(): Registry
    {
        if ($this->params === null) {
            $tableName = $this->getConfig()->getDefinition()->getDbTable();
            $idColumn = $this->getConfig()->getDefinition()->getId()->getDbColumn();
            $filterParentId = (int) $this->getParentId();
            $query = $this->database->getQuery(true);
            $query->select($this->database->quoteName('fieldParams'))
                ->select($this->database->quoteName($idColumn, 'id')) //needed for Falang to allow translation in frontend
                ->from($this->database->quoteName($tableName))
                ->where($this->database->quoteName($idColumn) . '= :id')
                ->bind(':id',$filterParentId , ParameterType::INTEGER);

            $this->database->setQuery($query);
            $paramsTmp = $this->database->loadResult();
            $this->params = new Registry($paramsTmp);
        }
        return $this->params;
    }
}
