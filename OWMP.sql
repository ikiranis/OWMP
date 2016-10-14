-- MySQL Script generated by MySQL Workbench
-- Fri Oct 14 19:33:34 2016
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema OWMP
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema OWMP
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `OWMP` DEFAULT CHARACTER SET utf8 ;
USE `OWMP` ;

-- -----------------------------------------------------
-- Table `OWMP`.`Session`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `OWMP`.`Session` (
  `Session_Id` VARCHAR(255) NOT NULL,
  `Session_Time` DATETIME NULL DEFAULT NULL,
  `Session_Data` LONGTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`Session_Id`),
  UNIQUE INDEX `Session_Id_UNIQUE` (`Session_Id` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `OWMP`.`options`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `OWMP`.`options` (
  `option_id` TINYINT(4) NOT NULL AUTO_INCREMENT,
  `option_name` VARCHAR(20) NOT NULL,
  `option_value` VARCHAR(255) NOT NULL,
  `setting` TINYINT(1) NOT NULL,
  `encrypt` TINYINT(1) NULL DEFAULT NULL,
  PRIMARY KEY (`option_id`))
ENGINE = InnoDB
AUTO_INCREMENT = 16
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `OWMP`.`paths`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `OWMP`.`paths` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `file_path` VARCHAR(255) NULL DEFAULT NULL,
  `kind` VARCHAR(15) NULL,
  `main` TINYINT(1) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `OWMP`.`user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `OWMP`.`user` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(15) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `agent` VARCHAR(15) NOT NULL,
  `user_group` SMALLINT(6) NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `user_id_UNIQUE` (`user_id` ASC),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 32
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `OWMP`.`salts`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `OWMP`.`salts` (
  `user_id` INT(11) NOT NULL,
  `salt` VARCHAR(255) NULL DEFAULT NULL,
  `algo` VARCHAR(6) NULL DEFAULT NULL,
  `cost` VARCHAR(3) NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_salts_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `OWMP`.`user` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `OWMP`.`user_details`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `OWMP`.`user_details` (
  `user_id` INT(11) NOT NULL,
  `fname` VARCHAR(15) NULL DEFAULT NULL,
  `lname` VARCHAR(25) NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `user_id_UNIQUE` (`user_id` ASC),
  CONSTRAINT `fk_user_details_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `OWMP`.`user` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `OWMP`.`files`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `OWMP`.`files` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `path` VARCHAR(255) NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `hash` VARCHAR(100) NULL,
  `kind` VARCHAR(20) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `OWMP`.`logs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `OWMP`.`logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `message` VARCHAR(255) NULL DEFAULT NULL,
  `ip` VARCHAR(15) NULL DEFAULT NULL,
  `user_name` VARCHAR(15) NULL DEFAULT NULL,
  `log_date` DATETIME NULL DEFAULT NULL,
  `browser` VARCHAR(70) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `OWMP`.`album_arts`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `OWMP`.`album_arts` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `path` VARCHAR(255) NULL,
  `filename` VARCHAR(255) NULL,
  `hash` VARCHAR(100) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `OWMP`.`music_tags`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `OWMP`.`music_tags` (
  `id` BIGINT NOT NULL,
  `song_name` VARCHAR(255) NULL,
  `artist` VARCHAR(255) NULL,
  `genre` VARCHAR(20) NULL,
  `date_added` DATETIME NULL,
  `play_count` INT NULL,
  `date_last_played` DATETIME NULL,
  `rating` TINYINT NULL,
  `album` VARCHAR(255) NULL,
  `video_height` INT NULL,
  `filesize` BIGINT NULL,
  `video_width` INT NULL,
  `track_time` FLOAT NULL,
  `song_year` INT NULL,
  `live` TINYINT(1) NULL,
  `album_artwork_id` BIGINT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_music_tags_album_arts1_idx` (`album_artwork_id` ASC),
  CONSTRAINT `fk_music_tags_album_arts1`
    FOREIGN KEY (`album_artwork_id`)
    REFERENCES `OWMP`.`album_arts` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_music_tags_files1`
    FOREIGN KEY (`id`)
    REFERENCES `OWMP`.`files` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `OWMP`.`current_playlist`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `OWMP`.`current_playlist` (
  `id` INT NOT NULL,
  `file_id` INT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `OWMP`.`progress`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `OWMP`.`progress` (
  `progressID` INT NOT NULL AUTO_INCREMENT,
  `progressName` VARCHAR(20) NULL,
  `progressValue` VARCHAR(255) NULL,
  PRIMARY KEY (`progressID`))
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
