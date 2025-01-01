<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Unit\Model\Config;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Section;

class MySection extends Section
{
    /**
     * @var Field
     */
    protected $name;

    /**
     * @var Field
     */
    protected $address;

    /**
     * @return Field|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Field $name
     * @return Section
     */
    public function setName(Field $name): Section
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Field|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param Field $address
     * @return Section
     */
    public function setAddress(Field $address): Section
    {
        $this->address = $address;
        return $this;
    }
}
