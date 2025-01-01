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
$displayData = ccommentHelperUtils::getFileUploadConfig();
?>

<div class="fileupload">
    <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
    <div class="row fileupload-buttonbar">
        <input type="file" name="files[]" class="js-ccomment-file-upload-real ccomment-file-upload-real" multiple/>
        <div class="panel panel-default compojoom-notes">
            <div class="panel-body">
                <!-- The global file processing state -->
                <span class="fileupload-process"><span class="fa fa-spinner fa-pulse"></span></span>
                <?php echo JText::sprintf('LIB_COMPOJOOM_ATTACH_IMAGES_BY_DRAG_DROP_OR', '<span type="button" class="js-file-upload-fake ccomment-file-upload-fake btn-link">', '</span>'); ?>
                <br/>
                <small class="muted"><?php echo JText::sprintf('LIB_COMPOJOOM_THE_MAXIMUM_FILE_SIZE', $displayData['maxSize'] . 'MB'); ?>
                    <?php echo JText::sprintf('LIB_COMPOJOOM_ONLY_FILE_TYPES_ARE_ALLOWED', $displayData['fileTypes']); ?></small>

                <!-- The global progress state -->
                <div class="fileupload-progress fade hide d-none">
                    <!-- The global progress bar -->
                    <div class="progress progress-striped active" role="progressbar" aria-valuemin="0"
                         aria-valuemax="100">
                        <div class="progress-bar progress-bar-success" style="width:0%;"></div>
                    </div>
                    <!-- The extended global progress state -->
                    <div class="progress-extended">&nbsp;</div>
                </div>
                <div class="ccomment-file-list">
                    <div class="alert alert-warning hide d-none compojoom-max-number-files">
                        <?php echo JText::sprintf('LIB_COMPOJOOM_MAX_NUMBER_OF_FILES_REACHED', $displayData['maxNumberOfFiles']); ?>
                    </div>
                    <table role="presentation" class="table table-striped">
                        <thead></thead>
                        <tbody class="files"></tbody>
                    </table>
                    <div class="alert alert-warning hide d-none compojoom-max-number-files">
                        <?php echo JText::sprintf('LIB_COMPOJOOM_MAX_NUMBER_OF_FILES_REACHED', $displayData['maxNumberOfFiles']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
