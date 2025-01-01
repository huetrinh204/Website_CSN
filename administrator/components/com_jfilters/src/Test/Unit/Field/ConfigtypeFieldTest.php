<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Unit\Field;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Field\ConfigtypeField;
use PHPUnit\Framework\TestCase;

class ConfigtypeFieldTest extends TestCase
{
    public function testCreateLabel()
    {
        $this->assertEquals('MYCATEGORIES', ConfigtypeField::createLabel('category'));
    }

    public function testCreateLabelNotFound()
    {
        $this->assertEquals('COM_JFILTERS_CONFIG_TYPE_MYFIELD', ConfigtypeField::createLabel('myfield'));
    }
}
