DROP TABLE IF EXISTS `#__jfilters_filters`;

-- We have to drop the indexes added to 3rd party table. Otherwise there will be an installation error in case of re-install (i.e. trying to add the indexes again)
ALTER TABLE `#__categories` DROP INDEX `jf_parent_id`;
ALTER TABLE `#__categories` DROP INDEX `jf_published_extension`;
ALTER TABLE `#__content` DROP INDEX `jf_cat_id_state_access_language`;
ALTER TABLE `#__contentitem_tag_map` DROP INDEX `jf_content_item_id_tag_id_type_id`;
ALTER TABLE `#__contentitem_tag_map` DROP INDEX `jf_tag_id_item_id_type_id`;
ALTER TABLE `#__contentitem_tag_map` DROP INDEX `jf_tag_id`;
ALTER TABLE `#__fields_values` DROP INDEX `jf_item_id_field_id`;
ALTER TABLE `#__fields_values` DROP INDEX `jf_value_field_id`;
ALTER TABLE `#__tags` DROP INDEX `jf_parent_id`;
