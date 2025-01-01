<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\BaseObject;
use Bluecoder\Component\Jfilters\Administrator\Field\DisplaytypesField;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Resolver as ConfigResolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\TypeResolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface as configFilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface as FilterConfigInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\AttributesResolver;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection as OptionCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\CollectionFactory as OptionCollectionFactory;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Registry;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Request;
use Exception;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\Registry\Registry as JRegistry;

class Filter extends BaseObject implements FilterInterface
{
    /**
     * @var OptionCollection
     * @since 1.0.0
     */
    protected $optionCollection;

    /**
     * @var int
     * @since 1.0.0
     */
    protected $id;

    /**
     * @var int
     * @since 1.0.0
     */
    protected $parentId;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $configName;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $context;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $name;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $label;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $alias;

    /**
     * @var int
     * @since 1.0.0
     */
    protected $state;

    /**
     * @var boolean
     * @since 1.0.0
     */
    protected $root;

    /**
     * @var int
     * @since 1.0.0
     */
    protected $display;

    /**
     * @var Registry
     * @since 1.0.0
     */
    protected $attributes;

    /**
     * @var array
     * @since 1.0.0
     */
    protected $requestValues;

    /**
     * @var FilterConfigInterface
     * @since 1.0.0
     */
    protected $config;

    /**
     * @var Request
     * @since 1.0.0
     */
    protected $request;

    /**
     * @var TypeResolver
     * @since 1.0.0
     */
    protected $typeResolver;

    /**
     * @var ConfigResolver
     * @since 1.0.0
     */
    protected $configResolver;

    /**
     * @var OptionCollectionFactory
     * @since 1.0.0
     */
    protected $optionCollectionFactory;

    /**
     * @var AttributesResolver
     * @since 1.0.0
     */
    protected $attributesResolver;

    /**
     * Store the boolean multi-select per display
     * @var array
     * @since 1.0.0
     */
    protected $isMultiSelect = [];

    /**
     * Store the boolean isRange per display
     * @var array
     * @since 1.14.0
     */
    protected $isRange = [];

    /**
     * @var FormFactoryInterface
     * @since 1.0.0
     */
    protected $formFactory;

    /**
     * @var LoggerInterface
     * @since 1.0.0
     */
    protected $logger;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $language;

    /**
     * @var bool|null
     * @since 1.12.0
     */
    protected ?bool $isVisible;

    /**
     * Filter constructor.
     *
     * @param   Request                  $request
     * @param   ConfigResolver           $configResolver
     * @param   TypeResolver             $typeResolver
     * @param   OptionCollectionFactory  $optionCollectionFactory
     * @param   FormFactoryInterface     $formFactory
     * @param   AttributesResolver       $attributesResolver
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
        LoggerInterface $logger,
        $properties = null
    ) {
        parent::__construct($properties);
        $this->request = $request;
        $this->configResolver = $configResolver;
        $this->typeResolver = $typeResolver;
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->attributesResolver = $attributesResolver;
        $this->formFactory = $formFactory;
        $this->logger = $logger;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): FilterInterface
    {
        $this->id = $id;

        return $this;
    }

    /**
     * The parent id is referred to the id of the parent field (e.g. com_fields.field)
     *
     * @return int
     * @since 1.0.0
     */
    public function getParentId(): int
    {
        return $this->parentId;
    }

    public function setParentId(int $parentId): FilterInterface
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function getConfigName(): string
    {
        if (empty($this->configName)) {
            throw new \RuntimeException('No \'configName\' is set for a filter');
        }

        return $this->configName;
    }

    public function setConfigName(string $configName): FilterInterface
    {
        $this->configName = $configName;

        return $this;
    }

    public function getContext(): string
    {
        if (empty($this->context)) {
            throw new \RuntimeException('No \'context\' is set for a filter');
        }

        return $this->context;
    }

