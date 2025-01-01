<?php defined('_JEXEC') or die(); ?>

=====================================================================
CComment 6.1.14 - Released 24-Nov-2024
=====================================================================
# remove shebang (#!) from the url, use just the hash (#) to scroll to the comments

=====================================================================
CComment 6.1.13 - Released 20-Sep-2024
=====================================================================
# If sendMail in the queue fails, don't crash the whole queue, but treat as success & continue with the next mail

=====================================================================
CComment 6.1.12 - Released 20-Oct-2023
=====================================================================
# Another try at fixing a crash on Joomla 5

=====================================================================
CComment 6.1.11 - Released 19-Oct-2023
=====================================================================
# Fix crash on Joomla 5

=====================================================================
CComment 6.1.10 - Released 21-Feb-2023
=====================================================================
- Fix deprecated function call (JArrayHelper) in Joomla 4

=====================================================================
CComment 6.1.9 - Released 06-Feb-2023
=====================================================================
- Fix wrong db structure for old installations upgraded to the latest version

=====================================================================
CComment 6.1.8 - Released 10-Jan-2023
=====================================================================
- Fix recaptcha causing a JS error when not enabled for a user group

=====================================================================
CComment 6.1.7 - Released 20-Feb-2022
=====================================================================
- fix crash when trying to show CB avatars on joomla 4.x

=====================================================================
CComment 6.1.6 - Released 20-Feb-2022
=====================================================================
- fix failing installation on some joomla 4 instances due to strict mysql

=====================================================================
CComment 6.1.5 - Released 21-Dec-2021
=====================================================================
- behavior tooltip was causing a crash in the comment module on joomla 4
- incorrect redirect in comment module - thanks for the fix to @iona

=====================================================================
CComment 6.1.4 - Released 26-Oct-2021
=====================================================================
# Fix crash on Joomla 4 when editing global settings
~ Improve discuss import

=====================================================================
CComment 6.1.3 - Released 19-Sept-2021
=====================================================================
# fix crash when editing a comment on PHP 8

=====================================================================
CComment 6.1.2 - Released 31-Aug-2021
=====================================================================
# quote styling wasn't properly applied to comments

=====================================================================
CComment 6.1.1 - Released 29-Aug-2021
=====================================================================
+ Added quote styling in comments
# Fixed uninstallation notices
# Fixed wrong upvote/downvote count

=====================================================================
CComment 6.1.0 - Released 22-Aug-2021
=====================================================================
+ CComment is now Joomla 4 compatible!

=====================================================================
CComment 6.0.14 - Released 12-May-2021
=====================================================================
# Activity chart on dashboard was not rendering properly

=====================================================================
CComment 6.0.13 - Released 8-March-2021
=====================================================================
# Fix failing new installations

=====================================================================
CComment 6.0.12 - Released 4-March-2021
=====================================================================
# on Multilingual sites requests to vote, edit, quote were redirected with 301
+ it's now possible for users to change their own votes

=====================================================================
CComment 6.0.11 - Released 21-January-2021
=====================================================================
# comment submission time was not properly translated on multilingual websites
# backend dashboard was missing the correct extension title

=====================================================================
CComment 6.0.10 - Released 14-January-2021
=====================================================================
# in multilingual setup the comment needs moderation message was not properly translated

=====================================================================
CComment 6.0.8 - Released 25-May-2020
=====================================================================
# Form gravatar image was being loaded over http and not https

=====================================================================
CComment 6.0.8 - Released 17-Feb-2020
=====================================================================
# Add comment on top when sorting by newest first
~ Updated jceeditor to version 2.1.3
~ Updated vuejs library to version 2.6.11
~ Updated vuex library to version 3.1.2

=====================================================================
CComment 6.0.7 - Released 23-Mai-2019
=====================================================================
# hide reply button when the user is not able to post
# not able to press the edit button a second time
# uploading an image was not working correctly in some situations

=====================================================================
CComment 6.0.5 - Released 20-Dec-2018
=====================================================================
# fix notices on php 7.3
# navigating to Easyblog comments was not working

=====================================================================
CComment 6.0.4 - Released 17-Sep-2018
=====================================================================
+ use vue.js in production mode
# navigating to Easyblog comments was not working

=====================================================================
CComment 6.0.3 - Released 10-April-2018
=====================================================================
# enforce name and email when this is set in the settings
# in some situations the edit button on a comment was shown to the wrong user

