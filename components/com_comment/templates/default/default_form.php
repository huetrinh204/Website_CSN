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
$formAvatar = $this->config->get('template_params.form_avatar');
$user       = JFactory::getUser();
?>

<?php if (!$this->discussionClosed) : ?>
	<?php if ($this->allowedToPost) : ?>


		<script type="text/x-template" id="ccomment-form">
			<form class="ccomment-form" v-on:submit.prevent="onSubmit">
				<div class="ccomment-error-form row-fluid  margin-bottom" v-if="error">
					<div class="alert alert-error">
						{{errorMessage}}
					</div>
				</div>
				<div class="ccomment-info-form row-fluid  margin-bottom" v-if="info">
					<div class="alert alert-info">
						{{infoMessage}}
					</div>
				</div>
				<div class="row-fluid margin-bottom">
					<?php if ($formAvatar) : ?>
						<div class="span1 hidden-phone">
							<ccomment-avatar v-bind:avatar="getAvatar"></ccomment-avatar>
						</div>
					<?php endif; ?>

					<div class="<?php echo ($formAvatar) ? 'span11' : 'row-fluid'; ?>">
						<textarea v-on:focus="toggle"
								  name="jform[comment]"
								  class='js-ccomment-textarea ccomment-textarea span12 required'
								  placeholder="<?php echo JText::_('COM_COMMENT_LEAVE_COMMENT'); ?>"
						></textarea>

						<div v-show="uploadImage">
							<?php echo $this->loadTemplate('fileupload_html'); ?>
						</div>

						<div v-show="active">
							<div class="span4 muted small">
								<?php echo JText::_('COM_COMMENT_POSTING_AS'); ?>
								<?php if ($user->guest) : ?>
									<button type="button"
											v-on:click="display = !display"
											class="btn-link btn-small ccomment-posting-as">{{getDefaultName}}
									</button>
								<?php else : ?>
									<span class="ccomment-posting-as">
							<?php if ($this->config->get('layout.use_name', 1)) : ?>
								<?php echo $user->name; ?>
							<?php else : ?>
								<?php echo $user->username; ?>
							<?php endif; ?>
						</span>
									<!--					<button class="btn-link btn-mini ccomment-not-you">(--><?php //echo JText::_('COM_COMMENT_NOT_YOU'); ?><!--)</button>-->
								<?php endif; ?>
							</div>
							<?php if ($this->config->get('template_params.notify_users')) : ?>
								<label class="checkbox pull-right small ccomment-notify">
									<input type="checkbox" value="1" name="jform[notify]ƒ"
										   v-on:click="notify = !notify"/>
									<span class="muted">
										<?php echo JText::_('COM_COMMENT_NOTIFY_FOLLOW_UP_EMAILS') ?>
									</span>
								</label>
							<?php endif; ?>


						</div>
					</div>
					<?php if (!$user->get('id')) : ?>
						<div v-show="display"
							 class="row-fluid ccomment-user-info offset<?php echo ($formAvatar) ? 1 : 0; ?>
					<?php echo ($formAvatar) ? 'span11' : ''; ?>">
							<div class="span6">
								<input name="jform[name]"
									   class="ccomment-name span12 no-margin <?php echo $this->config->get('template_params.required_user', 0) ? 'required nonEmpty' : ''; ?>"
									   type='text'
									   v-bind:value="getName"
									   v-on:input="updateDefaultName"
										<?php echo $this->config->get('template_params.required_user', 0) ? 'required="required"' : ''; ?>
									   placeholder="<?php echo JText::_('COM_COMMENT_ENTER_YOUR_NAME'); ?><?php echo $this->config->get('template_params.required_user', 0) ? '*' : ''; ?>"
									   tabindex="2"
									<?php if ($user->id) : ?> disabled="disabled" <?php endif; ?>
								/>
								<span class="help-block pull-right small muted">
								<?php echo JText::_('COM_COMMENT_DISPLAYED_NEXT_TO_YOUR_COMMENTS'); ?>
							</span>
							</div>

							<?php if ($this->config->get('template_params.notify_users')) : ?>
								<div class="span6">
									<input name='jform[email]'
										   class="ccomment-email span12 no-margin <?php echo $this->config->get('template_params.required_email', 0) ? 'required nonEmpty' : ''; ?>"
										   type='text'
										   v-bind:value='getEmail'
										   v-on:input="updateUserEmail"
										   placeholder="<?php echo JText::_('COM_COMMENT_ENTER_YOUR_EMAIL'); ?><?php echo $this->config->get('template_params.required_email', 0) ? '*' : ''; ?>"
										<?php echo $this->config->get('template_params.required_email', 0) ? 'required="required"' : ''; ?>
										   tabindex="3"
										<?php if ($user->id) : ?> disabled="disabled" <?php endif; ?>
									/>
									<p class="help-block small pull-right muted">
										<?php echo JText::_('COM_COMMENT_NOT_DISPLAYED_PUBLICLY'); ?>
										<?php if ($this->config->get('integrations.gravatar')) : ?>
											<span class='gravatar'>
											<?php echo JText::_('COM_COMMENT_GRAVATAR_ENABLED'); ?>
										</span>
										<?php endif; ?>
									</p>
								</div>
							<?php endif; ?>
						</div>
					<?php else : ?>

					<?php endif; ?>


					<?php if ($this->customfieldsForm->getGroup('customfields')) : ?>
						<div class="row-fluid offset1 span11 ccomment-actions" v-show="active">
							<strong><?php echo JText::_('COM_COMMENT_CUSTOM_FIELDS'); ?></strong>
							<?php foreach ($this->customfieldsForm->getGroup('customfields') as $custom) : ?>
								<div class="ccomment-customfields-group">
									<?php echo $this->customfieldsForm->getLabel($custom->fieldname, 'customfields'); ?>
									<?php echo $this->customfieldsForm->getInput($custom->fieldname, 'customfields'); ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ($this->config->get('security.captcha') && ccommentHelperSecurity::groupHasAccess($user->getAuthorisedGroups(), $this->config->get('security.captcha_usertypes'))) : ?>
						<div class="<?php echo ($formAvatar) ? 'offset1 span11' : 'row-fluid'; ?> ccomment-actions"
							 v-show="active">
							<div class='muted small'>
								<?php if ($this->config->get('security.captcha_type') == "recaptcha") : ?>
									<div class="ccomment-recaptcha-placeholder">

									</div>
								<?php else : ?>
									<div>
										<?php echo JText::_('COM_COMMENT_FORMVALIDATE_CAPTCHATXT'); ?>
									</div>
									<div class="ccomment-captcha">
										<?php echo ccommentHelperCaptcha::insertCaptcha('jform[security_refid]', $this->config->get('security.captcha_type'), $this->config->get('security.recaptcha_public_key')); ?>
										<input type='text' name='jform[security_try]' id='security_try' maxlength='5'
											   tabindex='4' class='ccomment-captcha-input required'/>
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>


					<div class="row-fluid ccomment-actions" v-show="active">
						<?php if (!ccommentHelperSecurity::autopublish($this->config)) : ?>
							<div class="pull-left muted small ccomment-undergo-moderation offset<?php echo ($formAvatar) ? 1 : 0; ?>">
								<?php echo JText::_('COM_COMMENT_COMMENTS_UNDERGO_MODERATION'); ?>
							</div>
						<?php endif; ?>
						<div class="pull-right">
							<button v-on:click="reset()"
									type="button"
									class="btn ccomment-cancel">
								<?php echo JText::_('COM_COMMENT_CANCEL'); ?></button>
							<button type="submit" class='btn btn-primary ccomment-send'
									tabindex="7"
									name='bsend'>
								<span v-if="isSending">
									<?php echo JText::_('COM_COMMENT_SAVING'); ?>
								</span>
								<span v-else>
									<?php echo Jtext::_('COM_COMMENT_SENDFORM'); ?>
								</span>
							</button>
						</div>
					</div>
				</div>

				<input type="hidden" name="jform[contentid]" v-bind:value="itemConfig.contentid"/>
				<input type="hidden" name="jform[component]" v-bind:value="itemConfig.component"/>
				<input type="hidden" name="jform[page]" v-bind:value="page"/>
				<slot name="parent-id"></slot>
			</form>
		</script>

	<?php else : ?>
		<div class="ccomment-not-authorised">
			<h5><?php echo JText::_('COM_COMMENT_NOT_AUTHORISED_TO_POST_COMMENTS') ?></h5>

			<p class="muted small">
				<?php if (!$this->config->get('security.auto_publish')) : ?>
					<?php echo JText::_('COM_COMMENT_COMMENTS_UNDERGO_MODERATION'); ?>
				<?php endif; ?>
			</p>
		</div>
	<?php endif; ?>
<?php else : ?>
	<div class="ccomment-comments-disabled alert alert-info">
		<?php echo JText::_('COM_COMMENT_DISABLEADDITIONALCOMMENTS') ?>
	</div>
<?php endif; ?>




<?php echo $this->loadTemplate('fileupload_jstemplate'); ?>
