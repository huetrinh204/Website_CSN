<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection;

\defined('_JEXEC') or die();

interface FilteredInterface
{

    /**
     * Use the selections in the other filters as conditions, for getting the options of that filter.
     *
     * @param   bool  $use
     *
     * @return bool true if the collection is cleared, false otherwise
     * @since 1.0.0
     */
    public function setUseOtherSelectionsAsConditions(bool $use): bool;

    /**
     * Adds item ids, as condition, based on the search query.
     * @param string|null $context
     * @return FilteredInterface
     * @throws \Exception
     * @since 1.0.0
     */
    public function setQueryCondition(?string $context): FilteredInterface;
}
