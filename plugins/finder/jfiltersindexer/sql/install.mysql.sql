CREATE TABLE IF NOT EXISTS `#__jfilters_links_items`
(
    `link_id` int unsigned NOT NULL COMMENT 'Key to the finder_links.link_id',
    `item_id` int unsigned NOT NULL COMMENT 'Key to the item''s id',
    `context` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    UNIQUE KEY `link_id` (`link_id`),
    KEY `item_id` (`item_id`),
    KEY `context` (`context`(60))
) ENGINE = InnoDB
  DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
