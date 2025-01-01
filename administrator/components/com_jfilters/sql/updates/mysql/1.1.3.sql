# We drop that index as it was unique and did not let us copy filters
ALTER TABLE `#__jfilters_filters` DROP INDEX `parent_id_config_name_language`;

# And we add it again as non unique
ALTER TABLE `#__jfilters_filters` ADD KEY `parent_id_config_name_lang` (`parent_id`,`config_name`,`language`);
