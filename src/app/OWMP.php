<?php

/**
 * File: OWMP.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 19/06/16
 * Time: 23:18
 *
 * Κλάση με τα βασικά στοιχεία του Parrot Tunes
 *
 * Σπάει σε OWMPElements για τις λεπτομερέστετες μέθοδους
 *
 */

namespace apps4net\parrot\app;

use apps4net\framework\Language;
use apps4net\framework\MyDB;
use apps4net\framework\Page;
use apps4net\framework\User;

class OWMP
{


    // Εμφανίζει το βίντεο
    static function showVideo ()
    {

        
        $tags = new Page();
        $conn = new MyDB();
        $user = new User();

        $UserGroup=$user->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης

        if ($UserGroup==1)  // Αν ο χρήστης είναι admin
            $disabled='no';
        else $disabled='yes';


        $FormElementsArray = array(
            array('name' => 'title',
                'fieldtext' => __('tag_title'),
                'type' => 'text',
                'required' => 'no',
                'maxlength' => '255',
                'readonly' => $disabled,
                'allwaysview' => 'yes',
                'value' => null),
            array('name' => 'artist',
                'fieldtext' => __('tag_artist'),
                'type' => 'text',
                'required' => 'no',
                'maxlength' => '255',
                'readonly' => $disabled,
                'allwaysview' => 'yes',
                'value' => null),
            array('name' => 'album',
                'fieldtext' => __('tag_album'),
                'type' => 'text',
                'required' => 'no',
                'maxlength' => '255',
                'readonly' => $disabled,
                'allwaysview' => 'yes',
                'value' => null),
            array('name' => 'genre',
                'fieldtext' => __('tag_genre'),
                'type' => 'text',
                'required' => 'no',
                'maxlength' => '20',
                'readonly' => $disabled,
                'allwaysview' => 'yes',
                'value' => null),
            array('name' => 'year',
                'fieldtext' => __('tag_year'),
                'type' => 'number',
                'required' => 'no',
                'readonly' => $disabled,
                'allwaysview' => 'yes',
                'value' => null),
            array('name' => 'live',
                'fieldtext' => __('tag_live'),
                'type' => 'select',
                'options' => array(
                    array('value' => '0', 'name' => __('tag_live_official')),
                    array('value' => '1', 'name' => __('tag_live_live'))
                ),
                'required' => 'no',
                'maxlength' => '1',
                'readonly' => $disabled,
                'disabled' => $disabled,
                'allwaysview' => 'yes',
                'value' => null),
            array('name' => 'rating',
                'fieldtext' => __('tag_rating'),
                'type' => 'range',
                'required' => 'no',
                'maxlength' => '5',
                'min' => '0',
                'max' => '5',
                'step' => '1',
                'ticks' => array(0,1,2,3,4,5),
                'disabled' => $disabled,
                'allwaysview' => 'yes',
                'value' => '0'),

            

            array('name' => 'play_count',
                'fieldtext' => __('tag_play_count'),
                'type' => 'number',
                'required' => 'no',
                'disabled' => 'no',
                'readonly' => 'yes',
                'allwaysview' => 'no',
                'value' => null),
            array('name' => 'date_added',
                'fieldtext' => __('tag_date_added'),
                'type' => 'text',
                'required' => 'no',
                'maxlength' => '20',
                'disabled' => 'no',
                'readonly' => 'yes',
                'allwaysview' => 'no',
                'value' => null),
            array('name' => 'date_played',
                'fieldtext' => __('tag_date_played'),
                'type' => 'text',
                'required' => 'no',
                'maxlength' => '20',
                'disabled' => 'no',
                'readonly' => 'yes',
                'allwaysview' => 'no',
                'value' => null),

            array('name' => 'path_filename',
                'fieldtext' => __('tag_path_filename'),
                'type' => 'text',
                'required' => 'no',
                'maxlength' => '255',
                'disabled' => 'no',
                'readonly' => 'yes',
                'allwaysview' => 'no',
                'value' => null)

        );

        
        ?>

        <video id="myVideo" width="100%" onerror="failed(event);" ondblclick="displayFullscreenControls();"></video>

        <?php OWMPElements::displayControls('mediaControls', false); ?>
        
        <div id="the_time_track">
            <span id="current_track_time">00:00</span> /
            <span id="total_track_time">00:00</span>
        </div>

        <div id="track_time">
            <input type=range id="track_range" name="track_range" min=0 max=100 list=overlay_track_ticks value=0 oninput="controlTrack();">
        </div>

        
        <div id="overlay_volume">
            <span id="overlay_volume_text">
                
            </span>
        </div>

        <!--        Fullscreen overlay elements-->
        <div id="overlay" ondblclick="displayFullscreenControls();">
            <div id="overlay_rating"></div>
            <div id="overlay_play_count"></div>
            <div id="overlay_track_time">
                <span id="overlay_current_track_time">00:00</span>
                <input type=range id="overlay_track_range" name="overlay_track_range" min=0 max=100 list=overlay_track_ticks value=0 oninput="controlTrack();">
                <span id="overlay_total_track_time">00:00</span>
            </div>



            <div id="bottom_overlay">
                <span id="overlay_song_name"></span>
                <span id="overlay_artist"></span>
                <span id="overlay_song_year"></span>
                <span id="overlay_album"></span>
            </div>

            <div id="error_overlay">
            
            </div>

            <div id="bottom_right_overlay">
                <span id="overlay_poster_source"></span>
                <span id="overlay_live"></span>
                <span id="overlay_time"></span>
            </div>

        </div>

        <datalist id="overlay_track_ticks">
            <?php
            for ($i=0;$i<=100;$i++) {
                ?>
                <option> <?php echo $i; ?> </option>

                <?php
            }
            ?>
        </datalist>

        <div id="tags">

            <?php $tags->MakeForm('FormTags', $FormElementsArray, true); ?>

            <?php
                if ($UserGroup==1)  {
            ?>
            <input type="button" class="myButton" name="submit" id="submit" <?php if($disabled=='yes') echo ' disabled '; ?>
                value="<?php echo __('tag_form_submit'); ?>" onclick="update_tags();">
            <?php
            }
            ?>

        </div>

        <input type="button" class="message" id="message">


        <script type="text/javascript">

            // περνάει στην javascript το ότι το video φορτώθηκε
            var VideoLoaded=true;

        </script>


        <?php

    }

