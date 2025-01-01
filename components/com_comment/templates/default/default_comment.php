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

$avatars  = $this->config->get('integrations.support_avatars') || $this->config->get('integrations.gravatar');
$profiles = $this->config->get('integrations.support_profiles');

$reply = false;
$canPost = ccommentHelperSecurity::canPost($this->config);

if($canPost) {
    if ((int) $this->config->get('layout.tree') === 1)
    {
        $reply = true;
    }
    elseif ((int) $this->config->get('layout.tree') === 2)
    {
        if (ccommentHelperSecurity::isModerator($this->contentId) && $this->allowedToPost)
        {
            $reply = true;
        }
    }
}
?>

<script type="text/x-template" id="ccomment-customfields">
	<div class="ccomment-customfields" v-if="customfields">
		<strong><?php echo JText::_('COM_COMMENT_CUSTOM_FIELDS'); ?></strong>
		<dl class="dl-horizontal">
			<template v-for="customfield in customfields">
				<dt>{{customfield.title}}</dt>
				<dd>{{customfield.value}}</dd>
			</template>
		</dl>
	</div>
</script>

<script type="text/x-template" id="ccomment-template">
	<li v-bind:class="model.class+' ccomment-level-'+model.level">
		<div class="ccomment-comment-content" v-bind:id="'ccomment-comment-'+model.id">
			<div class="ccomment-data">
				<?php if ($avatars) : ?>
					<ccomment-avatar
							v-bind:avatar="model.avatar"
							v-bind:profileLink="model.profileLink"/>
				<?php endif; ?>
				<div class="ccomment-content">
					<div class="ccomment-meta">
						<ccomment-user-name v-bind:name="model.name" v-bind:profileLink="model.profileLink"></ccomment-user-name>
						<ccomment-created v-bind:date="model.date" v-bind:id="model.id"></ccomment-created>
					</div>

					<div v-html="model.comment">
					</div>


					<div v-if="model.galleria" class="js-ccomment-galleria galleria ccomment-galleria"></div>

					<ccomment-customfields v-bind:customfields="model.customfields"></ccomment-customfields>
					<div class="ccomment-actions">
						<?php if ($this->config->get('layout.voting_visible')) : ?>
							<span class="muted">
					{{model.votes}}
					<i class="ccomment-thumbs-up ccomment-voting" v-on:click="vote(+1, model.id)"></i>
					<i class="ccomment-thumbs-down ccomment-voting" v-on:click="vote(-1, model.id)"></i>
				</span>
						<?php endif; ?>

						<?php if ($this->allowedToPost) : ?>
							<button class="btn btn-small ccomment-quote btn-link" v-on:click="quote(model.id)">
								<?php echo JText::_('COM_COMMENT_QUOTE'); ?>
							</button>
						<?php endif; ?>

						<?php if ($reply) : ?>
							<button v-if="showReply" v-on:click="reply = !reply" class="btn btn-small ccomment-reply btn-link">
								<?php echo JText::_('COM_COMMENT_REPLY', true); ?>
							</button>
						<?php endif; ?>

						<div class="pull-right ccomment-moderation">
							<button v-if="model.commentModerator" class="btn btn-mini btn-ccomment-edit" v-on:click="edit(model.id)">
								<?php echo JText::_('COM_COMMENT_EDIT'); ?>
							</button>

							<?php if (ccommentHelperSecurity::isModerator($this->contentId)) : ?>
								<button v-if="model.published === 1" class="btn btn-mini btn-ccomment-unpublish btn-ccomment-change-state" v-on:click="changeState(0, model.id)">
									<?php echo JText::_('COM_COMMENT_UNPUBLISH'); ?>
								</button>

								<button class="btn btn-mini btn-ccomment-publish btn-ccomment-change-state" v-on:click="changeState(1, model.id)" v-if="model.published === 0">
									<?php echo JText::_('COM_COMMENT_PUBLISH'); ?>
								</button>

								<button class="btn btn-mini btn-ccomment-delete btn-ccomment-change-state" v-on:click="changeState(-1, model.id)">
									<?php echo JText::_('COM_COMMENT_DELETE'); ?></button>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>


			<keep-alive>
				<ccomment-form v-if="reply" :ref="'form-'+model.id" v-bind:focus="true">
					<input slot="parent-id" name="jform[parentid]" type="hidden" v-bind:value="model.id"/>
				</ccomment-form>
			</keep-alive>
		</div>

		<ul v-if="hasChildren">
			<ccomment-comment class="item" v-for="model in getChild()" v-bind:key="model.id" v-bind:model="model">
			</ccomment-comment>
		</ul>
	</li>

</script>
