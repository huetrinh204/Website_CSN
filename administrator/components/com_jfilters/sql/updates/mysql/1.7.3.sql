ALTER TABLE `#__contentitem_tag_map` DROP INDEX `jf_tag_id_item_id`;
ALTER TABLE `#__contentitem_tag_map` ADD UNIQUE KEY `jf_tag_id_item_id_type_id` (`tag_id`,`content_item_id`,`type_id`) COMMENT 'Added by JFilters';
