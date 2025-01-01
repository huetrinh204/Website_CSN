<?php
/**
 * @package    Ccomment
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       24.04.17
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

$user = JFactory::getUser();
$config = $this->config;
?>
<?php if ($this->config->get('layout.show_copyright', 1)) : ?>
	<div class="row-fluid small muted ccomment-powered">
		<p class="text-center">
			<?php echo JText::sprintf('COM_COMMENT_POWERED_BY', "<a href='https://compojoom.com' rel='nofollow' target='_blank'>CComment</a>"); ?>
		</p>
	</div>
<?php endif; ?>