    // Εμφανίζει το βασικό παράθυρο που εμφανίζεται η playlist
    static function showPlaylistWindow ($offset, $step)
    {

        $fields=MyDB::getTableFields('music_tags',array('id'));

        global $mediaKinds;

        $tags = new Page();
        $conn = new MyDB();
        $user = new User();

        $UserGroup=$user->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης

        if ($UserGroup==1)  // Αν ο χρήστης είναι admin
            $disabled='no';
        else $disabled='yes';


        $FormElementsArray = array(
            array('name' => 'artist',
                'fieldtext' => __('tag_artist'),
                'type' => 'text',
                'required' => 'no',
                'maxlength' => '255',
                'disabled' => $disabled,
                'value' => null),
            array('name' => 'album',
                'fieldtext' => __('tag_album'),
                'type' => 'text',
                'required' => 'no',
                'maxlength' => '255',
                'disabled' => $disabled,
                'value' => null),
            array('name' => 'genre',
                'fieldtext' => __('tag_genre'),
                'type' => 'text',
                'required' => 'no',
                'maxlength' => '20',
                'disabled' => $disabled,
                'value' => null),
            array('name' => 'year',
                'fieldtext' => __('tag_year'),
                'type' => 'number',
                'required' => 'no',
                'disabled' => $disabled,
                'value' => null),
            array('name' => 'live',
                'fieldtext' => __('tag_live'),
                'type' => 'select',
                'options' => array(
                    array('value' => '0', 'name' => __('tag_live_official')),
                    array('value' => '1', 'name' => __('tag_live_live'))
                ),
                'required' => 'no',
                'maxlength' => '1',
                'disabled' => $disabled,
                'value' => null),
            array('name' => 'rating',
                'fieldtext' => __('tag_rating'),
                'type' => 'range',
                'required' => 'no',
                'maxlength' => '5',
                'min' => '0',
                'max' => '5',
                'step' => '1',
                'ticks' => array(0,1,2,3,4,5),
                'disabled' => $disabled,
                'value' => '0')



        );

        ?>

        <div id="progress"></div>


<!--        Εμφάνιση του παραθύρου για edit tags-->
        <div id="editTag">

            <?php $tags->MakeForm('FormMassiveTags', $FormElementsArray, true); ?>
            
                <div id="myImage"></div>
                
                <input type="file" name="uploadFile" id="uploadFile" accept='image/*' onchange="readImage(this.files);">
           
                <div id="editTagButtons">
                    <input type="button" class="myButton" name="submit" id="submit"
                           value="<?php echo __('tag_form_submit'); ?>" onclick="editFiles();">

                    <input type="button" class="myButton" name="clearEdit" id="clearEdit" value="<?php echo __('search_text_clear'); ?>" onclick="resetFormMassiveTags();">
                
                    <input type="button" class="myButton" name="cancelEdit" id="cancelEdit" value="<?php echo __('search_text_cancel'); ?>" onclick="cancelTheEdit();">
                </div>

        </div>


            <div id="ChooseMediaKind">
                <select name="mediakind" id="mediakind" onchange="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>, true);">
                    <option value="">
                        All
                    </option>
                    <?php
                    foreach ($mediaKinds as $kind) {
                        ?>
                        <option value="<?php echo $kind; ?>">
                            <?php echo $kind; ?>
                        </option>

                        <?php
                    }
                    ?>
                </select>
            </div>

            <input type="button" id="searchClick" onclick="displaySearchWindow();" title="<?php echo __('search_text_search'); ?>" >

            <div id="ChoosePlaylist">
                <form id="formChoosePlaylist">
                    <select name="playlist" id="playlist" >
                        <option value="">
                            <?php echo __('choose_playlist'); ?>
                        </option>
                        <?php
    
                        $userID=$user->getUserID($conn->getSession('username'));      // Επιστρέφει το id του user με username στο session
                        // H λίστα με τις manual playlists
                        $manualPlaylists = MyDB::getTableArray('manual_playlists', 'id, playlist_name', 'user_id=?', array($userID), null, null, null);
    
                        foreach ($manualPlaylists as $playlist) {
                            ?>
                            <option value="<?php echo $playlist['id']; ?>">
                                <?php echo  $playlist['playlist_name']; ?>
                            </option>
    
                            <?php
                        }
                        ?>
                    </select>
                </form>
            </div>

            <input type="button" id="playPlaylist" onclick="playPlaylist();" title="<?php echo __('play_file'); ?>">
            <input type="button" id="insertPlaylistClick" onclick="displayInsertPlaylistWindow();" title="<?php echo __('create_playlist'); ?>">
            <input type="button" id="deletePlaylistClick" onclick="deletePlaylist();" title="<?php echo __('delete_playlist'); ?>">

            <?php Page::getHelp('help_manual_playlists'); ?>
        
            <div id="insertPlaylistWindow">
                <form id="insertPlaylist" name="insertPlaylist">
                    <input type="text" id="playlistName" name="playlistName">
                    <input type="button" class="myButton PlaylistButton" id="insertPlaylistButton" name="insertPlaylistButton" onclick="createPlaylist();"
                           value="<?php echo __('create_playlist'); ?>">
                    <input type="button" class="myButton" name="cancelPlaylist" id="cancelPlaylist" value="<?php echo __('search_text_cancel'); ?>" onclick="cancelCreatePlaylist();">
                </form>
            </div>


            <div id="someTools">
                <?php
                if ($UserGroup==1) {
                    ?>
                    <input type="button" class="myButton" name="sendToJukebox" id="sendToJukebox"
                           value="<?php echo __('send_to_jukebox'); ?>" onclick="sendToJukeboxList();">
                    <?php
                }
                ?>
            </div>

            <?php
                if($_SESSION['PlaylistCounter']==0) {
            ?>

                <div id="search">
                    <form id="SearchForm" name="SearchForm">
                        <?php

                        for($counter=1;$counter<6;$counter++) {

                        ?>
                        <div id="searchRow<?php echo $counter; ?>">
                            <label for="search_field<?php echo $counter; ?>">
                                <select class="search_field" name="search_field<?php echo $counter; ?>" id="search_field<?php echo $counter; ?>">
                                    <?php
                                    foreach ($fields as $field) {
                                        ?>
                                        <option value="<?php echo $field; ?>">
                                            <?php
                                                switch ($field) {
                                                    case 'song_name': echo __('tag_title'); break;
                                                    case 'artist': echo __('tag_artist'); break;
                                                    case 'genre': echo __('tag_genre'); break;
                                                    case 'date_added': echo __('tag_date_added'); break;
                                                    case 'play_count': echo __('tag_play_count'); break;
                                                    case 'date_last_played': echo __('tag_date_played'); break;
                                                    case 'rating': echo __('tag_rating'); break;
                                                    case 'album': echo __('tag_album'); break;
                                                    case 'video_height': echo __('tag_video_height'); break;
                                                    case 'filesize': echo __('tag_filesize'); break;
                                                    case 'video_width': echo __('tag_video_width'); break;
                                                    case 'track_time': echo __('tag_track_time'); break;
                                                    case 'song_year': echo __('tag_year'); break;
                                                    case 'live': echo __('tag_live'); break;
                                                    case 'album_artwork_id': echo __('tag_album_artwork_id'); break;
                                                }
                                            ?>
                                        </option>

                                        <?php
                                    }
                                    ?>
                                </select>
                            </label>

                            <select class="search_equality" name="search_equality<?php echo $counter; ?>" id="search_equality<?php echo $counter; ?>">

                                <option value="equal">
                                    <?php echo __('search_equal'); ?>
                                </option>

                                <option value="greater">
                                    <?php echo __('search_greater'); ?>
                                </option>

                                <option value="less">
                                    <?php echo __('search_less'); ?>
                                </option>


                            </select>

                            <label for="search_text<?php echo $counter; ?>">
                                <input type="text" name="search_text<?php echo $counter; ?>" id="search_text<?php echo $counter; ?>">
                            </label>

                            <select class="search_operator" name="search_operator<?php echo $counter; ?>" id="search_operator<?php echo $counter; ?>">

                                    <option value="OR">
                                        <?php echo __('search_or'); ?>
                                    </option>

                                    <option value="AND">
                                        <?php echo __('search_and'); ?>
                                    </option>

                            </select>
                        </div>

                        <?php
                        }
                        ?>

                        <div id="searchButtons">
                            <input type="button" class="myButton" name="searching" id="searching" 
                                   value="<?php echo __('search_text_search'); ?>" onclick="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>, true);">

                            <input type="button" class="myButton" name="duplicates" id="duplicates" 
                                   value="<?php echo __('search_text_duplicates'); ?>" onclick="findDuplicates(0,<?php echo PLAYLIST_LIMIT; ?>, true);">

                            <input type="button" class="myButton" name="playedQueue" id="playedQueue" 
                                   value="<?php echo __('search_text_played_queue'); ?>" onclick="loadPlayedQueuePlaylist();">

                            <input type="button" class="myButton" name="clearSearch" id="clearSearch" 
                                   value="<?php echo __('search_text_clear'); ?>" onclick="reset();">

                            <input type="button" class="myButton" name="cancelSearch" id="cancelSearch" 
                                   value="<?php echo __('search_text_cancel'); ?>" onclick="cancelTheSearch();" >
                        </div>
                    </form>
                </div>

            <?php
            }
            else {
            ?>
                <div id="search"></div>
            <?php
            }
            ?>


            <script type="text/javascript">

                // περνάει στην javascript τα options των αντίστοιχων select
                var liveOptions = <?php echo json_encode([__('tag_live_official'),__('tag_live_live')]); ?>;

                var ratingOptions = <?php echo json_encode([0,1,2,3,4,5]); ?>;

            </script>

            <?php

            $UserGroupID = $user->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης
           
            if($UserGroupID==1) {
                ?>
                <div id="editButtons">
                    <input type="button" class="delete_button playlist_button_img"
                           title="<?php echo __('delete_file'); ?>"
                           onclick="deleteFile(0);">
                    <input type="button" class="edit_button playlist_button_img"
                           title="<?php echo __('edit_file'); ?>"
                           onclick="openMassiveTagsWindow();" >
                    <input type="button" class="export_button playlist_button_img"
                           title="<?php echo __('export_playlist'); ?>"
                           onclick="exportPlaylist();" >

                </div>

                <?php
            }
            ?>


        <div id="playlist_container">

            <?php
                if($_SESSION['PlaylistCounter']==0) {
                    $_SESSION['condition']=null;   // Μηδενίζει το τρέχον search query
                    $_SESSION['arrayParams']=null;
                    OWMPElements::getPlaylist(null,$offset,$step,null,null,null,null,false);
                }
                else {
                    ?>

                        <div id="playlist_content"></div>

                    <?php
                }

            ?>



        </div>

        <?php



    }

