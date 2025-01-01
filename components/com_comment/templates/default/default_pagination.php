<?php
/**
 * @package    Ccomment
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       24.04.17
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
?>

<script type="text/x-template" id="ccomment-pagination">
	<nav class="pagination text-center" v-if="pagination.last_page > 0">
		<ul >
			<li v-if="showPrevious()" :class="{ 'disabled' : pagination.current_page <= 1 }">
				<span v-if="pagination.current_page <= 1">
					<span aria-hidden="true"><?php echo JText::_('JPREV'); ?></span>
				</span>
				<a href="#" v-if="pagination.current_page > 1 " :aria-label="config.ariaPrevioius"
				   @click.prevent="changePage(pagination.current_page - 1)">
					<span aria-hidden="true"><?php echo JText::_('JPREV'); ?></span>
				</a>
			</li>
			<li v-for="num in array" :class="{ 'active': num === pagination.current_page }">
				<a href="#" @click.prevent="changePage(num)">{{ num }}</a>
			</li>
			<li v-if="showNext()"
			    :class="{ 'disabled' : pagination.current_page === pagination.last_page || pagination.last_page === 0 }">
				<span v-if="pagination.current_page === pagination.last_page || pagination.last_page === 0">
					<span aria-hidden="true"><?php echo JText::_('JNEXT'); ?></span>
				</span>
				<a href="#" v-if="pagination.current_page < pagination.last_page" :aria-label="config.ariaNext"
				   @click.prevent="changePage(pagination.current_page + 1)">
					<span aria-hidden="true"><?php echo JText::_('JNEXT'); ?></span>
				</a>
			</li>
		</ul>
	</nav>
</script>
