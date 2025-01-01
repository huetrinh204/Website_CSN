<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

\defined('_JEXEC') or die();

/**
 * This is the app's dependency injection config.
 * Contains the preferences for the used Interfaces.
 * By changing the array values, the used classes can change without breaking the application.
 * The preferred classes just need to implement the mentioned Interface (array key).
 */
$preferences =[
    '\Joomla\CMS\Application\CMSApplicationInterface' => '\Joomla\CMS\Application\SiteApplication',
    '\Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface' => '\Bluecoder\Component\Jfilters\Administrator\Model\Filter',
    '\Bluecoder\Component\Jfilters\Administrator\Model\Filter\DynamicFilterInterface' => '\Bluecoder\Component\Jfilters\Administrator\Model\Filter\DynamicFilter',
    '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\FiltersConfigReaderInterface' => '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\FiltersXMLConfigReader',
    '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\ContextsConfigReaderInterface' => '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\ContextsXMLConfigReader',
    '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\Filters\DynamicConfigReaderInterface' => '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Reader\Filters\DynamicConfigReader',
    '\Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface' => '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter',
    '\Bluecoder\Component\Jfilters\Administrator\Model\Config\ContextInterface' => '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Context',
    '\Bluecoder\Component\Jfilters\Administrator\Model\Config\SectionInterface' => '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Section',
    '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\DynamicInterface' => '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Dynamic',
    '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section\DefinitionInterface' => '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section\Definition',
    '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section\ValueInterface' => '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section\Value',
    '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section\ValueItemRefInterface' => '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section\ValueItemRef',
    '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Section\ItemInterface' => '\Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Section\Item',
    '\Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionInterface' => '\Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option',
    '\Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\UriHandlerInterface' => '\Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\UriHandler',
    '\Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\NestedInterface' => '\Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Nested',
    '\Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface' => '\Bluecoder\Component\Jfilters\Administrator\Model\Logger',
];
