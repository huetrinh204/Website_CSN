CREATE TABLE IF NOT EXISTS `#__jfilters_fields_subform_values`
(
    `field_id` int unsigned NOT NULL COMMENT 'Key to the field id',
    `item_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Key to the item''s id',
    `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    KEY `field_id` (`field_id`),
    KEY `item_id` (`item_id`(60)),
    CONSTRAINT field_id FOREIGN KEY (`field_id`)
    REFERENCES #__fields(`id`)
    ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;