    // Εμφανίζει την οθόνη του configuration
    static function showConfiguration ()
    {

        $conn = new MyDB();
        $user = new User();

        $UserGroup=$user->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης

        ?>
        <h2><?php echo __('nav_item_2'); ?></h2>


        <?php

        if($UserGroup==1) {  // Αν ο χρήστης είναι admin
            ?>
            <details>
                <summary><?php echo __('settings_options'); Page::getHelp('help_options')?></summary>
                <?php OWMPElements::getOptionsInFormFields() ?>
            </details>



            <?php
        }
        ?>

        <details>
            <summary><?php echo __('settings_users'); ?></summary>
            <?php OWMPElements::getUsersInFormFields() ?>
        </details>





        <div id="error_container">
            <div id="alert_error"></div>
        </div>

        <script type="text/javascript">

            var error1='<?php echo __('user_error1'); ?>';
            var error2='<?php echo __('user_error2'); ?>';

        </script>

        <?php

    }

    // εμφάνιση της οθόνης συγχρονισμού
    static function showSynchronization () {

        OWMPElements::displayBrowsePath(); // Εμφάνιση του παραθύρου για επιλογή path

        ?>
            <details>
                <summary> <?php echo __('settings_paths'); Page::getHelp('help_paths'); ?> </summary>
                <?php OWMPElements::getPathsInFormFields(); ?>
            </details>

            <details>
                <summary> <?php echo __('download_paths'); Page::getHelp('help_download_paths'); ?> </summary>
                <?php OWMPElements::getDownloadPaths(); ?>
            </details>

            <details>
                <summary> <?php echo __('sync_jobs'); ?> </summary>
                <?php OWMPElements::getSyncJobsButtons(); ?>
            </details>

            <details>
                <summary> <?php echo __('youtube_download'); Page::getHelp('help_youtube'); ?> </summary>
                <?php
                    OWMPElements::displayYoutubeDownloadElements(); // Εμφανίζει τις επιλογές για το youtube
                ?>
            </details>

            <details>
                <summary> <?php echo __('requirements_check'); ?> </summary>
                <?php OWMPElements::checkRequirements();  // Εμφανίζει τους ελέγχους για τα requirements ?>
            </details>

            <p>
                <li> <?php echo __('help_samba_sharing_title'); Page::getHelp('help_samba_sharing'); ?> </li>
                <li> <?php echo __('help_apache_title'); Page::getHelp('help_apache'); ?> </li>
                <li> <?php echo __('help_itunes_sync_title'); Page::getHelp('help_itunes_sync'); ?> </li>
                <li> <?php echo __('help_alac_title'); Page::getHelp('help_alac'); ?> </li>
            </p>

            
            <div id="SyncDetails">
                <div id="progress"></div>
            </div>

            <div id="error_container">
                <div id="alert_error"></div>
            </div>
            

            <script type="text/javascript">

                checkProcessAlive();
                checkTheFocus('syncForm');

            </script>


            <?php
    }

