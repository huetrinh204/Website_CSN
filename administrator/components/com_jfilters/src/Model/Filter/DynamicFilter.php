<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Filter;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;

class DynamicFilter extends Filter implements DynamicFilterInterface
{
    /**
     * @var string
     * @since 1.0.0
     */
    protected $type;

    public function setType(string $type): FilterInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): ?string
    {
        if ($this->type === null) {
            // we store the type in the attributes, when generated.
            $this->type = $this->getAttributes()->get('type');
        }

        return $this->type;
    }
}