=====================================================================
CComment 6.0.2 - Released 9-April-2018
=====================================================================
# when comments were moderated no message was displayed to the user that the comment was approved and is awaiting moderation

=====================================================================
CComment 6.0.1 - Released 7-April-2018
=====================================================================
# default captcha was not validating properly
# recaptcha was not validating properly

=====================================================================
CComment 6.0.0 - Released 4-April-2018
=====================================================================
# the reply button now respect the nesting level
+ added a onAfterPrepareComments event allowing you to change the json returned for the comment list
+ added a onAfterBuildQuery event that can be intercepted to modify the query fetching the comments from the db

=====================================================================
CComment 6.0.0 alpha 1 - Released 26-April-2017
=====================================================================
+ new BBCode editor (SCEditor)
+ image upload with drag and drop
+ new template build on vuejs
- removed mootools dependeny in frontend template

=====================================================================
CComment 5.4.1 - Released 4-February-2017
=====================================================================
~ changed the ip field to varchar(45) in order for it to sae ipv6 addresses
# custom fields values were not properly shown when editing the comment in the backend

=====================================================================
CComment 5.4.0 - Released 29-January-2017
=====================================================================
+ added Reditem plugin
# disqus has ids that don't fit in the int columns that we have. Changed importid and parentimportid to bigint
# fixed import bug with disqus comments
+ add the title of the article in the search results
# wrong text in the search plugin
# jDownloads plugin now works with version bigger than JDownloads 3.2
# configuration notes couldn't be saved

=====================================================================
CComment 5.3.8 - Released 10-November-2016
=====================================================================
# quoting a comment was not showing the user's name for non-registered users. ([quoted=null]...[/quoted])

=====================================================================
CComment 5.3.7 - Released 31-October-2016
=====================================================================
# nested comments were not shown on Joomla 2.5 (yes, we no longer support it, but we had to fix this)

=====================================================================
CComment 5.3.6 - Released 20-October-2016
=====================================================================
# the url bbcode was erroneously adding http: when we had https urls
# the BBcode class was throwing notices on PHP7
# routing issue on multilingual sites on an nginx server
# fixes to the jdownloads plugin
+ djclassified plugin
+ new view for the notification queue
# imported comments had wrong parent id
# when quoting the quoted user name is not respecting the layout use name setting
# when notify user was set to no, editing a comment was creating a new comment
# recaptcha incompatibility with matukio
+ when changing pages scroll always to the top of the comments
+ the categories for com_content are now shown nested in the settings

=====================================================================
CComment 5.3.5 - Released 17-December-2015
=====================================================================
# the hotspots plugin is now compatible with Hotspots 5.2.0
+ ccomment can now install on installations with PDO mysql driver
# fixed a bug in the disqus import
# the event titles were not properly fetched from matukio
# notify "content creator moderator" was not working in matukio
- google has deprecated the ajax crawling implementation as they can craw pages as normal browsers now. Removed our implementation of it.
# editing a comment and selecting the option to be notified was throwing an error for missing e-mail
# user JEVHelper::getItemid() to determine the correct Itemid for jevents entries
# the jevents plugin now works with the latest jevents version 3.4
# [quote=undefined] when liking/disliking a comment after quoting
~ use CB code to get the correct plugin path
# wrong path to css file for the CB usercomment plugin
# wrong query on multilingual vm installs - thanks Max!
# the links to easyblog items were wrong

=====================================================================
CComment 5.3.4 - Released 26-August-2015
=====================================================================
+ added option to control the length of the comment posted in the activity stream in Jomsocial
~ making some modifications to the default theme, so that it can better work on most templates
+ the comments in the Jomsocial stream can now be shown with name or username
# don't load the tooltips library when the option is not enabled in the settings
# content creator moderator was not working for com_hotspost
# moved the ZOO plugin to the system folder as it no longer worked as content plugin
~ the search in backend now looks also for the commenter's name & email
# delete icon in backend was not working
~ we now send a notification to the user when a moderator approves the comment through the link in the mail
# if the user selects to be notified of further e-mails validate the e-mail he has provided
# updating the CB plugins to be compatible with CB 2.x
# error in the docman integration when "content creator moderator" was set to yes
# the link to hwdmediashare item was wrong
# add a non-empty check for name and e-mail
# ccomment was not working properly on multilingual VM installs

