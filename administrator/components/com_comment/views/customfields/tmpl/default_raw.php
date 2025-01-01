<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       27.01.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

?>

<?php if($this->form->getGroup('customfields')) : ?>
	<ul>
		<?php foreach($this->form->getGroup('customfields') as $custom) : ?>
			<li><?php echo $custom->label; ?>
				<?php echo $custom->input; ?></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>