    // Εμφάνιση των logs
    static function showLogs ()
    {
        ?>
        <h2><?php echo __('nav_item_4'); ?></h2>
        <?php

        $conn = new MyDB();
        $conn->CreateConnection();

        $sql = 'SELECT * FROM logs ORDER BY log_date DESC LIMIT 0,100';

        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute();

        echo '<div id=logs>';

        echo '<div class=row>';
        echo '<span class="col logs_id basic">'.__('logs_id').'</span>';
        echo '<span class="col logs_message basic">'.__('logs_message').'</span>';
        echo '<span class="col logs_ip basic">'.__('logs_ip').'</span>';
        echo '<span class="col logs_user basic">'.__('logs_user').'</span>';
        echo '<span class="col logs_date basic">'.__('logs_date').'</span>';
        echo '<span class="col logs_browser basic">'.__('logs_browser').'</span>';
        echo '</div>';


        // Αν ο χρήστης username βρεθεί. Αν υπάρχει δηλαδή στην βάση μας
        while ($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            echo '<div class=row>';
            echo '<span class="col logs_id">' . $item['id'] . '</span>';
            echo '<span class="col logs_message">' . $item['message'] . '</span>';
            echo '<span class="col logs_ip">' . $item['ip'] . '</span>';
            echo '<span class="col logs_user">' . $item['user_name'] . '</span>';
            echo '<span class="col logs_date">' . date('Y-m-d H:i:s', strtotime($item['log_date'])) . '</span>';
            echo '<span class="col logs_browser">' . $item['browser'] . '</span>';
            echo '</div>';

        }

        echo '</div>';

        $stmt->closeCursor();
        $stmt = null;

    }

    // Εμφάνιση της οθόνης βοήθειας
    static function showHelp ()
    {
        ?>
        <h2><?php echo __('nav_item_5'); ?></h2>

        <?php

        $lang = new Language();

        include ('../lang/'.$lang->lang_id().'.help.html');

    }

    
}