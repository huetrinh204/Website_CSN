--
-- Create the jfilters table
-- We get the component's install.mysql file through php



--
-- Table structure for table `#__fields`
--

CREATE TABLE IF NOT EXISTS `#__fields` (
                                           `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                           `asset_id` int(10) unsigned NOT NULL DEFAULT 0,
                                           `context` varchar(255) NOT NULL DEFAULT '',
                                           `group_id` int(10) unsigned NOT NULL DEFAULT 0,
                                           `title` varchar(255) NOT NULL DEFAULT '',
                                           `name` varchar(255) NOT NULL DEFAULT '',
                                           `label` varchar(255) NOT NULL DEFAULT '',
                                           `default_value` text,
                                           `type` varchar(255) NOT NULL DEFAULT 'text',
                                           `note` varchar(255) NOT NULL DEFAULT '',
                                           `description` text NOT NULL,
                                           `state` tinyint(1) NOT NULL DEFAULT 0,
                                           `required` tinyint(1) NOT NULL DEFAULT 0,
                                           `checked_out` int(11) NOT NULL DEFAULT 0,
                                           `checked_out_time` datetime,
                                           `ordering` int(11) NOT NULL DEFAULT 0,
                                           `params` text NOT NULL,
                                           `fieldparams` text NOT NULL,
                                           `language` char(7) NOT NULL DEFAULT '',
                                           `created_time` datetime NOT NULL,
                                           `created_user_id` int(10) unsigned NOT NULL DEFAULT 0,
                                           `modified_time` datetime NOT NULL,
                                           `modified_by` int(10) unsigned NOT NULL DEFAULT 0,
                                           `access` int(11) NOT NULL DEFAULT 1,
                                           `only_use_in_subform` tinyint NOT NULL DEFAULT 0,
                                           PRIMARY KEY (`id`),
                                           KEY `idx_checkout` (`checked_out`),
                                           KEY `idx_state` (`state`),
                                           KEY `idx_created_user_id` (`created_user_id`),
                                           KEY `idx_access` (`access`),
                                           KEY `idx_context` (`context`(191)),
                                           KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__fields_values`
--

CREATE TABLE IF NOT EXISTS `#__fields_values` (
                                                  `field_id` int(10) unsigned NOT NULL,
                                                  `item_id` varchar(255) NOT NULL COMMENT 'Allow references to items which have strings as ids, eg. none db systems.',
                                                  `value` text,
                                                  KEY `idx_field_id` (`field_id`),
                                                  KEY `idx_item_id` (`item_id`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- Tags
CREATE TABLE `#__tags` (
                              `id` int(10) UNSIGNED NOT NULL,
                              `parent_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                              `lft` int(11) NOT NULL DEFAULT '0',
                              `rgt` int(11) NOT NULL DEFAULT '0',
                              `level` int(10) UNSIGNED NOT NULL DEFAULT '0',
                              `path` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                              `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                              `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
                              `note` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                              `description` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
                              `published` tinyint(1) NOT NULL DEFAULT '0',
                              `checked_out` int(10) UNSIGNED DEFAULT NULL,
                              `checked_out_time` datetime DEFAULT NULL,
                              `access` int(10) UNSIGNED NOT NULL DEFAULT '0',
                              `params` text COLLATE utf8mb4_unicode_ci NOT NULL,
                              `metadesc` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The meta description for the page.',
                              `metakey` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'The keywords for the page.',
                              `metadata` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'JSON encoded metadata properties.',
                              `created_user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                              `created_time` datetime NOT NULL,
                              `created_by_alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                              `modified_user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                              `modified_time` datetime NOT NULL,
                              `images` text COLLATE utf8mb4_unicode_ci NOT NULL,
                              `urls` text COLLATE utf8mb4_unicode_ci NOT NULL,
                              `hits` int(10) UNSIGNED NOT NULL DEFAULT '0',
                              `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL,
                              `version` int(10) UNSIGNED NOT NULL DEFAULT '1',
                              `publish_up` datetime DEFAULT NULL,
                              `publish_down` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Table structure for table `#__contentitem_tag_map`
--

CREATE TABLE `#__contentitem_tag_map` (
                                             `type_alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                                             `core_content_id` int(10) UNSIGNED NOT NULL COMMENT 'PK from the core content table',
                                             `content_item_id` int(11) NOT NULL COMMENT 'PK from the content type table',
                                             `tag_id` int(10) UNSIGNED NOT NULL COMMENT 'PK from the tag table',
                                             `tag_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Date of most recent save for this tag-item',
                                             `type_id` mediumint(8) NOT NULL COMMENT 'PK from the content_type table'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Maps items from content tables to tags';

--
-- Table structure for table `#__categories`
--

CREATE TABLE IF NOT EXISTS `#__categories` (
                                               `id` int(11) NOT NULL AUTO_INCREMENT,
                                               `asset_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'FK to the #__assets table.',
                                               `parent_id` int(10) unsigned NOT NULL DEFAULT 0,
                                               `lft` int(11) NOT NULL DEFAULT 0,
                                               `rgt` int(11) NOT NULL DEFAULT 0,
                                               `level` int(10) unsigned NOT NULL DEFAULT 0,
                                               `path` varchar(400) NOT NULL DEFAULT '',
                                               `extension` varchar(50) NOT NULL DEFAULT '',
                                               `title` varchar(255) NOT NULL DEFAULT '',
                                               `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
                                               `note` varchar(255) NOT NULL DEFAULT '',
                                               `description` mediumtext,
                                               `published` tinyint(1) NOT NULL DEFAULT 0,
                                               `checked_out` int(11) unsigned NOT NULL DEFAULT 0,
                                               `checked_out_time` datetime,
                                               `access` int(10) unsigned NOT NULL DEFAULT 0,
                                               `params` text,
                                               `metadesc` varchar(1024) NOT NULL DEFAULT '' COMMENT 'The meta description for the page.',
                                               `metakey` varchar(1024) NOT NULL DEFAULT '' COMMENT 'The meta keywords for the page.',
                                               `metadata` varchar(2048) NOT NULL DEFAULT '' COMMENT 'JSON encoded metadata properties.',
                                               `created_user_id` int(10) unsigned NOT NULL DEFAULT 0,
                                               `created_time` datetime NOT NULL,
                                               `modified_user_id` int(10) unsigned NOT NULL DEFAULT 0,
                                               `modified_time` datetime NOT NULL,
                                               `hits` int(10) unsigned NOT NULL DEFAULT 0,
                                               `language` char(7) NOT NULL DEFAULT '',
                                               `version` int(10) unsigned NOT NULL DEFAULT 1,
                                               PRIMARY KEY (`id`),
                                               KEY `cat_idx` (`extension`,`published`,`access`),
                                               KEY `idx_access` (`access`),
                                               KEY `idx_checkout` (`checked_out`),
                                               KEY `idx_path` (`path`(100)),
                                               KEY `idx_left_right` (`lft`,`rgt`),
                                               KEY `idx_alias` (`alias`(100)),
                                               KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


--
-- Table structure for table `#__content`
--

CREATE TABLE IF NOT EXISTS `#__content` (
                                            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                            `asset_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'FK to the #__assets table.',
                                            `title` varchar(255) NOT NULL DEFAULT '',
                                            `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
                                            `introtext` mediumtext NOT NULL,
                                            `fulltext` mediumtext NOT NULL,
                                            `state` tinyint(3) NOT NULL DEFAULT 0,
                                            `catid` int(10) unsigned NOT NULL DEFAULT 0,
                                            `created` datetime NOT NULL,
                                            `created_by` int(10) unsigned NOT NULL DEFAULT 0,
                                            `created_by_alias` varchar(255) NOT NULL DEFAULT '',
                                            `modified` datetime NOT NULL,
                                            `modified_by` int(10) unsigned NOT NULL DEFAULT 0,
                                            `checked_out` int(10) unsigned NOT NULL DEFAULT 0,
                                            `checked_out_time` datetime NULL DEFAULT NULL,
                                            `publish_up` datetime NULL DEFAULT NULL,
                                            `publish_down` datetime NULL DEFAULT NULL,
                                            `images` text NOT NULL,
                                            `urls` text NOT NULL,
                                            `attribs` varchar(5120) NOT NULL,
                                            `version` int(10) unsigned NOT NULL DEFAULT 1,
                                            `ordering` int(11) NOT NULL DEFAULT 0,
                                            `metakey` text NOT NULL,
                                            `metadesc` text NOT NULL,
                                            `access` int(10) unsigned NOT NULL DEFAULT 0,
                                            `hits` int(10) unsigned NOT NULL DEFAULT 0,
                                            `metadata` text NOT NULL,
                                            `featured` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT 'Set if article is featured.',
                                            `language` char(7) NOT NULL COMMENT 'The language code for the article.',
                                            `note` varchar(255) NOT NULL DEFAULT '',
                                            PRIMARY KEY (`id`),
                                            KEY `idx_access` (`access`),
                                            KEY `idx_checkout` (`checked_out`),
                                            KEY `idx_state` (`state`),
                                            KEY `idx_catid` (`catid`),
                                            KEY `idx_createdby` (`created_by`),
                                            KEY `idx_featured_catid` (`featured`,`catid`),
                                            KEY `idx_language` (`language`),
                                            KEY `idx_alias` (`alias`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
