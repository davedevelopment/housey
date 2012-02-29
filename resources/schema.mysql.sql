CREATE TABLE IF NOT EXISTS `housey_experiments` (
    `id` INT(10) UNSIGNED NOT NULL auto_increment,
    `test_name` VARCHAR(255) NOT NULL,
    `status` VARCHAR(255) NOT NULL,
    `modified` DATETIME NOT NULL,
    `created` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `test_name_idx` (`test_name`)
) Engine=InnoDB DEFAULT CHARSET 'UTF8';

CREATE TABLE IF NOT EXISTS `housey_alternatives` (
    `id` INT(10) UNSIGNED NOT NULL auto_increment,
    `housey_experiment_id` INT(10) UNSIGNED NOT NULL,
    `content` VARCHAR(255) NULL,
    `lookup` CHAR(32) NOT NULL,
    `weight` INT(6) NOT NULL DEFAULT 1,
    `participants` INT(10) NOT NULL DEFAULT 0,
    `conversions` INT(10) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX `housey_experiment_id_idx` (`housey_experiment_id`),
    INDEX `lookup_idx` (`lookup`)
) Engine=InnoDB DEFAULT CHARSET 'UTF8';
