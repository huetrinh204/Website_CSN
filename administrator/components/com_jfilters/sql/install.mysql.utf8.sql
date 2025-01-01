CREATE TABLE `#__jfilters_filters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int NOT NULL COMMENT 'The id as defined in the filter.definition->id of the filters.xml',
  `config_name` varchar(36) NOT NULL COMMENT 'The name as defined in the filter->name of the filters.xml',
  `context` varchar(255) NOT NULL COMMENT 'The context as defined in the filter.definition->context of the filters.xml',
  `name` varchar(255) NOT NULL COMMENT 'The title as defined in the filter.definition->title of the filters.xml',
  `label` varchar(255) DEFAULT '' COMMENT 'The label of the filter as displayed in the front-end',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `display` varchar(255) NOT NULL DEFAULT 'list' COMMENT 'The display type of the filter',
  `state` tinyint(4) NOT NULL DEFAULT 0,
  `access` int NOT NULL DEFAULT 1,
  `root` tinyint(4) NOT NULL DEFAULT 0,
  `ordering` int NOT NULL DEFAULT 0,
  `attribs` VARCHAR( 5120 ) NOT NULL COMMENT 'Stores the attributes',
  `checked_out` int NOT NULL DEFAULT 0,
  `checked_out_time` datetime DEFAULT NULL,
  `created_time` datetime DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `language` char(7) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_state` (`state`),
  KEY `parent_id_config_name_lang` (`parent_id`,`config_name`, `language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


-- Add indexes to the 3rd party tables

-- Used by the categories query
ALTER TABLE `#__content` ADD KEY `jf_cat_id_state_access_language` (`catid`,`state`,`access`, `language`) COMMENT 'Added by JFilters';
ALTER TABLE `#__categories` ADD KEY `jf_parent_id` (`parent_id`) COMMENT 'Added by JFilters';
ALTER TABLE `#__categories` ADD KEY `jf_published_extension` (`published`,`extension`) COMMENT 'Added by JFilters';

-- Used by the tags query
ALTER TABLE `#__tags` ADD KEY `jf_parent_id` (`parent_id`) COMMENT 'Added by JFilters';
-- Used by the tags query when there is search
ALTER TABLE `#__contentitem_tag_map` ADD UNIQUE KEY `jf_content_item_id_tag_id_type_id` (`content_item_id`,`tag_id`,`type_id`) COMMENT 'Added by JFilters';

-- Used by filters joined with selected field filters
ALTER TABLE `#__fields_values` ADD KEY `jf_item_id_field_id` (`item_id`(80),`field_id`) COMMENT 'Added by JFilters';
ALTER TABLE `#__fields_values` ADD KEY `jf_value_field_id` (`value`(80),`field_id`) COMMENT 'Added by JFilters';

-- Used by filters joined with selected tag filters
ALTER TABLE `#__contentitem_tag_map` ADD UNIQUE KEY `jf_tag_id_item_id_type_id` (`tag_id`,`content_item_id`, `type_id`) COMMENT 'Added by JFilters';

-- Used by the ResultsModel to fetch results when tag/s are selected
ALTER TABLE `#__contentitem_tag_map` ADD KEY `jf_tag_id` (`tag_id`) COMMENT 'Added by JFilters';