=====================================================================
CComment 5.3.3 - Released 27-April-2015
=====================================================================
# docman plugin couldn't be updated on new install
+ updated recaptcha to use the new "I'm human" recaptcha code
# fixed reordering issue with custom fields
# typo in the komento import script caused the import not to function - thanks to Jordan Weinstein for reporting and fixing!
# wrong comment count when a new comment is added
# when comments were sorted by "new entries first" sometimes nested comments were missing on page refresh
# typo in the update stream
# to ease some people added rel="nofollow" to our powered by link (even though the link is not indexed by search engines...)
# the "custom fields" text in the form was hardcoded
+ option to gather anonymous stats about the environment & configuration of the extension
+ added JED review request on the dashboard

=====================================================================
CComment 5.3.2 - Released 2-February-2015
=====================================================================
# changelog in the PRO version was wrong
# unable to save comments in the backend
+ the docman plugin has been updated to support version 2 of the component
+ once the comment is submitted close the comment form completely
# the hotspots plugin now supports hotspots 5
# the ordering of the latest submitted comment was not correct

=====================================================================
CComment 5.3.0 - Released 5-January-2015
=====================================================================
# EasySocial avatars now use the path specified in EasySocial
# don't show a custom field when it isn't filled out
# custom fields were rendered several times depending on how many fields there are
+ adding a checkbox custom field
+ added EasySocial avatar support
+ added EasySocial profile support
# cb comment wall plugin was not respecting the use_name setting
+ developer feature: onPrepareConfig event allowing you to override the config on initialization
+ added option to sort on best comments or worst comments first (based on the votes)
# cb installation was showing an error when no language file was present in the package
# wrong date format for the cb user comments plugin
+ custom fields support

=====================================================================
CComment 5.2.2 - Released 19-November-2014
=====================================================================
# wrong link generated by the matukio plugin
# no longer display error loading feed on the dashboard
# wrong link to rss feed in the dashboard
# the installer plugin was missing from the PRO package
# wrong url for ajax requests on some installations
# when editing a comment, the wrong gravatar was fetched

=====================================================================
CComment 5.2.1 - Released 22-October-2014
=====================================================================
# comment system was no longer loading when user wrote a comment and provided an email

=====================================================================
CComment 5.2 - Released 22-October-2014
=====================================================================
# the installer plugin was missing for joomla 2.5
+ added an option to minify the javascript files
+ added an option to select the user groups that are allowed to post without moderation
+ added an option for comment maximum depth level
- no longer uses require.js
# on multilingual sites we no longer need 2 requests to fetch the comments
- removed features from core package: "disable comments in", "content creator moderator", "use names", "emoticons turn off"

=====================================================================
CComment 5.1 - Released 09-October-2014
=====================================================================
^ using the new compojoom installer library & database schema
^ backend now uses lanceng
+ use joomla's update manager to update the component
- removed live update
# gravatar can no longer load default images through https. We'll use the standard mysteryman from gravatar for now
# break words in the comments in the module if they are too long

