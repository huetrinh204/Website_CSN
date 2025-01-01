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

// Output the js localisation
ccommentHelperUtils::getJsLocalization();
$document  = JFactory::getDocument();
$component = $this->component;
$count     = $this->count;
$id        = $this->contentId;
$user      = JFactory::getUser();
$modules   = JModuleHelper::getModules('ccomment-top');

$htmlId = 'ccomment-' . str_replace('com_', '', $component) . '-' . $id;
$config = $this->config;
$avatar = '';

if ($config->get('template_params.form_avatar'))
{
	if ($user->guest)
	{
		$avatar = ccommentHelperAvatars::noAvatar();
	}
	else
	{
		$avatar = ccommentHelperAvatars::getUserAvatar($user->id, $config->get('integrations.support_avatars'));

		if ($avatar == '' && $config->get('integrations.gravatar'))
		{
			$avatar = ccommentHelperAvatars::getUserGravatar($user->email);
		}

		// If we still don't have an avatar here, let us load the no avatar image
		if ($avatar == '')
		{
			$avatar = ccommentHelperAvatars::noAvatar();
		}
	}
}

$userInfo = array(
	'loggedin' => !$user->guest,
	'avatar'   => $avatar
);

$pageItem = array('contentid' => (int) $id, 'component' => $component, 'count' => (int) $count);

JHtml::_('behavior.formvalidator');

// Keep the session alive - the user is writing comments!!!
JHtml::_('behavior.keepalive');

if ($config->get('template_params.emulate_bootstrap', 1))
{
	JHtml::stylesheet('media/com_comment/templates/default/css/bootstrap.css');
}

JHtml::stylesheet('media/com_comment/templates/default/css/style.css');

JHtml::_('jquery.framework');

JHtml::stylesheet('media/com_comment/js/vendor/sceditor/themes/compojoom.css');

JHtml::script('media/lib_compojoom/third/polyfills/assign.js');
JHtml::script('media/lib_compojoom/third/polyfills/promise.js');

JHTML::stylesheet('media/lib_compojoom/third/font-awesome/css/font-awesome.min.css');
JHtml::stylesheet('media/lib_compojoom/third/galleria/themes/compojoom/galleria.compojoom.css');

JHtml::stylesheet('media/lib_compojoom/css/jquery.fileupload.css');
JHtml::stylesheet('media/lib_compojoom/css/jquery.fileupload-ui.css');
JHtml::stylesheet('media/lib_compojoom/css/fields/fileupload.css');

if (!$config->get('layout.support_ubb', false))
{
	JHtml::script('media/com_comment/js/vendor/autosize/autosize.min.js');
}

// Add the recaptcha js if we need it
if ($this->config->get('security.captcha') && $this->config->get('security.captcha_type') == 'recaptcha')
{
	CompojoomHtml::recaptcha();
}
?>

