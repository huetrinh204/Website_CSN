<?php
/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\PasswordField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class JFormFieldNR_Password extends PasswordField
{
	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	public function getInput()
	{
		if (defined('nrJ4'))
		{
			return parent::getInput();
		}

		$id = $this->id . '_btn';

		$doc = Factory::getDocument();

		HTMLHelper::stylesheet('plg_system_nrframework/fields.css', false, true);

		$doc->addStyleDeclaration('
			.nr-pass-btn {
				display:flex;
				align-items:center;
			}
			.nr-pass-btn > * {
				margin:0 !important;
				padding:0 !important;
			}
			.nr-pass-btn label {
				margin-left:5px !important;
				user-select: none;
			}
		');

		$doc->addScriptDeclaration('
			jQuery(function($) {
				$("#' . $id . '").change(function() {
					var type = $(this).is(":checked") ? "text" : "password";
					$(this).closest(".nr-pass").find(".nr-pass-input input").attr("type", type);
				})
			})
		');

		return '
			<div class="nr-pass input-flex">
				<div class="input-flex-item nr-pass-input">'. parent::getInput() .'</div>
		 		<div class="input-flex-item nr-pass-btn">
					<input name="' . $id . '" id="' . $id . '" type="checkbox"/>
					<label for="' . $id . '">' . Text::_("JSHOW") . '</label>
				</div>
			</div>
		';
	}
}