<?php
/**
 *
 * File: mySQLSchema.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 24/03/17
 * Time: 22:38
 *
 * Το Schema της βάσης
 *
 */


// Οι πίνακες της βάσης
$mySqlTables = array (
    array ('table' => 'album_arts', 'sql' => 'CREATE TABLE `album_arts` (
                                              `id` bigint(20) NOT NULL AUTO_INCREMENT,
                                              `path` varchar(255) DEFAULT NULL,
                                              `filename` varchar(255) DEFAULT NULL,
                                              `hash` varchar(100) DEFAULT NULL,
                                              PRIMARY KEY (`id`)
                                              ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;'),
    array ('table' => 'logs', 'sql' => 'CREATE TABLE `logs` (
                                          `id` int(11) NOT NULL AUTO_INCREMENT,
                                          `message` varchar(255) DEFAULT NULL,
                                          `ip` varchar(15) DEFAULT NULL,
                                          `user_name` varchar(15) DEFAULT NULL,
                                          `log_date` datetime DEFAULT NULL,
                                          `browser` varchar(70) DEFAULT NULL,
                                          PRIMARY KEY (`id`)
                                        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;'),
    array ('table' => 'options', 'sql' => 'CREATE TABLE `options` (
                                          `option_id` int(11) NOT NULL AUTO_INCREMENT,
                                          `option_name` varchar(20) NOT NULL,
                                          `option_value` varchar(255) NOT NULL,
                                          `setting` tinyint(1) NOT NULL,
                                          `encrypt` tinyint(1) DEFAULT NULL,
                                          PRIMARY KEY (`option_id`)
                                        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;'),
    array ('table' => 'paths', 'sql' => 'CREATE TABLE `paths` (
                                          `id` int(11) NOT NULL AUTO_INCREMENT,
                                          `file_path` varchar(255) DEFAULT NULL,
                                          `kind` varchar(15) DEFAULT NULL,
                                          `main` tinyint(1) DEFAULT NULL,
                                          PRIMARY KEY (`id`)
                                        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;'),
    array ('table' => 'playlist_tables', 'sql' => 'CREATE TABLE `playlist_tables` (
                                                      `id` bigint(20) NOT NULL AUTO_INCREMENT,
                                                      `table_name` varchar(20) DEFAULT NULL,
                                                      `last_alive` datetime DEFAULT NULL,
                                                      PRIMARY KEY (`id`)
                                                    ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;'),
    array ('table' => 'progress', 'sql' => 'CREATE TABLE `progress` (
                                              `progressID` int(11) NOT NULL AUTO_INCREMENT,
                                              `progressName` varchar(20) DEFAULT NULL,
                                              `progressValue` varchar(255) DEFAULT NULL,
                                              PRIMARY KEY (`progressID`)
                                            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;'),
    array ('table' => 'Session', 'sql' => 'CREATE TABLE `Session` (
                                              `Session_Id` varchar(255) NOT NULL,
                                              `Session_Time` datetime DEFAULT NULL,
                                              `Session_Data` longtext,
                                              PRIMARY KEY (`Session_Id`),
                                              UNIQUE KEY `Session_Id_UNIQUE` (`Session_Id`)
                                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'),
    array ('table' => 'user', 'sql' => 'CREATE TABLE `user` (
                                              `user_id` int(11) NOT NULL AUTO_INCREMENT,
                                              `username` varchar(15) NOT NULL,
                                              `email` varchar(255) NOT NULL,
                                              `password` varchar(255) NOT NULL,
                                              `agent` varchar(15) NOT NULL,
                                              `user_group` smallint(6) DEFAULT NULL,
                                              PRIMARY KEY (`user_id`),
                                              UNIQUE KEY `user_id_UNIQUE` (`user_id`),
                                              UNIQUE KEY `username_UNIQUE` (`username`)
                                            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;'),
    array ('table' => 'salts', 'sql' => 'CREATE TABLE `salts` (
                                          `user_id` int(11) NOT NULL,
                                          `salt` varchar(255) DEFAULT NULL,
                                          `algo` varchar(6) DEFAULT NULL,
                                          `cost` varchar(3) DEFAULT NULL,
                                          PRIMARY KEY (`user_id`),
                                          CONSTRAINT `fk_salts_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'),
    array ('table' => 'user_details', 'sql' => 'CREATE TABLE `user_details` (
                                          `user_id` int(11) NOT NULL,
                                          `fname` varchar(15) DEFAULT NULL,
                                          `lname` varchar(25) DEFAULT NULL,
                                          PRIMARY KEY (`user_id`),
                                          UNIQUE KEY `user_id_UNIQUE` (`user_id`),
                                          CONSTRAINT `fk_user_details_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'),
    array ('table' => 'files', 'sql' => 'CREATE TABLE `files` (
                                              `id` bigint(20) NOT NULL AUTO_INCREMENT,
                                              `path` varchar(255) NOT NULL,
                                              `filename` varchar(255) NOT NULL,
                                              `hash` varchar(100) DEFAULT NULL,
                                              `kind` varchar(20) DEFAULT NULL,
                                              PRIMARY KEY (`id`)
                                            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;'),
    array ('table' => 'music_tags', 'sql' => 'CREATE TABLE `music_tags` (
                                              `id` bigint(20) NOT NULL,
                                              `song_name` varchar(255) DEFAULT NULL,
                                              `artist` varchar(255) DEFAULT NULL,
                                              `genre` varchar(20) DEFAULT NULL,
                                              `date_added` datetime DEFAULT NULL,
                                              `play_count` int(11) DEFAULT NULL,
                                              `date_last_played` datetime DEFAULT NULL,
                                              `rating` tinyint(4) DEFAULT NULL,
                                              `album` varchar(255) DEFAULT NULL,
                                              `video_height` int(11) DEFAULT NULL,
                                              `filesize` bigint(20) DEFAULT NULL,
                                              `video_width` int(11) DEFAULT NULL,
                                              `track_time` float DEFAULT NULL,
                                              `song_year` int(11) DEFAULT NULL,
                                              `live` tinyint(1) DEFAULT NULL,
                                              `album_artwork_id` bigint(20) NOT NULL,
                                              PRIMARY KEY (`id`),
                                              KEY `fk_music_tags_album_arts1_idx` (`album_artwork_id`),
                                              CONSTRAINT `fk_music_tags_album_arts1` FOREIGN KEY (`album_artwork_id`) REFERENCES `album_arts` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                                              CONSTRAINT `fk_music_tags_files1` FOREIGN KEY (`id`) REFERENCES `files` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'),
    array ('table' => 'manual_playlists', 'sql' => 'CREATE TABLE `manual_playlists` (
                                          `id` bigint(20) NOT NULL AUTO_INCREMENT,
                                          `table_name` varchar(20) DEFAULT NULL,
                                          `playlist_name` varchar(50) DEFAULT NULL,
                                          `user_id` int(11) NOT NULL,
                                          PRIMARY KEY (`id`,`user_id`),
                                          KEY `fk_manual_playlists_user1_idx` (`user_id`),
                                          CONSTRAINT `fk_manual_playlists_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                                        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;'),
    array ('table' => 'votes', 'sql' => 'CREATE TABLE `votes` (
                                          `id` bigint(20) NOT NULL AUTO_INCREMENT,
                                          `file_id` bigint(20) DEFAULT NULL,
                                          `voter_ip` varchar(20) DEFAULT NULL,
                                          PRIMARY KEY (`id`)
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'),
    array ('table' => 'download_paths', 'sql' => 'CREATE TABLE `download_paths` (
                                          `id` INT(11) NOT NULL AUTO_INCREMENT,
                                          `file_path` VARCHAR(255) NULL DEFAULT NULL,
                                          `path_name` VARCHAR(25) NULL,
                                          PRIMARY KEY (`id`))
                                        ENGINE = InnoDB
                                        AUTO_INCREMENT = 4
                                        DEFAULT CHARACTER SET = utf8'),
    array ('table' => 'smart_playlists', 'sql' => 'CREATE TABLE IF NOT EXISTS `smart_playlists` (
                                          `id` BIGINT NOT NULL AUTO_INCREMENT,
                                          `playlist_name` VARCHAR(50) NULL,
                                          `playlist_data` TEXT NULL,
                                          `user_id` INT(11) NOT NULL,
                                          PRIMARY KEY (`id`, `user_id`),
                                          INDEX `fk_manual_playlists_user1_idx` (`user_id` ASC),
                                          CONSTRAINT `fk_manual_playlists_user10`
                                            FOREIGN KEY (`user_id`)
                                            REFERENCES `user` (`user_id`)
                                            ON DELETE NO ACTION
                                            ON UPDATE NO ACTION)
                                        ENGINE = InnoDB DEFAULT CHARACTER SET = utf8')
);

$mySqlChanges = array(
                array('table' => 'options', 'field' => 'option_id', 'oldType' => 'tinyint(4)',
                    'sql' => 'ALTER TABLE options MODIFY option_id int(11) AUTO_INCREMENT'),
                array('table' => 'smart_playlists', 'field' => 'playlist_data', 'oldType' => 'varchar(1000)',
                    'sql' => 'ALTER TABLE smart_playlists CHANGE COLUMN playlist_data playlist_data TEXT NULL DEFAULT NULL')
);

$defaultOptions = array(
    array('option_name' => 'convert_alac_files', 'option_value' => 'false', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'playlist_limit', 'option_value' => '150', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'dir_prefix', 'option_value' => '/', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'syncItunes', 'option_value' => 'false', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'date_format', 'option_value' => 'Y-m-d', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'icecast_server', 'option_value' => '0.0.0.0:8000', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'icecast_mount', 'option_value' => 'listen', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'icecast_user', 'option_value' => 'user', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'icecast_pass', 'option_value' => 'pass', 'setting' => 1, 'encrypt' => 1),
    array('option_name' => 'icecast_enable', 'option_value' => 'false', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'jukebox_enable', 'option_value' => 'false', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'default_language', 'option_value' => 'en', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'youtube_api', 'option_value' => 'AIzaSyArMqCdw1Ih1592YL96a2Vdo5sGo6vsS4A', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'play_percentage', 'option_value' => '20', 'setting' => 1, 'encrypt' => 0),
    array('option_name' => 'max_video_height', 'option_value' => '1080', 'setting' => 1, 'encrypt' => 0)
);

$defaultProgress = array(
    array('progressName' => 'progressInPercent', 'progressValue' => '0'),
    array('progressName' => 'progressMessage', 'progressValue' => ''),
    array('progressName' => 'killCommand', 'progressValue' => '0'),
    array('progressName' => 'lastMomentAlive', 'progressValue' => time()),
    array('progressName' => 'currentSong', 'progressValue' => '0'),
    array('progressName' => 'restoreRunning', 'progressValue' => '0')
);

$defaultPathNames = array(
    array('pathName' => 'musicDownloadPath', 'path' => $_SERVER["DOCUMENT_ROOT"] . PROJECT_PATH . 'upload/musicDownload'),
    array('pathName' => 'musicVideoDownloadPath', 'path' => $_SERVER["DOCUMENT_ROOT"] . PROJECT_PATH . 'upload/videoDownload'),
    array('pathName' => 'outputFolder', 'path' => $_SERVER["DOCUMENT_ROOT"] . PROJECT_PATH . 'upload/output'),
    array('pathName' => 'coverAlbumsFolder', 'path' => $_SERVER["DOCUMENT_ROOT"] . PROJECT_PATH . 'upload/albumCovers'),
    array('pathName' => 'convertedALAC', 'path' => $_SERVER["DOCUMENT_ROOT"] . PROJECT_PATH . 'upload/convertedMusic')
);