<?php echo $this->loadTemplate('pagination'); ?>
	<script type="text/x-template" id="ccomment-avatar">
		<div class="ccomment-avatar">
			<a v-if="profileLink" v-bind:href="profileLink">
				<img v-bind:src="avatar"/>
			</a>
			<img v-else v-bind:src="avatar"/>
		</div>
	</script>

	<script type="text/x-template" id="ccomment-user-name">
		<a v-if="profileLink" :href="profileLink">
			<span class="ccomment-author">{{name}}</span>
		</a>
		<span v-else class="ccomment-author">{{name}}</span>
	</script>

	<script type="text/x-template" id="ccomment-created">
		<a :href="'#ccomment-comment=' + id" class="muted ccomment-created">
			{{date}}
		</a>
	</script>

	<div id="ccomment-token" style="display:none;">
		<?php echo JHtml::_('form.token'); ?>
	</div>

	<?php echo $this->loadTemplate('comment'); ?>
	<?php echo $this->loadTemplate('form'); ?>
	<?php echo $this->loadTemplate('menu'); ?>

	<!-- the ccomment root element -->
	<div class="ccomment" id="ccomment">

		<ccomment-menu></ccomment-menu>

		<?php if((int) $config->get('template_params.form_position', 0) === 1): ?>
			<ccomment-form></ccomment-form>
		<?php endif; ?>

		<?php if ((int) $config->get('template_params.pagination_position') === 2 || (int) $config->get('template_params.pagination_position', 0) === 1): ?>
			<pagination v-if="pagination.last_page > 1" :pagination="pagination" :callback="loadData" :options="paginationOptions"></pagination>
		<?php endif; ?>

		<ul class="ccomment-comments-list">
			<ccomment-comment v-for="item in comments"
							  v-if="item.parentid === -1"
							  class="item"
							  v-bind:key="item.id"
							  v-bind:model="item">
			</ccomment-comment>
		</ul>

		<?php if ((int) $config->get('template_params.pagination_position') === 0 || (int) $config->get('template_params.pagination_position', 0) === 2): ?>
			<pagination v-if="pagination.last_page > 1" :pagination="pagination" :callback="loadData" :options="paginationOptions"></pagination>
		<?php endif; ?>

		<?php if((int) $config->get('template_params.form_position', 0) === 0): ?>
			<ccomment-form></ccomment-form>
		<?php endif; ?>

		<?php echo $this->loadTemplate('footer'); ?>
	</div>

	<script type="text/javascript">
		window.compojoom = compojoom = window.compojoom || {};
		compojoom.ccomment = {
			user: <?php echo json_encode($userInfo) ?>,
			item: <?php echo json_encode($pageItem) ?>,
			config: <?php echo json_encode(CcommentHelperUtils::getJSConfig($component)) ?>
		};
	</script>

<?php
CompojoomHtml::script(
	array(
		'media/lib_compojoom/third/vue/vue-prod.js',
		'media/lib_compojoom/third/vuex/vuex.min.js',
		'media/lib_compojoom/third/js-cookie/js.cookie.min.js',
		'media/lib_compojoom/third/galleria/galleria.min.js',
		'media/lib_compojoom/third/galleria/themes/compojoom/galleria.compojoom.min.js',
		'media/lib_compojoom/third/jquery-file-upload/jquery.ui.widget.js',
		'media/lib_compojoom/third/jquery-file-upload/tmpl.min.js',
		'media/lib_compojoom/third/jquery-file-upload/canvas-to-blob.min.js',
		'media/lib_compojoom/third/jquery-file-upload/load-image.all.min.js',
		'media/lib_compojoom/third/jquery-file-upload/jquery.fileupload.min.js',
		'media/lib_compojoom/third/jquery-file-upload/jquery.fileupload-process.min.js',
		'media/lib_compojoom/third/jquery-file-upload/jquery.fileupload-image.min.js',
		'media/lib_compojoom/third/jquery-file-upload/jquery.fileupload-validate.min.js',
		'media/lib_compojoom/third/jquery-file-upload/jquery.fileupload-ui.min.js',
		'media/com_comment/js/vendor/sceditor/jquery.sceditor.bbcode.min.js',
		'media/com_comment/js/vendor/sceditor/formats/bbcode.min.js',
		'media/com_comment/js/vendor/sceditor/plugins/autoyoutube.min.js',
		'media/com_comment/js/vendor/sceditor/plugins/plaintext.min.js',
		'media/com_comment/js/vendor/JavaScript-MD5/md5.min.js',
		'media/com_comment/js/v6/store/index.min.js',
		'media/com_comment/js/v6/components/username.min.js',
		'media/com_comment/js/v6/components/created.min.js',
		'media/com_comment/js/v6/components/customfields.min.js',
		'media/com_comment/js/v6/components/pagination.min.js',
		'media/com_comment/js/v6/components/avatar.min.js',
		'media/com_comment/js/v6/components/menu.min.js',
		'media/com_comment/js/v6/components/comment.min.js',
		'media/com_comment/js/v6/components/form.min.js',
		'media/com_comment/js/v6/main.min.js',
	),
	'media/com_comment/cache',
	$config->get('template_params.minify_scripts', true),
	true
);
