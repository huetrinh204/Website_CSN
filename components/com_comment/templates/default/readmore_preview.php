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
use Joomla\String\StringHelper;

$previewLength = $this->config->get('template_params.preview_length', 80);
$dateFormat = $this->config->get('layout.date_format', 'age');
?>

<div class='ccomment-preview-container'>
	<?php foreach ($this->comments as $value) : ?>
		<?php
		if ($value->title != '') {
			$title = stripslashes($value->title);
		} else {
			$title = stripslashes($value->comment);
		}
		if (StringHelper::strlen($title) > $previewLength) {
			$title = StringHelper::substr($title, 0, $previewLength) . '...';
		}
		?>
		<div class='ccomment-preview'>
			<a href="<?php echo $this->link; ?>#ccomment-comment<?php echo $value->id; ?>">
				<?php echo ccommentHelperComment::getLocalDate($value->date, $dateFormat); ?>
				<b><?php echo $title; ?></b>
			</a>
		</div>
	<?php endforeach; ?>
</div>