    public function setContext(string $context): FilterInterface
    {
        $this->context = $context;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): FilterInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): FilterInterface
    {
        $this->label = $label;

        return $this;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function setAlias($alias): FilterInterface
    {
        $this->alias = $alias;

        return $this;
    }

    public function getDisplay(): string
    {
        return $this->display;
    }

    public function setDisplay(string $layout): FilterInterface
    {
        $this->display = $layout;

        return $this;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): FilterInterface
    {
        $this->state = $state;

        return $this;
    }

    public function getRoot(): bool
    {
        return $this->root;
    }

    public function setRoot(bool $isRoot): FilterInterface
    {
        $this->root = $isRoot;

        return $this;
    }

    /**
     * Get the Options of the Filter
     * We get the preferable Option\Collection from the config
     *
     * @return OptionCollection
     * @throws Exception
     * @since 1.0.0
     */
    public function getOptions(): OptionCollection
    {
        if ($this->optionCollection === null) {
            $optionsCollectionClass = $this->typeResolver->getTypeClass($this,
                configFilterInterface::SECTION_VALUE_NAME);
            if (!empty($optionsCollectionClass)) {
                try {
                    $this->optionCollection = $this->optionCollectionFactory->create($optionsCollectionClass);
                } catch (Exception $e) {
                    $this->logger->critical($e);
                    throw $e;
                }
            } else {
                try {
                    $this->optionCollection = $this->optionCollectionFactory->create();
                } catch (Exception $e) {
                    $this->logger->critical($e);
                    throw $e;
                }
            }
            $this->optionCollection->clear();
            $this->optionCollection->setFilterItem($this);
        }

        return $this->optionCollection;
    }

    public function setOptions(OptionCollection $optionCollection): FilterInterface
    {
        $this->optionCollection = $optionCollection;

        return $this;
    }

    public function getConfig(): FilterConfigInterface
    {
        if ($this->config == null) {
            $this->config = $this->configResolver->getFilterConfig($this);
        }

        return $this->config;
    }

    public function getAttributes(array $names = []): Registry
    {
        if ($this->attributes === null) {
            $this->attributes = new Registry();
        } else {
            // Cast the attributes from JRegistry to Registry (our Registry)
            $this->attributes = new Registry($this->attributes);
        }

        $attributes = $this->attributes;
        if($names) {
            $attributes = new Registry([]);
            foreach ($names as $name) {
                $attributes->set($name, $this->attributes->get($name));
            }

            // Add the 'isPro' so that PRO functionalities can be utilized
            $attributes->set('isPro', $this->attributes->get('isPro'));
        }
        return $attributes;
    }

    public function setAttributes(JRegistry $attributes): FilterInterface
    {
        $this->attributes = $attributes;
        $this->attributesResolver->resolveEmptyToDefaults($this);

        return $this;
    }

    public function setLanguage(string $language): FilterInterface
    {
        $this->language = $language;

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getIsMultiSelect(): bool
    {
        if (!isset($this->isMultiSelect[$this->getDisplay()])) {
            $isMultiSelect = false;
            $form = $this->createForm($this->getData());
            if ($form) {
                /** @var DisplaytypesField $displayField */
                $displayField = $form->getField('display');
                $displayField->setFilter($this);
                // load that from the display form field
                $isMultiSelect = $displayField->isMultiSelect();
            }
            $this->isMultiSelect[$this->getDisplay()] = (boolean)$isMultiSelect;
        }

        return $this->isMultiSelect[$this->getDisplay()];
    }

    public function getIsRange() : bool
    {
        if (!isset($this->isRange[$this->getDisplay()])) {
            $isRange = false;
            $form = $this->createForm($this->getData());
            if ($form) {
                /** @var DisplaytypesField $displayField */
                $displayField = $form->getField('display');
                if ($displayField) {
                    $displayField->setFilter($this);
                    // load that from the display form field
                    $isRange = $displayField->isRange();
                }
            }
            $this->isRange[$this->getDisplay()] = (boolean)$isRange;
        }

        $isRange = $this->isRange[$this->getDisplay()];

        // Calendars are not ranges by default, but they can be set as such in their settings.
        if ($this->getDisplay() == 'calendar' && $this->getAttributes()->get('calendar_mode') == 'range') {
            $isRange = true;
        }
        return $isRange;
    }

    /**
     * Create the form of the filter
     *
     * @param $data
     *
     * @return Form
     * @since 1.0.0
     */
    protected function createForm($data): Form
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jfilters' . DIRECTORY_SEPARATOR . 'forms');
        $form = $this->formFactory->createForm('com_jfilters.filter', ['control' => 'jform']);
        $form->loadFile('filter', false);
        $form->bind($data);

        return $form;
    }

    public function getRequestVarName(): string
    {
        $varName = self::URL_FILTER_VAR_NAME . $this->getId();
        if ($this->getAlias() !== null) {
            $varName = $this->getAlias();
        }

        return $varName;
    }

    public function getRequest(): array
    {
        if (!isset($this->requestValues)) {
            $this->requestValues = $this->request->getVar($this);
        }

        return $this->requestValues;
    }

    public function setRequest(array $requestValues): FilterInterface
    {
        $this->requestValues = $requestValues;

        return $this;
    }

    public function isVisible(): bool
    {
        if ($this->isVisible === null) {
            $this->isVisible = $this->getState() == 1;
        }

        return $this->isVisible;
    }

    public function setIsVisible(bool $visible): FilterInterface
    {
        $this->isVisible = $visible;
        return $this;
    }

}
