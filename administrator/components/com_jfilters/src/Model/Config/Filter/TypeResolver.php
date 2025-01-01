<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection as ConfigFilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\SectionInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;

class TypeResolver
{
    /**
     * @var Collection
     * @since 1.0.0
     */
    protected $filterConfigCollection;

    /**
     * @var LoggerInterface
     * @since 1.0.0
     */
    protected $logger;

    /**
     * TypeResolver constructor.
     * @param Collection $configFilterCollection
     * @param LoggerInterface $logger
     */
    public function __construct(ConfigFilterCollection $configFilterCollection, LoggerInterface $logger)
    {
        $this->filterConfigCollection = $configFilterCollection;
        $this->logger = $logger;
    }

    /**
     * Resolve and return the type 'class' from the specified configuration section.
     *
     * @param $item
     * @param string $section
     * @return string|null
     * @since 1.0.0
     */
    public function getTypeClass($item, string $section)
    {
        $typeClass = null;
        $section = ucfirst(strtolower($section));
        if(method_exists($item, 'getConfigName')) {
            $configName = $item->getConfigName();
        }
        else {
            /*
             * Most times that function is called before the actual object is instantiated.
             * We need to access the \stdClass row properties
             */
            $configName = $item->config_name;
        }
        $filterConfig = $this->filterConfigCollection->getByNameAttribute($configName);
        $functionName = 'get'.$section;
        if (method_exists($filterConfig, $functionName)) {
            /** @var  SectionInterface $section */
            $section = $filterConfig->{$functionName}();
            if (is_subclass_of($section, SectionInterface::class)) {
                try {
                    $typeClass = $section->getClass();
                }
                catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                }
            }
        }
        return $typeClass;
    }
}
