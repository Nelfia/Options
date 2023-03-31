# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `product_available_option`;

CREATE TABLE `product_available_option`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `product_id` INTEGER NOT NULL,
    `option_id` INTEGER NOT NULL,
    `product_sale_elements_id` INTEGER,
    `product_available_option_customization` TEXT,
    `option_added_by` JSON,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `product_available_option_UNIQUE` (`product_id`, `option_id`, `product_sale_elements_id`),
    INDEX `idx_product_available_option_product_id` (`product_id`),
    INDEX `idx_product_available_option_option_id` (`option_id`),
    INDEX `idx_product_sale_elements_option_id` (`product_sale_elements_id`),
    CONSTRAINT `fk_product_available_option_product_id`
        FOREIGN KEY (`product_id`)
            REFERENCES `product` (`id`)
            ON UPDATE RESTRICT
            ON DELETE CASCADE,
    CONSTRAINT `fk_product_sale_elements_option_id`
        FOREIGN KEY (`product_sale_elements_id`)
            REFERENCES `product_sale_elements` (`id`)
            ON UPDATE RESTRICT
            ON DELETE CASCADE,
    CONSTRAINT `fk_product_available_option_option_id`
        FOREIGN KEY (`option_id`)
            REFERENCES `option_product` (`id`)
            ON UPDATE RESTRICT
            ON DELETE CASCADE
) ENGINE=InnoDB;


# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;