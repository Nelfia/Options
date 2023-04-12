
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- option_product
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `option_product`;

CREATE TABLE `option_product`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `product_id` INTEGER NOT NULL,
    `configuration` TEXT,
    PRIMARY KEY (`id`),
    INDEX `fi_option_product_product_id` (`product_id`),
    CONSTRAINT `fk_option_product_product_id`
        FOREIGN KEY (`product_id`)
        REFERENCES `product` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- product_available_option
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `product_available_option`;

CREATE TABLE `product_available_option`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `product_id` INTEGER NOT NULL,
    `option_id` INTEGER NOT NULL,
    `option_added_by` JSON,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `product_available_option_UNIQUE` (`product_id`, `option_id`),
    INDEX `idx_product_available_option_product_id` (`product_id`),
    INDEX `idx_product_available_option_option_id` (`option_id`),
    CONSTRAINT `fk_product_available_option_product_id`
        FOREIGN KEY (`product_id`)
        REFERENCES `product` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,
    CONSTRAINT `fk_product_available_option_option_id`
        FOREIGN KEY (`option_id`)
        REFERENCES `option_product` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- pse_available_option
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `pse_available_option`;

CREATE TABLE `pse_available_option`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `pse_id` INTEGER NOT NULL,
    `option_id` INTEGER NOT NULL,
    `option_pse_id` INTEGER NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `pse_available_option_UNIQUE` (`pse_id`, `option_pse_id`, `option_id`),
    INDEX `idx_product_sale_elements_pse_id` (`pse_id`),
    INDEX `idx_product_sale_elements_option_pse_id` (`option_pse_id`),
    INDEX `idx_product_sale_elements_option_id` (`option_id`),
    CONSTRAINT `fk_product_sale_elements_pse_id`
        FOREIGN KEY (`pse_id`)
        REFERENCES `product_sale_elements` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,
    CONSTRAINT `fk_product_sale_elements_option_pse_id`
        FOREIGN KEY (`option_pse_id`)
        REFERENCES `product_sale_elements` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,
    CONSTRAINT `fk_option_product_option_id`
        FOREIGN KEY (`option_id`)
        REFERENCES `option_product` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- category_available_option
-- ---------------------------------------------------------------------

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

-- ---------------------------------------------------------------------
-- option_cart_item_customization
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `option_cart_item_customization`;

CREATE TABLE `option_cart_item_customization`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `product_available_option_id` INTEGER NOT NULL,
    `cart_item_option_id` INTEGER,
    `order_product_id` INTEGER,
    `data_customization_order_product_id` INTEGER,
    `customisation_data` TEXT,
    `price` DECIMAL(16,6) DEFAULT 0.000000,
    `taxed_price` DECIMAL(16,6) DEFAULT 0.000000,
    `quantity` VARCHAR(255),
    PRIMARY KEY (`id`),
    INDEX `fi_cart_item_option_id_ci` (`cart_item_option_id`),
    INDEX `fi_cart_item_customization_op` (`order_product_id`),
    INDEX `fi_data_customization_order_product_op` (`data_customization_order_product_id`),
    INDEX `fi_product_available_option_cart_item_customization` (`product_available_option_id`),
    CONSTRAINT `fk_cart_item_option_id_ci`
        FOREIGN KEY (`cart_item_option_id`)
        REFERENCES `cart_item` (`id`)
        ON DELETE SET NULL,
    CONSTRAINT `fk_cart_item_customization_op`
        FOREIGN KEY (`order_product_id`)
        REFERENCES `order_product` (`id`)
        ON DELETE SET NULL,
    CONSTRAINT `fk_data_customization_order_product_op`
        FOREIGN KEY (`data_customization_order_product_id`)
        REFERENCES `order_product` (`id`)
        ON DELETE SET NULL,
    CONSTRAINT `fk_product_available_option_cart_item_customization`
        FOREIGN KEY (`product_available_option_id`)
        REFERENCES `product_available_option` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
