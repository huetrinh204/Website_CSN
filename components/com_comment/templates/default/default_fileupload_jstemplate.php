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

$user       = JFactory::getUser();
$displayData = ccommentHelperUtils::getFileUploadConfig();
$canDelete   = $user->authorise('core.multimedia.delete', $displayData['component']) || $user->authorise('core.multimedia.delete.own', $displayData['component']);

?>

<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td>
            <span class="preview"></span>
        </td>
        <td>
         <span class="name"><i>{%=file.name%}</i></span>
            <div class="compojoom-single-file-progress">
	            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
	                <div class="progress-bar progress-bar-success" style="width:0%;"></div>
	            </div>
	           <small><strong class="size"><?php echo JText::_('LIB_COMPOJOOM_PROCESSING'); ?>...</strong></small>
			</div>
        </td>
        <td>
            {% if (!i && !o.options.autoUpload) { %}
                <button class="btn btn-default btn-xs start" disabled>
                    <i class="fa fa-upload"></i>
                    <span><?php echo JText::_('LIB_COMPOJOOM_START'); ?></span>
                </button>
            {% } %}
            {% if (!i) { %}
                <button class="btn btn-default btn-xs btn-xs cancel pull-left">
                    <i class="fa fa-stop"></i>
                    <span><?php echo JText::_('LIB_COMPOJOOM_CANCEL'); ?></span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
</script>

<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        <td style="">
        {% if (file.thumbnailUrl) { %}
            <span class="preview">
                {% if (file.url) { %}
					<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery>
						<img src='{%=file.thumbnailUrl%}'>
					</a>
				{% } else { %}
					<img src='{%=file.thumbnailUrl%}'>
				{% } %}
            </span>
		{% } %}
        </td>
        <td>
        {% if (!file.error) { %}
	        <div class="file-meta">
			    <div class="row">
			        <div class="col-lg-4">
			           <input type="text" class="form-control"
			                placeholder="<?php echo JText::_('LIB_COMPOJOOM_TITLE'); ?>"
							name="<?php echo $displayData['formControl']; ?>[<?php echo $displayData['fieldName']; ?>_data][{%=file.name%}][title]"
					        value="{%=file.title%}" />
			        </div>
			        <div class="col-lg-8">
			            <input type="text" placeholder="<?php echo JText::_('LIB_COMPOJOOM_DESCRIPTION'); ?>" class="form-control"
					                name="<?php echo $displayData['formControl']; ?>[<?php echo $displayData['fieldName']; ?>_data][{%=file.name%}][description]"

					                value="{%=file.description%}" />
			        </div>
			    </div>
	        </div>
		 {% } %}
        {% if (file.error) { %}
            <div><span class="label label-danger"><?php echo JText::_('LIB_COMPOJOOM_ERROR'); ?></span> {%=file.error%}</div>
        {% } %}
        </td>
        <td style="text-align: center">
            {% if (file.deleteUrl) { %}
                <?php if ($canDelete) : ?>
	                <button class="btn btn-danger btn-xs delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
	                    <i class="fa fa-trash-o"></i>
	                    <span><?php echo JText::_('LIB_COMPOJOOM_DELETE'); ?></span>
	                </button>
	                <div>
		                <small class="size muted">{%=o.formatFileSize(file.size)%}</small>
	                </div>
                <?php endif; ?>
            {% } else { %}
                 <button class="btn btn-default btn-xs btn-xs cancel">
                    <i class="fa fa-stop"></i>
                    <span><?php echo JText::_('LIB_COMPOJOOM_CANCEL'); ?></span>
                </button>
            {% }%}
            {% if (!file.error) { %}
            <input type="hidden" name="<?php echo $displayData['formControl']; ?>[<?php echo $displayData['fieldName']; ?>][]" value="{%=file.name%}" />
            {% } %}
        </td>
    </tr>
{% } %}
</script>
