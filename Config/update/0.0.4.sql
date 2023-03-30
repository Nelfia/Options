# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;


DROP TABLE IF EXISTS `category_available_option`;

CREATE TABLE `category_available_option`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `category_id` INTEGER NOT NULL,
    `option_id` INTEGER NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `category_available_option_UNIQUE` (`category_id`, `option_id`),
    INDEX `idx_category_available_option_category_id` (`category_id`),
    INDEX `idx_category_available_option_option_id` (`option_id`),
    CONSTRAINT `fk_category_available_option_product_id`
        FOREIGN KEY (`category_id`)
            REFERENCES `category` (`id`)
            ON UPDATE RESTRICT
            ON DELETE CASCADE,
    CONSTRAINT `fk_category_available_option_option_id`
        FOREIGN KEY (`option_id`)
            REFERENCES `option_product` (`id`)
            ON UPDATE RESTRICT
            ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- template_available_option
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `template_available_option`;

CREATE TABLE `template_available_option`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `template_id` INTEGER NOT NULL,
    `option_id` INTEGER NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `template_available_option_UNIQUE` (`template_id`, `option_id`),
    INDEX `idx_template_available_option_template_id` (`template_id`),
    INDEX `idx_template_available_option_option_id` (`option_id`),
    CONSTRAINT `fk_template_available_option_product_id`
        FOREIGN KEY (`template_id`)
            REFERENCES `template` (`id`)
            ON UPDATE RESTRICT
            ON DELETE CASCADE,
    CONSTRAINT `fk_template_available_option_option_id`
        FOREIGN KEY (`option_id`)
            REFERENCES `option_product` (`id`)
            ON UPDATE RESTRICT
            ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;