=====================================================================
CComment 5.0.8 - Released 04-September-2014
=====================================================================
# hikashop listings were always pointing to the same page - thanks to Nicolas from hikashop for fixing this
# support pictures option was not taken into account for ubb code
# hitting enter in the name or e-mail field was just hidding them
# fixed sql error caused by the latest comments module when using the mostcommented option
# module was showing wrong author name for edited comment
# wrong date format in the backend
# the simple smiley set had wrong icon names
# easyblog not showing the comment count
+ adding a count comment function that only returns an int - some components need only this and don't use our "write comment (x comments)" readmore
=====================================================================
CComment 5.0.7 - Released 16-April-2014
=====================================================================
# the textarea was growing on each character with some templates and always on ff29 - textarea should have box-sizing set to border-box
# "Content creator moderator" - couldn't change the state of the comments (publish/unpublish)
# fixing an issue with the comments in zoo. When using the related element the comment form was shown multiple times.
# fixed - the module was outputting html in the tooltips
# fixed - the language strings were not loaded in the module
# no longer send emails to blocked moderators
# the avatar for logged in users was missing from the form
~ powered by cannot be changed from the settings for the core version anymore
# fixing an issue with the compojoom namespace
=====================================================================
CComment 5.0.6 - Released 10-February-2014
=====================================================================
# wrong label desc for the date syntax in the settings
# moving form validation bevor the requirejs call, because of incompatible punycode j3.2.2.....
=====================================================================
CComment 5.0.5 - Released 07-February-2014
=====================================================================
# Updated requireJS in order to fix a but introduced with j3.2.2
+ adding a plugin for DJClassifieds
+ adding a plugin for DJCatalog2
# properly format an url that is a mail address
=====================================================================
CComment 5.0.4 - Released 03-February-2014
=====================================================================
# wrong tabindex used for the form fields
# permissions for the backend access couldn't be modified
# wrong captcha/recaptcha position in the template
# notify users of new comments only when their comments are published in the first place
# blocking IP was not working properly
# load recaptcha through the https protocol
# gravatar was not using the default avatar
+ added ccomment-top module position
# make sure that people can't enter another value for font size
# make sure that big images don't screw the layout
=====================================================================
CComment 5.0.3 - Released 19-November-2013
=====================================================================
# going around the joomla cache, by putting the initialization code in a file (ccomment should be able to work with cache on)
# on some installations the k2 plugin was causing the backend of k2 not to work. Now only using the plugin when we are in the frontend
+ the AUP plugin now respects the rules assignement method
# 10.0.8.35 is detected as a url. Disabled this auto url detection.
# the notify flag was set on all comments no matter if the user had selected it or not
=====================================================================
CComment 5.0.2 - Released 03-October-2013
=====================================================================
# don't show the replay & quote buttons if user is not allowed to post
# No email was sent to moderators when autopublish comment was turned on
+ added cobalt plugin
=====================================================================
CComment 5.0.1 - Released 03-September-2013
=====================================================================
# the general import script had an error in it
# updated CB user comments plugin
# not all CB plugin were installed with the package
# added new jomsocial plugin to render the activity stream (works on jomsocial > 2.8)
# youtube video now respects the url scheme (http/https)
# some language strings were missing from the language files
# getELement (...)' is null or not an objet on line 250 character 5 on media/com_comment/us/views/comments-outer.js
# placeholder not showing on IE
+ added placeholder support for browsers that don't support placeholders
=====================================================================
CComment 5.0.0 - Released 13-August-2013
=====================================================================
+ added smart search plugin
+ added search plugin
# the latest comment module was not part of the PRO package
# the k2 plugin was causing JForm::getInstance not found on some installations in k2 items
# anonymous was not translatable in the javascript files
# joomla 3.1.4 has a bug and is unable to load the JHtml class if it is written in any other way than JHtml (JHTML, jhtml was invalid)
+ added option in the template to auto show the name and email fields
# the comments module was showing comments that were unpublished
+ added gpstools plugin
+ added communityquotes plugin
# fixed fatal error when the module was enabled on kunena pages
# fixed an issue with comment crawling
# nested comments - moderators only was not working as expected
# thumbs move down/up - thanks Josh!
+ AUP improvements - showing a message about the activity the user performed
# HWD plugin was not installed properly
# added margin to .ccomment-replies for templates that set the margin for uls to 0
# comments were not showing up in HWDMediashare
+ added AUP integration - user points are now assigned on comment & vote
# possible fix for - require.js was not loaded
# fixing bug with import from jcomments
# comment needs moderation was not visible when we were replying to nested comment
# unpublished comments were shown when they were nested
+ added plugin for Communitypolls
# groupHasAccess was producing a notice if the second argument was not an array and that way breaking the component
# the cancel button in the template was not translated
+ added redshop plugin
# using overrides/new template located in your_template/html/templates was causing an error
# could not delete comments on some servers
# no message was output when the user comment needed moderation
+ added support for kunena profiles
+ added support for kunena avatars
+ added Zoo plugin
# optimised performance when working on the mail queue
+ added back the docman plugin for docman 1.6.4
# html errors in the template
# the installed version number was not visible in the backend
# too long words were not wrapped...
# fixing problems with the resizing of the textarea
+ added plugin for dpcalendar
+ added option to add the comments using the onContentPrepare event for com_content
# fixes issue with update from 4.2.1
# fixes issues with content tag {ccomment on|off|closed}
# captcha issues with selected group on j2.5
# update from 4.2.1 was not properly executed
# captcha usergroup selection was not having any effect
# k2 plugin was missing from the pro package
+ added plugin for hikashop
# comment system was not working properly when cache was on
# "use names" option was not working properly
# k2 plugin was not installed with the core version
# when the article is closed for further comments change the "write comment(x comments)" button to just "x comments" in list view
# joscomment plugin recognises {ccomment on|off|closed} tag
# comments were not loading on IE8
# k2 plugin was not installed in the correct plugin group
# uninstallation was not removing some plugins
# comments were not displaying when the user was not authorised to post
+ emails are now sent either on page load or per cron job
+ added plugin for com_matukio (Matukio - Event management)
# url in notification mail is wrong for https sites
+ added com_joomgallery plugin (check docs for more info)
+ added com_jdownloads plugin (check docs for more info)
# write comment was not shown on category blog view for com_content
# the link to docimport didn't have an itemId
# wrong username was shown in comment list in the backend
# textarea was not expanding when the quote was bigger than the textarea
# module was not able to show the comments from multiple components
# use the setLimit for comments in the module properly by respecting the bbcode
# css fixes for embeded youtube videos
# fixing problem when updating from 4.2.1 & and a language translation is installed
# quote & edit were not working
# some buttons were not clickable on the Ipad
# fixed issue 67 smilies break on vote
+ implemented ajax crawling according to google's specification. Now comments should be indexed by search engines
# wrong license tag in few plugins
# missing JEXEC statement in few files
# posting comment as logged in user was not working
# fixing a problem with reply to a comment
# now scrolling to the comments only if we have the correct hash
# settings were not correctly saved after the install
# bug fiexes for IE6
# bug fixes for IE8
+ added plugin for Docimport
# fixed a problem with joomla's SEF :(
+ added HWDMediashare plugin
- removed docman integration
- removed hwdvideo and hwdphoto integration
# updated LiveUpdate library
+ added jcomments, komento & disqus import
+ added fb like on dashboard for compojoom.com
+ added dashboard
+ added an indicator when loading comments
+ making the form a little more user friendly on submit
+ output error messages to the user submitting a comment & validate form input
# updated virtuemart plugin
# updated ninjamonial plugin
# updated the jphoto plugin
# updated jevents plugin
- removed com_eventlist plugin as the extension is no longer supported
+ cb plugins are now installed during the ccomment installation
# updating the com_comprofile plugin and adding the ccomment wall plugin for cb
# updated adsmanager plugin
# updated the easyblog plugin
+ adding the k2 and Hotpsots plugins to ccomment5 Core
# updating the hotspots plugin
# updating the phocadownload plugin
# could not delete settings in backend
- removing JomsocialGroup & JomsocialGroupDiscussion plugins
# updated the jomsocial wall plugin
# wrong message shown when comments were set to autopublish
# fixing a warning when no moderator group was selected
# one was not able to create new plugin settings
+ add support for like & comment on the jomsocial activity stream
# updated our jomsocial plugin
+ adding the bare minimum of bootstrap CSS so that the template can be displayed properly on sites that don't use bootstrap
# css class was not properly added to comment
# display component name when editing/creating a new stting
# fixing the backend CSS on joomla 2.5 (it doesn't come with bootsrap....)
+ uninstall now works properly
# we are no able to properly update from 4.2.1
+ we are now able to select which user group has the right to post comments
~ updated the K2 plugin
- removed the stringparser library as it is no longer used
# fixes for joomla 2.5
# show pagination only if enabled
# the DS constant is not available in j3.0
# selecting all comments to delete was not working in backend
# fixed strict standards warning when using jomsocial avatars
- removed legacy install/uninstall procedures
- removed plugin for sobi2 as the extension is not supported on joomla 2.5
- removed plugin for seyret as the extension is no longer supported
- removed plugin for puarcade as the extension is no longer supported
- removed plugin for jomtube as the extension is no longer supported
- removed plugin for mmsblog as the extension is no longer supported
- removed plugin for myblog as the extension is no longer supported

=====================================================================
CComment 5.0.0 alpha - Released 15-May-2013
=====================================================================
~ simplified backend
+ joomla 3 support
+ Closely follows Joomla MVC conventions
+ new template engine
+ new default template based on bootstrap markup
+ new bbcode engine supporting video, automatic link, code highlighting
+ author of article can be moderator
+ new email templates
+ one click publish/unpublish comment from email
+ one click unsubscribe from future notifications of new comments
- removed legacy code - functions, templates (40k lines of code)


=====================================================================
LEGEND
=====================================================================
! Note
+ New feature or addition
^ Major change
~ Small change
$ Language change
* Security fix
# Bug fix
- Feature removal
