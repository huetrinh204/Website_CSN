<?php
/**
 * @name		Slideshow CK
 * @package		com_slideshowck
 * @copyright	Copyright (C) 2019. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @author		Cedric Keiflin - https://www.template-creator.com - https://www.joomlack.fr
 */
 
// No direct access
defined('CK_LOADED') or die;

use Slideshowck\CKController;
use Slideshowck\CKFof;

require_once SLIDESHOWCK_PATH . '/helpers/ckbrowse.php';

class SlideshowckControllerBrowse extends CKController {

	public function getFiles() {
		// security check
		if (! CKFof::checkAjaxToken()) {
			exit();
		}

		$folder = $this->input->get('folder', '', 'string');
		$type = $this->input->get('type', '', 'string');
		$filetypes = CKBrowse::getFileTypes($type);
		$files = CKBrowse::getImagesInFolder(JPATH_SITE . '/' . $folder, implode('|', $filetypes));

		if ($type == 'folder') {
			$pathway = str_replace('/', '</span><span class="ckfoldertreepath">', $folder);
			?>
			<div id="ckfoldertreelistfolderselection">
				<div class="ckbutton ckbutton-primary" style="font-size:20px;padding: 10px 20px;" onclick="ckSelectFolder('<?php echo ($folder) ?>')"><i class="fas fa-check-square"></i> <?php echo \Joomla\CMS\Language\Text::_('CK_SELECT_FOLDER') ?><br /><small><?php echo $pathway ?></small></div>
			</div>
		<?php }
		if (empty($files)) {
			echo \Joomla\CMS\Language\Text::_('CK_NO_IMAGE_FOUND');
		} else {
			foreach($files as $file) {
				?>
					<div class="ckfoldertreefile" data-type="<?php echo $type ?>" onclick="ckSelectFile(this)" data-path="<?php echo iconv('ISO-8859-1', 'UTF-8', $folder) ?>" data-filename="<?php echo iconv('ISO-8859-1', 'UTF-8', $file) ?>">
						<img src="<?php echo \Joomla\CMS\Uri\Uri::root(true) . '/' . iconv('ISO-8859-1', 'UTF-8', $folder) . '/' . iconv('ISO-8859-1', 'UTF-8', $file) ?>" title="<?php echo iconv('ISO-8859-1', 'UTF-8', $file); ?>" loading="lazy">
						<div class="ckimagetitle"><?php echo iconv('ISO-8859-1', 'UTF-8', $file); ?></div>
					</div>
				<?php
			}
		}
		exit;
	}
}
