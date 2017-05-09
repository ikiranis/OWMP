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
        $conn = new MyDB();
        $user = new User();

        $UserGroup=$user->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης
        $userID=$user->getUserID($conn->getSession('username'));      // Επιστρέφει το id του user με username στο session

        ?>

        <div id="progress"></div>


        <?php

        OWMPElements::displayEditTagsWindow($UserGroup); // Εμφάνιση του παραθύρου για edit tags
        OWMPElements::displayChooseMediaSelect(); // Εμφάνιση του media select
        OWMPElements::displaySleepTimer(); // Εμφάνιση του παραθύρου για επιλογή sleep timer

        ?>

        <input type="button" id="searchClick" onclick="displaySearchWindow();" title="<?php echo __('search_text_search'); ?>" >

        <?php

        OWMPElements::displayChoosePlaylistElements($userID); // Εμφάνιση των στοιχείων επιλογής playlist
        OWMPElements::displayInsertPlaylistWindow(); // Εμφάνιση παραθύρου προσθήκης playlist
        OWMPElements::displaySomeTools($UserGroup); // Εμφάνιση διάφορων εργαλείων
        OWMPElements::displaySearchWindow(); // Εμφάνιση του παραθύρου για αναζήτηση
        OWMPElements::displayEditButtons($UserGroup); // Εμφάνιση των edit buttons
        OWMPElements::displayPlaylistContainer($offset,$step); // Εμφάνιση του playlist container
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