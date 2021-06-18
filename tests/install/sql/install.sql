CREATE TABLE IF NOT EXISTS `book`
(
    `id`          int(10) unsigned                     NOT NULL AUTO_INCREMENT,
    `name`        varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `isbn`        char(13) COLLATE utf8_unicode_ci     NOT NULL,
    `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `published`   date                                 NOT NULL,
    `created_at`  timestamp                            NOT NULL DEFAULT current_timestamp(),
    `updated_at`  timestamp                            NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `name_UNIQUE` (`name`),
    UNIQUE KEY `isbn_UNIQUE` (`isbn`),
    KEY `book_description_index` (`description`),
    KEY `book_published_index` (`published`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `category`
(
    `id`          int(10) unsigned                     NOT NULL AUTO_INCREMENT,
    `name`        varchar(45) COLLATE utf8_unicode_ci  NOT NULL,
    `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `created_at`  timestamp                            NOT NULL DEFAULT current_timestamp(),
    `updated_at`  timestamp                            NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

CREATE TABLE `book_category`
(
    `id_book`     int(10) unsigned NOT NULL,
    `id_category` int(10) unsigned NOT NULL,
    KEY `book_index` (`id_book`),
    KEY `category_index` (`id_category`),
    CONSTRAINT `fk_book_category_book` FOREIGN KEY (`id_book`) REFERENCES `book` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_book_category_category` FOREIGN KEY (`id_category`) REFERENCES `category` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;
