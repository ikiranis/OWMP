<?php
/**
 *
 * File: OWMPElements.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 28/04/17
 * Time: 21:31
 *
 * Λεπτομερέστερες μέθοδοι που επεκτείνουν την βασική κλάση OWMP
 *
 */

namespace apps4net\parrot\app;

use apps4net\framework\MyDB;
use apps4net\framework\User;
use apps4net\framework\Utilities;
use apps4net\framework\Page;
use apps4net\framework\FilesIO;
use apps4net\framework\Progress;


class OWMPElements extends OWMP
{
    public $checkVideoFileUpload;
    public $checkAudioFileUpload;

    /**
     * Εμφανίζει την μπάρα με τα controls
     *
     * @param $element {string} To element στο οποίο θα εμφανίσει τα controls
     * @param $fullscreen {boolean} True για εμφάνιση σε fullscreen
     */
    static function displayControls($element, $fullscreen)
    {
        ?>

        <div id="<?php echo $element; ?>" <?php if($fullscreen) echo 'class="bgc10 c2"'; ?>>

            <span class="<?php if($fullscreen) echo 'mdi mdi-skip-previous mdi-light mdi-36px hasCursorPointer'; else echo 'mdi mdi-skip-previous mdi-dark mdi-36px hasCursorPointer'; ?>"
                   title="<?php echo __('previous_song'); ?>"
                   onclick="prevSong();">
            </span>

            <span class="pause_play_button <?php if($fullscreen) echo 'mdi mdi-pause mdi-light mdi-36px hasCursorPointer'; else echo 'mdi mdi-pause mdi-dark mdi-36px hasCursorPointer'; ?>"
                   title="<?php echo __('play_file'); ?>"
                   onclick="playSong();">
            </span>

            <span class="<?php if($fullscreen) echo 'mdi mdi-skip-next mdi-light mdi-36px hasCursorPointer'; else echo 'mdi mdi-skip-next mdi-dark mdi-36px hasCursorPointer'; ?>"
                   title="<?php echo __('next_song'); ?>"
                   onclick="nextSong();">
            </span>

            <span class="<?php if($fullscreen) echo 'mdi mdi-fullscreen-exit mdi-light mdi-36px hasCursorPointer'; else echo 'mdi mdi-fullscreen mdi-dark mdi-36px hasCursorPointer'; ?>"
                   title="<?php echo __('toggle_fullscreen'); ?>"
                   onclick="toggleFullscreen();">
            </span>


            <?php

            if($fullscreen) { // Αν είναι σε fullscreen
                ?>
                <span class="gif_button fullscreen_button_img hasCursorPointer"
                       title="<?php echo __('toggle_giphy'); ?>"
                       onclick="giphyToggle();">
                </span>

                <span class="mdi mdi-information-outline mdi-light mdi-36px hasCursorPointer"
                       title="<?php echo __('toggle_overlay'); ?>"
                       onclick="interfaceToggle();">
                </span>

                <?php
            } else { // Αν δεν είναι σε fullscreen
                ?>
                <span class="shuffle_button mdi mdi-shuffle mdi-dark mdi-36px hasCursorPointer"
                       title="<?php echo __('toggle_shuffle'); ?>"
                       onclick="toggleShuffle();">
                </span>

                <span class="lower_bitrate mdi mdi-priority-low mdi-dark mdi-36px hasCursorPointer"
                       title="<?php echo __('toggle_lower_bitrate'); ?>"
                       onclick="toggleLowerBitrate();">
                </span>

                <?php
            }

            ?>
        </div>

        <?php
    }

    /**
     * Display fullscreen overlay elements
     */
    static function displayFullscreenOverlayElements()
    {
        ?>

        <div id="overlay" class="fixed-top row w-100 h-100 no-gutters" ondblclick="displayFullscreenControls();">

            <div class="row w-100 fixed-top no-gutters">

                <div id="overlay_rating" class="col-lg-2 col-3 text-left px-3 my-auto">

                </div>

                <div class="col-lg-8 col-6 w-100 text-white text-center row no-gutters">
                    <span id="jsOverlayTrackTime" class="col-lg-1 col-4 my-auto">00:00</span>
                    <input type=range class="o-trackTime--overlay__range col-lg-10 col-4 my-auto" min="0" max="100"
                           list="overlay_track_ticks" value="0" oninput="controlTrack();">
                    <span id="jsOverlayTotalTrackTime" class="col-lg-1 col-4 my-auto">00:00</span>
                </div>

                <div id="overlay_play_count" class="col-lg-2 col-3 text-right w-100 text-white px-3 my-auto">

                </div>

            </div>

            <div id="overlay_volume" class="row h-100 fixed-top no-gutters" >
                <div id="overlay_volume_text" class="col-sm-2 col-6 ml-auto mr-auto text-white text-center px-2 py-2">

                </div>
            </div>


            <div id="bottom_overlay" class="row w-100 fixed-bottom py-1 px-3 no-gutters">

                <div class="col-lg-6 col-8 text-left text-white row no-gutters">
                    <span id="overlay_song_name" class="col-12"></span>
                    <span id="overlay_artist" class="col-12"></span>
                    <span id="overlay_song_year" class="col-12"></span>
                    <span id="overlay_album" class="col-12"></span>
                </div>

                <div class="col-lg-6 col-4 text-right text-white row no-gutters small">
                    <span id="overlay_poster_source" class="col-12"></span>
                    <span id="overlay_live" class="col-12"></span>
                    <span id="overlay_time" class="col-12"></span>
                </div>

            </div>


            <div id="error_overlay">

            </div>

        </div>

        <?php
    }

    /**
     * Display tags form
     *
     * @param $disabled
     */
    static function displayTagsForm($disabled)
    {
        $readonly = ($disabled) ? 'readonly' : null;
        $disabled = ($disabled) ? 'disabled' : null;

        ?>

        <form class="validate-form" id="FormTags" name="FormTags">

            <input type="hidden" id="songID" name="songID" >

            <div class="form-group my-1">
                <label for="title" class="sr-only"><?php echo __('tag_title'); ?></label>
                <input type="text" class="form-control" id="title" name="title" placeholder="<?php echo __('tag_title'); ?>"
                <?php echo $disabled . ' ' . $readonly; ?> maxlength="255">
            </div>

            <div class="form-group my-1">
                <label for="artist" class="sr-only"><?php echo __('tag_artist'); ?></label>
                <input type="text" class="form-control form-control-sm" id="artist" name="artist" placeholder="<?php echo __('tag_artist'); ?>"
                    <?php echo $disabled . ' ' . $readonly; ?> maxlength="255">
            </div>

            <div class="form-group my-1">
                <label for="album" class="sr-only"><?php echo __('tag_album'); ?></label>
                <input type="text" class="form-control form-control-sm" id="album" name="album" placeholder="<?php echo __('tag_album'); ?>"
                    <?php echo $disabled . ' ' . $readonly; ?> maxlength="255">
            </div>

            <div class="row my-1">
                <div class="form-group col-xl-4 col-sm-4 col-md-12 w-100 my-1">
                    <label for="genre" class="sr-only"><?php echo __('tag_genre'); ?></label>
                    <input type="text" class="form-control form-control-sm" id="genre" name="genre" placeholder="<?php echo __('tag_genre'); ?>"
                        <?php echo $disabled . ' ' . $readonly; ?> maxlength="20">
                </div>

                <div class="form-group col-xl-4 col-sm-4 col-md-12 w-100 my-1">
                    <label for="year" class="sr-only"><?php echo __('tag_year'); ?></label>
                    <input type="number" class="form-control form-control-sm" id="year" name="year" placeholder="<?php echo __('tag_year'); ?>"
                        <?php echo $disabled . ' ' . $readonly; ?>>
                </div>

                <div class="form-group col-xl-4 col-sm-4 col-md-12 w-100 my-1">
                    <label for="live" class="sr-only"><?php echo __('tag_live'); ?></label>
                    <select class="form-control form-control-sm" id="live" name="live" <?php echo $disabled . ' ' . $readonly; ?>>
                        <option value="0"><?php echo __('tag_live_official'); ?></option>
                        <option value="1"><?php echo __('tag_live_live'); ?></option>
                    </select>
                </div>
            </div>

            <div id="rating_div">
                <label for="rating" class="sr-only"><?php echo __('tag_rating'); ?></label>

                <input type="range" id="rating" name="rating" oninput="printValue(rating, rating_output);"
                       maxlength="5" max="5" min="0" list="rating_ticks">

                <output for="rating" id="rating_output">0</output>

                <datalist id="rating_ticks">
                    <?php
                        for($i=0; $i<6; $i++) {
                            ?>
                            <option><?php echo $i; ?></option>
                            <?php
                        }
                    ?>
                </datalist>
            </div>

            <details>
                <summary>
                    <?php echo __('tag_details'); ?>
                </summary>

                <div class="form-group my-1">
                    <label for="play_count" class="sr-only"><?php echo __('tag_play_count'); ?></label>
                    <input type="number" class="form-control form-control-sm" id="play_count" name="play_count" placeholder="<?php echo __('tag_play_count'); ?>"
                        <?php echo $disabled; ?> readonly>
                </div>

                <div class="form-group my-1">
                    <label for="date_added" class="sr-only"><?php echo __('tag_date_added'); ?></label>
                    <input type="text" class="form-control form-control-sm" id="date_added" name="date_added" placeholder="<?php echo __('tag_date_added'); ?>"
                        <?php echo $disabled; ?> maxlength="20" readonly>
                </div>

                <div class="form-group my-1">
                    <label for="date_played" class="sr-only"><?php echo __('tag_date_played'); ?></label>
                    <input type="text" class="form-control form-control-sm" id="date_played" name="date_played" placeholder="<?php echo __('tag_date_played'); ?>"
                        <?php echo $disabled; ?> maxlength="20" readonly>
                </div>

                <div class="form-group my-1">
                    <label for="path_filename" class="sr-only"><?php echo __('tag_path_filename'); ?></label>
                    <input type="text" class="form-control form-control-sm" id="path_filename" name="path_filename" placeholder="<?php echo __('tag_path_filename'); ?>"
                        <?php echo $disabled; ?> maxlength="255" readonly>
                </div>

            </details>



        </form>


        <?php
    }


    // Εμφάνιση των εγγραφών των options σε μορφή form fields για editing
    static function getOptionsInFormFields ()
    {
        $conn = new MyDB();

        $options=$conn->getTableArray('options', null, 'setting=?', array(1), null, null, null);  // Παίρνει τα δεδομένα του πίνακα options σε array


        ?>

        <div class="ListTable">

            <?php

            foreach ($options as $option)
            {
                ?>
                <div class="OptionsRow" id="OptionID<?php echo $option['option_id']; ?>">
                    <form class="table_form options_form" id="options_formID<?php echo $option['option_id']; ?>">
                    <span class="ListColumn"><input class="input_field" disabled
                                                    placeholder="<?php echo __('options_option'); ?>"
                                                    type="text" name="option_name" value="<?php echo $option['option_name']; ?>"></span>
                        <span class="ListColumn"><input class="input_field"
                                                        placeholder="<?php echo __('options_value'); ?>"
                                                        title="<?php echo __('valid_option'); ?>"

                                                        maxlength="255" required type="<?php if($option['encrypt']==0) echo 'text'; else echo 'password'; ?>" name="option_value" value="<?php if($option['encrypt']==0) echo $option['option_value']; ?>"></span>

                        <input type="button" class="update_button button_img" name="update_option" title="<?php echo __('update_row'); ?>" onclick="updateOption(<?php echo $option['option_id']; ?>);"">

                        <input type="button" class="message" id="messageOptionID<?php echo $option['option_id']; ?>">
                    </form>
                </div>
                <?php
            }
            ?>



        </div>

        <?php


    }

    // Εμφάνιση των εγγραφών των χρηστών σε μορφή form fields για editing
    static function getUsersInFormFields ()
    {
        $conn = new MyDB();
        $user = new User();
        MyDB::createConnection();

        $UserGroupID=$user->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης
        $userID=$user->getUserID($conn->getSession('username'));      // Επιστρέφει το id του user με username στο session

        global $UserGroups;

        if($UserGroupID==1)
            $sql = 'SELECT * FROM user JOIN user_details on user.user_id=user_details.user_id';
        else $sql = 'SELECT * FROM user JOIN user_details on user.user_id=user_details.user_id WHERE user.user_id=?';

        $stmt = MyDB::$conn->prepare($sql);

        $counter=1;

        if($UserGroupID==1)
            $stmt->execute();
        else $stmt->execute(array($userID));

        ?>
        <div class="ListTable UsersList">

            <?php

            while($item=$stmt->fetch(\PDO::FETCH_ASSOC))
            {
                ?>
                <div class="UsersRow" id="UserID<?php echo $item['user_id']; ?>">
                    <form class="table_form users_form" id="users_formID<?php echo $item['user_id']; ?>">

                        <span class="ListColumn">
                            <input class="input_field"
                               placeholder="<?php echo __('users_username'); ?>"
                               title="<?php echo __('valid_username'); ?>"
                               pattern="^[a-zA-Z][a-zA-Z0-9-_\.]{4,15}$"
                               maxlength="15" required type="text" name="theUsername" value="<?php echo $item['username']; ?>">
                        </span>

                        <span class="ListColumn">
                            <input class="input_field"
                                   placeholder="<?php echo __('users_email'); ?>"
                                   title="<?php echo __('valid_email'); ?>"
                                   maxlength="50" required type="email" name="email" value="<?php echo $item['email']; ?>">
                        </span>

                        <span class="ListColumn">
                            <input class="input_field"
                                   placeholder="<?php echo __('users_password'); ?>"
                                   title="<?php echo __('valid_register_password'); ?>"
                                   pattern='(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}'
                                   maxlength="15"  type="password" id="password<?php echo $item['user_id']; ?>" name="password" value="">
                        </span>

                        <span class="ListColumn">
                            <input class="input_field"
                                   placeholder="<?php echo __('users_repeat_password'); ?>"
                                   title="<?php echo __('valid_register_repeat_password'); ?>"
                                   pattern='(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}'
                                   maxlength="15"  type="password" id="<?php echo $item['user_id']; ?>" name="repeat_password" value="">
                        </span>

                        <span class="ListColumn">
                            <select class="input_field" name="usergroup" <?php if($UserGroupID!=1) echo ' disabled=disabled'; ?> >
                                <?php
                                foreach ($UserGroups as $UserGroup) {
                                    ?>
                                    <option value="<?php echo $UserGroup['id']; ?>"
                                        <?php if($UserGroup['id']==$item['user_group']) echo 'selected=selected'; ?>>
                                        <?php echo $UserGroup['group_name']; ?>
                                    </option>

                                    <?php
                                }
                                ?>
                            </select>
                        </span>

                        <span class="ListColumn">
                            <input class="input_field"
                                   placeholder="<?php echo __('users_firstname'); ?>"
                                   title="<?php echo __('valid_fname'); ?>"
                                   pattern='^[a-zA-ZΆ-Ϋά-ώ][a-zA-ZΆ-Ϋά-ώ0-9-_\.]{2,15}$'
                                   maxlength="15"  type="text" name="fname" value="<?php echo $item['fname']; ?>">
                        </span>

                        <span class="ListColumn">
                            <input class="input_field"
                                   placeholder="<?php echo __('users_lastname'); ?>"
                                   title="<?php echo __('valid_lname'); ?>"
                                   pattern='^[a-zA-ZΆ-Ϋά-ώ][a-zA-ZΆ-Ϋά-ώ0-9-_\.]{2,25}$'
                                   maxlength="25"  type="text" name="lname" value="<?php echo $item['lname']; ?>">
                        </span>

                        <input type="button" class="update_button button_img" name="update_user" title="<?php echo __('update_row'); ?>" onclick="updateUser(<?php echo $item['user_id']; ?>);"">

                        <input type="button" class="delete_button button_img <?php if($counter==1) echo 'dontDelete'; ?>" name="delete_user" title="<?php echo __('delete_row'); ?>" onclick="deleteUser(<?php echo $item['user_id']; ?>);"">

                        <input type="button" class="message" id="messageUserID<?php echo $item['user_id']; ?>">

                    </form>
                </div>
                <?php
                $counter++;
            }
            ?>

        </div>

        <?php
        if($UserGroupID==1) {  // Αν είναι admin ο user εμφάνισε κουμπί για προσθήκη νέου user
            ?>

            <input type="button" class="myButton insert_row" name="insert_user" onclick="insertUser();" value="<?php echo __('insert_row'); ?>">
            <?php
        }
        ?>

        <?php
        $stmt->closeCursor();
        $stmt = null;

    }

    // Παράθυρο αναζήτησης διαδρομής
    static function displayBrowsePath()
    {
        ?>

        <div id="browsePathWindow">
            <div id="displayPaths"></div>
            <div id="chosenPath">
                <span id="chosenPathText"></span>
            </div>
            <div id="browsePathButtons">
                <input type="button" class="myButton" name="submit" id="submit"
                       value="<?php echo __('import_path'); ?>" onclick="importPath();">

                <input type="button" class="myButton" name="cancelBrowse" id="cancelBrowse"
                       value="<?php echo __('search_text_cancel'); ?>" onclick="cancelTheBrowse();">
            </div>
        </div>

        <?php
    }

    // Εμφανίζει τα input fields για τα paths
    static function getPathsInFormFields()
    {
        $conn = new MyDB();
        global $mediaKinds;


        $paths=$conn->getTableArray('paths', null, null, null, null, null, null);  // Παίρνει τα δεδομένα του πίνακα paths σε array

        if(empty($paths)) {  // Αν δεν επιστρέψει κανένα αποτέλεσμα, σετάρουμε εμείς μια πρώτη γραμμή στο array
            $paths[]=array('id'=>'0', 'file_path'=>'', 'kind'=>'', 'main'=>'');
        }

        $counter=1;


        ?>
        <div>

            <?php



            foreach($paths as $path)
            {
                ?>
                    <div  id="PathID<?php echo $path['id']; ?>">
                        <form class="row no-gutters my-1" id="paths_formID<?php echo $path['id']; ?>">

                            <div class="form-group my-1 w-100 col-5 px-1">
                                <label for="file_path" class="sr-only"><?php echo __('paths_file_path'); ?></label>
                                <input type="text" class="form-control form-control-sm" id="file_path" name="file_path"
                                       maxlength="255" required placeholder="<?php echo __('paths_file_path'); ?>"
                                       value="<?php echo $path['file_path']; ?>" onclick="displayBrowsePath('paths_formID<?php echo $path['id']; ?>');">
                            </div>

                            <div class="form-group my-1 w-100 col-5 px-1">
                                <label for="kind" class="sr-only">Media Kind</label>
                                <select class="form-control form-control-sm" id="kind" name="kind">
                                    <?php
                                    foreach ($mediaKinds as $mediaKind) {
                                        ?>
                                        <option value="<?php echo $mediaKind; ?>"
                                            <?php if($mediaKind==$path['kind']) echo 'selected=selected'; ?>>
                                            <?php echo $mediaKind ?>
                                        </option>

                                        <?php
                                    }
                                    ?>
                                </select>
                             </div>


                            <div class="col-2 px-1 text-right">
                                <span class="mdi mdi-checkbox-marked-circle mdi-24px hasCursorPointer" id="update_path"
                                      title="<?php echo __('update_row'); ?>" onclick="updatePath(<?php echo $path['id']; ?>);">
                                </span>

                                <span class="mdi mdi-delete mdi-24px hasCursorPointer <?php if($counter==1) echo 'dontDelete'; ?>"
                                      id="delete_path" title="<?php echo __('delete_row'); ?>" onclick="deletePath(<?php echo $path['id']; ?>);">
                                </span>

                                <input type="button" class="message" id="messagePathID<?php echo $path['id']; ?>">
                            </div>


                        </form>
                    </div>

                <?php
                $counter++;
            }
            ?>

        </div>

        <div class="row">
            <input type="button" class="btn btn-warning btn-sm ml-auto mr-auto w-25" name="insert_path" onclick="insertPath();" value="<?php echo __('insert_row'); ?>">
        </div>

        <?php
    }

    // Εμφανίζει τα input για επιλογή των διάφορων download paths
    static function getDownloadPaths()
    {

        // Τα αποτελέσματα του download_paths σε array
        $downloadPathsArray = MyDB::getTableArray('download_paths', null, null, null, null, null, null);

        ?>
        <div>

            <?php

            foreach ($downloadPathsArray as $item) {
                ?>

                <div id="<?php echo $item['path_name']; ?>">
                    <form class="row no-gutters my-1" id="form<?php echo $item['path_name']; ?>">

                        <div class="form-group my-1 w-100 col-5 px-1">
                            <label for="option_name" class="sr-only"><?php echo $item['path_name']; ?></label>
                            <input type="text" class="form-control form-control-sm" id="option_name" name="option_name"
                                   placeholder="<?php echo __('options_option'); ?>" value="<?php echo $item['path_name']; ?>"
                                   disabled>
                        </div>

                        <div class="form-group my-1 w-100 col-5 px-1">
                            <label for="file_path" class="sr-only"><?php echo $item['file_path']; ?></label>
                            <input type="text" class="form-control form-control-sm" id="file_path" name="file_path"
                                   placeholder="<?php echo __('paths_file_path'); ?>" maxlength="255" required
                                   value="<?php echo $item['file_path']; ?>"
                                   onclick="displayBrowsePath('form<?php echo $item['path_name']; ?>');">
                        </div>

                        <div class="col-2 px-1 text-right">
                            <span class="mdi mdi-checkbox-marked-circle mdi-24px hasCursorPointer" name="update_path"
                                   title="<?php echo __('update_row'); ?>"
                                   onclick="updateDownloadPath('<?php echo $item['path_name']; ?>');">
                            </span>

                            <input type="button" class="message" id="message_<?php echo $item['path_name']; ?>">
                        </div>

                    </form>
                </div>

                <?php
            }
            ?>

        </div>

        <?php
    }

    // Έλεγχος αν υπάρχουν κι έχουν δικαιώματα τα directories για upload από το youtube
    public function checkYoutubeUploadDirectories()
    {
        // Παράγει το file path από το έτος και τον μήνα
        $fileDir = Utilities::getPathFromYearAndMonth();

        if(VIDEO_FILE_UPLOAD) {
            $uploadDir=VIDEO_FILE_UPLOAD . $fileDir;

            $this->checkVideoFileUpload = FilesIO::createDirectory($uploadDir);

            if(!$this->checkVideoFileUpload['result']) {
                echo $this->checkVideoFileUpload['message'];
            }
        } else {
            echo '<p class="isFail">'.__('no_main_music_video_path').'</p>';
        }

        if(MUSIC_FILE_UPLOAD) {
            $uploadDir=MUSIC_FILE_UPLOAD . $fileDir;
            $this->checkAudioFileUpload = FilesIO::createDirectory($uploadDir);

            if(!$this->checkAudioFileUpload['result']) {
                echo $this->checkAudioFileUpload['message'];
            }
        } else {
            echo '<p class="isFail">'.__('no_main_music_path').'</p>';
        }

    }

    // Εμφάνιση των στοιχείων για κατέβασμα από YouTube
    public function displayYoutubeDownloadElements()
    {
        global $mediaKinds;

        // Έλεγχος αν έχει οριστεί κάποιο FILE_UPLOAD αλλιώς να μην ενεργοποιεί το κουμπί του youtube
        if(VIDEO_FILE_UPLOAD || MUSIC_FILE_UPLOAD) {

            // Έλεγχος αν υπάρχουν κι έχουν δικαιώματα τα directories για upload από το youtube
            $this->checkYoutubeUploadDirectories();

            if( $this->checkVideoFileUpload['result'] || $this->checkAudioFileUpload['result'] ) {

                ?>
                <div>
                    <textarea class="o-youTube__textArea" name="o-youTube__textArea"></textarea>

                    <select name="jsMediaKind" class="jsMediaKind">
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

                    <input type="button" class="myButton syncButton" id="jsDownloadYouTube" name="jsDownloadYouTube"
                           onclick="downloadTheYouTube();"
                           value="<?php echo __('sync_youtube'); ?>" >

                    <input type="hidden" id="jsMusicVideoPathOK" value="<?php if(VIDEO_FILE_UPLOAD) echo $this->checkVideoFileUpload['result']; ?>">
                    <input type="hidden" id="jsMusicPathOK" value="<?php if(MUSIC_FILE_UPLOAD) echo $this->checkAudioFileUpload['result']; ?>">

                </div>

                <?php
            }

        }
        else {
            echo '<p>'.__('youtube_error').'</p>';
        }
    }

    // Έλεγχος αν οι φάκελοι υπάρχουν κι έχουν δικαιώματα εγγραφής
    public function checkFoldersPermissions()
    {
        // Το array με τα download paths
        global $downloadPaths;

        foreach ($downloadPaths as $path) {
            if(is_dir($path)) { // Αν υπάρχει ο φάκελος
                if(!is_writable($path)) {  // Αν δεν έχει δικαιώματα εγγραγφής
                    echo '<p class="isFail">ERROR! '.__('cant_write_to_path'). ' '.$path . '. '.__('give_permissions').'</p>';
                }
            } else { // Αν ο φάκελος δεν υπάρχει
                echo '<p class="isFail">'. __('path_does_not_exist') . ': '. $path . '</p>';
            }

        }

    }

    // Έλεγχος και εμφάνιση απαιτήσεων
    public function checkRequirements()
    {
        // TODO να μπουν δυναμικά κείμενα
        ?>

        <p>ffmpeg:
            <?php
            if (Utilities::checkIfLinuxProgramInstalled('ffmpeg')) {
                echo '<span class="isSuccess">Installed</span>';
            } else {
                echo '<span class="isFail">Not Installed</span>';
            }
            ?>
        </p>

        <p>lame:
            <?php
            if (Utilities::checkIfLinuxProgramInstalled('lame')) {
                echo '<span class="isSuccess">Installed</span>';
            } else {
                echo '<span class="isFail">Not Installed</span>';
            }
            ?>
        </p>

        <p>youtube-dl:
            <?php
            if (Utilities::checkIfLinuxProgramInstalled('youtube-dl')) {
                echo '<span class="isSuccess">Installed</span>';
            } else {
                echo '<span class="isFail">Not Installed</span>';
            }
            ?>
        </p>

        <p>GD Library:
            <?php
            // Έλεγχος της GD library για την διαχείριση εικόνων
            if(function_exists('gd_info')) {
                echo '<span class="isSuccess">Installed</span>';
            } else {
                echo '<span class="isFail">Not Installed</span>';
            }
            Page::getHelp('help_GD_library');
            ?>
        </p>


        <p>php-xml Library:
            <?php
            // Έλεγχος της php-xml library για διαχείριση του xml
            if(function_exists('xml_set_object')) {
                echo '<span class="isSuccess">Installed</span>';
            } else {
                echo '<span class="isFail">Not Installed</span>';
            }
            // TODO να κάνω το help για το php-xml
//            Page::getHelp('help_GD_library');
            ?>
        </p>

        <?php
    }

    // Εμφανίζει τα διάφορα κουμπιά συγχρονισμού
    static function getSyncJobsButtons()
    {
        $conn = new MyDB();
        $user = new User();
        MyDB::createConnection();

        global $mediaKinds;

        $UserGroupID=$user->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης

        if($UserGroupID==1) {
            ?>
            <div id="syncButtons">
                <form id="syncForm" name="syncForm">
                    <select name="mediakind" id="mediakind">
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

                    <p>
                        <input type="button" class="myButton syncButton" id="startSync" name="startSync" onclick="startTheSync('sync');"
                               value="<?php echo __('Synchronize'); ?>">
                        <input type="hidden" id="jsGDOK" value="<?php if(function_exists('gd_info')) echo 'true'; else 'false'; ?>">
                        <?php Page::getHelp('help_sync'); ?>
                    </p>

                    <p>
                        <input type="button" class="myButton syncButton" id="startClear" name="startClear" onclick="startTheSync('clear');"
                               value="<?php echo __('sync_clear'); ?>">
                        <?php Page::getHelp('help_clear_db'); ?>
                    </p>

                    <p>
                        <input type="button" class="myButton syncButton" id="startHash" name="startHash" onclick="startTheSync('hash');"
                               value="<?php echo __('sync_hash'); ?>">
                        <?php Page::getHelp('help_hash'); ?>
                    </p>

                    <p>
                        <input type="button" class="myButton syncButton" id="startFileMetadata" name="startFileMetadata" onclick="startTheSync('metadata');"
                               value="<?php echo __('sync_metadata'); ?>">
                        <?php Page::getHelp('help_metadata'); ?>
                    </p>

                    <p>
                        <input type="button" class="myButton syncButton" id="startJsonImport" name="startJsonImport" onclick="startTheSync('json_import');"
                               value="<?php echo __('sync_json'); ?>">
                        <?php Page::getHelp('help_playlist_export'); ?>
                    </p>

                    <p>
                        <input type="button" class="myButton syncButton" id="startCoverConvert" name="startCoverConvert" onclick="startTheSync('coverConvert');"
                               value="<?php echo __('cover_convert'); ?>">
                        <?php Page::getHelp('help_convert_covers'); ?>
                    </p>

                    <!--                    <p>-->
                    <!--                        <input type="button" class="myButton syncButton" id="startUpdate" name="startUpdate" onclick="startTheUpdate();"-->
                    <!--                               value="Update">-->
                    <!--                    </p>-->

                    <p>
                        <input type="button" class="myButton syncButton" id="backupDatabase" name="backupDatabase" onclick="startTheBackup();"
                               value="<?php echo __('start_backup'); ?>">
                        <?php Page::getHelp('help_database_backup'); ?>
                        <input type="checkbox" id="autoDownloadBackupFile" name="autoDownloadBackupFile"> <?php echo __('backup_file_autoload'); ?>
                    </p>

                    <p>
                        <input type="file" name="uploadSQLFile" id="uploadSQLFile" onchange="jsUploadFile(this.files)">

<!--                        <input type="file" name="jsMediaFiles" id="jsMediaFiles"-->
<!--                               accept=".sql"-->
<!--                               onchange="UploadFiles.startUpload();" multiple>-->

                        <input type="button" class="myButton syncButton" id="restoreDatabase" name="restoreDatabase" onclick="restoreTheBackup();"
                               value="<?php echo __('start_restore'); ?>">
                        <?php Page::getHelp('help_database_backup'); ?>
                    </p>

                </form>

            </div>

            <?php
        }
        else {
            echo '<p>'.__('only_for_admin').'</p>';
        }
    }

    /**
     * Επιστρέφει τις διπλές εγγραφές με βάση το hash
     * Δεν χρησιμοποιείται
     *
     * @param $offset {int}
     * @param $step {int}
     * @return mixed
     */
    static function getFilesDuplicates ($offset, $step)
    {
        MyDB::createConnection();

        $sql='SELECT files.id as id, song_name, artist, genre, date_added, play_count, rating, song_year FROM files JOIN music_tags on files.id=music_tags.id WHERE hash IN (SELECT hash FROM OWMP.files GROUP BY hash HAVING count(*) > 1) ORDER BY hash';

        if(isset($offset))
            $sql=$sql.' LIMIT '.$offset.','.$step;

        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute();

        $result=$stmt->fetchAll();

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    /**
     * Δημιουργεί ένα κατάλληλο array ώστε να αντιγραφεί σε προσωρινό table
     * Δεν χρησιμοποιείται
     *
     * @param $arrayToCopy
     * @return array
     */
    static function makePlaylistArrayToCopy($arrayToCopy)
    {
        $counter=0;
        foreach($arrayToCopy as $item) {
            $newArray[]=array('id'=>$counter, 'file_id'=>$item['id']);
            $counter++;
        }

        return $newArray;
    }

    /**
     * Σβήνει ένα αρχείο και την αντίστοιχη εγγραφή στην βάση
     *
     * @param $id {int} Το id του αρχείου για σβύσιμο
     * @return bool
     */
    static function deleteFile($id)
    {
        $conn = new MyDB();

        $file=MyDB::getTableArray('files','*', 'id=?', array($id),null, null, null);   // Παίρνει το συγκεκριμένο αρχείο

        $filesArray=array('path'=>$file[0]['path'],
            'filename'=>$file[0]['filename']);

        $fullPath=DIR_PREFIX.$filesArray['path'].$filesArray['filename'];   // Το full path του αρχείου

        if (file_exists($fullPath)) {  // αν υπάρχει το αρχείο, σβήνει το αρχείο μαζί με την εγγραφή στην βάση
            if (unlink($fullPath)) {
                if($deleteMusicTags=$conn->deleteRowFromTable ('music_tags','id',$id))
                    if($deleteFile = $conn->deleteRowFromTable('files', 'id', $id))
                        $result = true;
                    else $result = false;
            }
        }
        else  {  // Αν δεν υπάρχει το αρχείο σβήνει μόνο την εγγραφή στην βάση
            if($deleteMusicTags=$conn->deleteRowFromTable ('music_tags','id',$id))
                if($deleteFile = $conn->deleteRowFromTable('files', 'id', $id))
                    $result = true;
                else $result = false;
        }

        return $result;
    }

    /**
     * Επιστρέφει τo fullpath από τα files με $id
     *
     * @param $id {int} Το id του αρχείου για επιστροφή
     * @return bool|string
     */
    static function getFullPathFromFileID($id)
    {
        MyDB::createConnection();

        $sql='SELECT path, filename FROM files WHERE id=?';

        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute(array($id));

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC))

            $result=$item['path'].urldecode($item['filename']);

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    /**
     * Upload ενός image κι εισαγωγή στην βάση
     *
     * @param $image {string} Η εικόνα σε string
     * @param $mime {string} Ο τύπος της εικόνας
     * @return bool|mixed Επιστρέφει το id του cover ή false
     */
    static function uploadAlbumImage($image, $mime)
    {
        $conn = new MyDB();

        $hash = SyncFiles::hashString($image); // Δημιουργούμε hash της εικόνας

        if(!$coverArtID = SyncFiles::searchForImageHash($hash)) {  // Ψάχνουμε αν το hash της εικόνας υπάρχει ήδη

            // εγγραφή του image σαν αρχείο σε υποκατάλογο έτους και μήνα
            switch ($mime) {  // το  extension του αρχείου αναλόγως το mime
                case 'image/png':
                    $imageExtension = '.png';
                    break;
                case 'image/jpeg':
                    $imageExtension = '.jpeg';
                    break;
                case 'image/jpg':
                    $imageExtension = '.jpg';
                    break;
                case 'image/gif':
                    $imageExtension = '.gif';
                    break;
            }

            $myYear = date('Y');
            $myMonth = date('m');
            $imageDir = $myYear . '/' . $myMonth . '/';  // O φάκελος που θα γραφτεί το αρχείο
            $timestampFilename = date('YmdHis'); // Το όνομα του αρχείου

            // TODO Check path permissions before starting the files upload
            $checkAlbumCoversDir = FilesIO::createDirectory(ALBUM_COVERS_DIR . $imageDir); // Αν δεν υπάρχει ο φάκελος τον δημιουργούμε
            if(!$checkAlbumCoversDir['result']) {  // Αν είναι false τερματίζουμε την εκτέλεση
                trigger_error($checkAlbumCoversDir['message']);
                exit($checkAlbumCoversDir['message']);
            }

            $file = ALBUM_COVERS_DIR . $imageDir . $timestampFilename . $imageExtension;  // Το πλήρες path που θα γραφτεί το αρχείο

            $success = file_put_contents($file, $image);  // Κάνει την τελική εγγραφή του image σε αρχείο

            if ($success) {  // Αν το αρχείο δημιουργηθεί κανονικά κάνουμε εγγραφή στην βάση

                $sql = 'INSERT INTO album_arts (path, filename, hash) VALUES(?,?,?)';   // Εισάγει στον πίνακα album_arts

                $artsArray = array($imageDir, $timestampFilename.$imageExtension, $hash);

                $coverID=$conn->insertInto($sql, $artsArray); // Παίρνουμε το id της εγγραφής που έγινε

                // GD install http://php.net/manual/en/image.installation.php

                // Αν είναι εγκατεστημένη η GD library στην PHP και αν το image είναι valid
                if(function_exists('gd_info') && self::checkValidImage($file)) {
                    // Δημιουργεί thumbnail, small image και ico
                    self::createSmallerImage($file, 'thumb');
                    self::createSmallerImage($file, 'small');
//                    self::createSmallerImage($file, 'ico');
                } else {
                    trigger_error('error');
                    exit('error');
                }
            }

        } else {
            $coverID=$coverArtID;
        }

        return $coverID;
    }

    static function get_content($URL){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $URL);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * Ελέγχει αν ένα image είναι valid
     *
     * @param $myImage {string} To path της εικόνας
     * @return bool True or false
     */
    static function checkValidImage($myImage)
    {
        $html = VALID_IMAGE_SCRIPT_ADDRESS . '?imagePath='.$myImage;
        $response = @file_get_contents($html, FILE_USE_INCLUDE_PATH);
//        $response = self::get_content($html);

        $decoded = json_decode($response, true);

        if($decoded) {
            foreach ($decoded as $items) {
                $result = $items;
                return $result;
            }
        } else {
            return false;
        }
    }

    /**
     * Αναλόγως το extension επιστρέφει την εικόνα στο $image
     *
     * @param $myImage {string} Το path της εικόνας
     * @return bool|resource Επιστρέφει την εικόνα σαν string ή false
     */
    static function openImage($myImage) {
        $extension = pathinfo($myImage, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = @imagecreatefromjpeg($myImage);
                break;
            case 'gif':
                $image = @imagecreatefromgif($myImage);
                break;
            case 'png':
                $image = @imagecreatefrompng($myImage);
                break;
            default:
                return false;
                break;
        }

        if (!$image) {
            trigger_error('ERROR!');
            return false;
        }

        return $image;
    }

    /**
     * Δημιουργεί μικρότερες εκδόσεις μίας εικόνας. Thumb, small, large.
     *
     * @param $fullpath {string} Το path της εικόνας
     * @param $imageSize {string} "thumb" or "small"
     * @return bool True or False για την επιτυχία
     */
    static function createSmallerImage($fullpath, $imageSize) {
        $imageFilename = pathinfo($fullpath, PATHINFO_BASENAME);  // Το όνομα του αρχείου
        $imagePath = pathinfo($fullpath, PATHINFO_DIRNAME);   // Το path του αρχείου μέσα στο ALBUM_COVERS_DIR
        $extension = pathinfo($fullpath, PATHINFO_EXTENSION);

     // Aνοίγει το image (αν υπάρχει) και το βάζει στο $image
        if(FilesIO::fileExists($fullpath)) {
            if (!$image = self::openImage($fullpath)) {
                return false;
            }
        } else {
            return false;
        }

        // Οι διαστάσεις του αρχικού image
        $oldImageWidth = imagesx($image);
        $oldImageHeight = imagesy($image);


        // Οι νέες διαστάσεις αναλόγως τι έχουμε επιλέξει να κάνει
        switch($imageSize) {
            case 'thumb':
                $newWidth = 50;
                $newHeight = 50;
                $newFilename = 'thumb_'.$imageFilename;
                break;
            case 'small':
                $newWidth = 250;
                $newHeight = 250;
                $newFilename = 'small_'.$imageFilename;
                break;
//            case 'ico':
//                $newWidth = 32;
//                $newHeight = 32;
//                $newFilename = str_replace('.'.$extension, '.ico', $imageFilename);
//                break;
        }

        // Δημιουργεί το image με νέες διαστάσεις
        $newImage = ImageCreateTrueColor($newWidth, $newHeight);
        imagecopyResampled ($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $oldImageWidth, $oldImageHeight);

        // Σώζει το image
//        if($imageSize!=='ico') {
            if (imagejpeg($newImage, $imagePath . '/' . $newFilename)) {
                $result = true;
            } else {
                $result = false;
            }
//        } else {
////            trigger_error($imagePath . '/' . $newFilename);
//            if (imagepng($newImage, $imagePath . '/' . $newFilename)) {
//
//                $result = true;
//            } else {
//                $result = false;
//            }
//        }

        imagedestroy($image); //  clean up image storage
        imagedestroy($newImage);

        return $result;

    }

    /**
     * Επιστρέφει το fullpath του album cover για το $id
     *
     * @param $id {int} Το id του cover image
     * @param $imageSize {string} "thumb" or "small" or "big"
     * @return bool|string To fullpath ή false στην αποτυχία
     */
    static function getAlbumImagePath($id, $imageSize)
    {
        MyDB::createConnection();

        $sql='SELECT path, filename FROM album_arts WHERE id=?';

        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute(array($id));

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC)) {

            $bigImage = ALBUM_COVERS_DIR . $item['path'] . $item['filename'];

            $extension = pathinfo($bigImage, PATHINFO_EXTENSION);

            if(FilesIO::fileExists($bigImage)) {
                $result = $bigImage;
            }

            if(function_exists('gd_info')) {
                $smallImage = ALBUM_COVERS_DIR . $item['path'] . 'small_' . $item['filename'];
                $thumbImage = ALBUM_COVERS_DIR . $item['path'] . 'thumb_' . $item['filename'];
                $icoImage = ALBUM_COVERS_DIR . $item['path'] . str_replace('.' . $extension, '.ico', $item['filename']);

                if(FilesIO::fileExists($smallImage)) {
                    $smallExist = true;
                } else {
                    $smallExist = false;
                }

                if(FilesIO::fileExists($thumbImage)) {
                    $thumbExist = true;
                } else {
                    $thumbExist = false;
                }

//                if(FilesIO::fileExists($icoImage)) {
//                    $icoExist = true;
//                } else {
//                    $icoExist = false;
//                }

                switch ($imageSize) {
                    case 'small': if($smallExist) {
                        $result = $smallImage;
                    } break;
                    case 'thumb': if($thumbExist) {
                        $result = $thumbImage;
                    } break;
//                    case 'ico': if($icoExist) {
//                        $result = $icoImage;
//                    } break;
                }

//                if($imageSize=='big' && $_SESSION['mobile'] && $smallExist) {
//                    $result = $smallImage;
//                }
            } else {
                if($imageSize=='ico') {
                    $result = false;
                }
            }

        } else {
            $result=false;
        }

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    /**
     * Εκτελεί την linux εντολή για μετατροπή ενός ALAC σε mp3
     *
     * @param $source {string} Το path του αρχικού αρχείου
     * @param $target {string} Το path του τελικού αρχείου
     * @param $bitrate {int} To bitrate της μετατροπής
     */
    static function execConvertALAC ($source, $target, $bitrate)
    {
        // Μετατροπή ALAC σε απλό mp3. Το δημιουργεί καταρχήν σε temp dir (INTERNAL_CONVERT_PATH)
        print shell_exec('ffmpeg -i "'.$source.'" -ac 2 -f wav - | lame -b '.$bitrate.' - "'.$target.'" ');
    }

    /**
     * Δημιουργεί έναν νέο πίνακα για temporary playlist με το όνομα $table
     *
     * @param $table {string} To όνομα του πίνακα που θα δημιουργηθεί
     * @return bool True or False για την επιτυχία
     */
    static function createPlaylistTempTable($table)
    {
        $conn = new MyDB();
        MyDB::createConnection();

        $sql = 'CREATE TABLE '.$table.' (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `file_id` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;';

        $stmt = MyDB::$conn->prepare($sql);


        if($stmt->execute())

            $result = true;

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    /**
     * Ελέγχει αν υπάρχει ένα $tempPlaylist και αν δεν υπάρχει το δημιουργεί και κάνει σχετική εγγραφή στο playlist_tables
     *
     * @param $tempPlaylist {string} Το όνομα της προσωρινής playlist
     */
    static function checkTempPlaylist($tempPlaylist)
    {
        $conn = new MyDB();

        // Αν δεν υπάρχει ήδη το σχετικό table το δημιουργούμε
        if (!MyDB::checkIfTableExist($tempPlaylist)) {
            self::createPlaylistTempTable($tempPlaylist); // Δημιουργούμε το table

            // κάνουμε την σχετική εγγραφή τον πίνακα playlist_tables
            $sql = 'INSERT INTO playlist_tables (table_name, last_alive) VALUES(?,?)';
            $playlistTableArray = array($tempPlaylist, date('Y-m-d H:i:s'));
            $conn->insertInto($sql, $playlistTableArray);
        }
    }

    /**
     * Επιστρέφει μία τυχαία εγγραφή από τον $table
     *
     * @param $table {string} Το όνομα του πίνακα
     * @param $tableCount {int} Το μέγεθος του πίνακα
     * @return array|bool
     */
    static function getRandomPlaylistID($table, $tableCount)
    {
        MyDB::createConnection();

        $sql='SELECT * FROM '.$table.' LIMIT '.$tableCount.',1';

        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute();

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC))
        {
            $result=array(
                'playlist_id' => $item['id'],
                'file_id' => $item['file_id']
            );
        }
        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    /**
     * Προσθέτει ένα $fileID στο $tempPlaylist
     *
     * @param $tempPlaylist {string} Το όνομα της προσωρινής playlist
     * @param $fileID {int} To id του αρχείου που θα προστεθεί
     * @return bool True or False για την επιτυχία
     */
    static function insertIntoTempPlaylist($tempPlaylist, $fileID)
    {

        // αν δεν υπάρχει η συγκεκριμένη εγγραφή ήδη, τότε μπορεί να γίνει η εισαγωγή
        if(!MyDB::getTableFieldValue($tempPlaylist, 'file_id=?', array($fileID), 'id')) {
            $conn = new MyDB();

            $sql = 'INSERT INTO ' . $tempPlaylist . ' (file_id) VALUES(?)';

            if ($conn->insertInto($sql, array($fileID))) {
                return true;
            } else {
                return false;
            }
        }

    }

    /**
     * Προσθέτει μία ψήφο στο table votes
     *
     * @param $fileID {int} Το id του αρχείου στο οποίο θα προστεθεί μία ψήφος
     * @return bool True or False για την επιτυχία
     */
    static function voteSong($fileID)
    {

        $userIP=$_SESSION['user_IP'];  // H ip του χρήστη

        $conn = new MyDB();

        if(!MyDB::getTableFieldValue('votes', 'voter_ip=?', $userIP, 'id')) {
            $sql = 'INSERT INTO votes (file_id,voter_ip) VALUES(?,?)';

            trigger_error('User vote '.$userIP);

            if ($conn->insertInto($sql, array($fileID,$userIP))) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }


    }

    /**
     * Επιστρέφει το σύνολο ψήφων για κάθε file_id
     *
     * @return mixed Array με το file_id και τον αριθμό ψήφων
     */
    static function getVotes()
    {
        MyDB::createConnection();

        $sql='SELECT file_id, count(*) as numberOfVotes FROM votes GROUP BY file_id';

        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute();

        $result=$stmt->fetchAll();

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    /**
     * Επιστρέφει σε πίνακα (song_name, artist) τα στοιχεία του τρέχοντος τραγουδιού
     *
     * @param $id {int} Το id του αρχείου
     * @return bool|mixed Array με τα στοιχεία του τραγουδιού ή False για αποτυχία
     */
    static function getSongInfo($id)
    {

        if(isset($id)) {
            $currentSongID = $id;
        } else {
            $currentSongID = Progress::getCurrentSong();
        }

        if($currentSongID) { // Το id του τραγουδιού
            if($currentSong = MyDB::getTableArray('music_tags', 'song_name, artist, id',
                'id=?', array($currentSongID), 'id DESC LIMIT 1', null, null)) { // Τα στοιχεία του τραγουδιού
                return $currentSong;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    /**
     * Στέλνει τα στοιχεία του τραγουδιού στον icecast server
     *
     * @param $songInfo {string} Τα στοιχεία του τραγουδιού
     * @return bool True or False για την επιτυχία
     */
    static function sendToIcecast ($songInfo)
    {

        $html = 'http://'.ICECAST_USER.':'.ICECAST_PASS.'@'.ICECAST_SERVER.'/admin/metadata?mount=/'.
            ICECAST_MOUNT.'&mode=updinfo&song='.urlencode($songInfo);

        $response = file_get_contents($html);
        $decoded = json_decode($response, true);


        if($decoded) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Εμφάνιση του παραθύρου για edit tags
     *
     * @param $UserGroup {int} Το usergroup του χρήστη
     */
    static function displayEditTagsWindow()
    {
        ?>

        <div class="modal fade" id="editTag" tabindex="-1" role="dialog" aria-labelledby="editTag" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTagsModalLabel"><?php echo __('edit_file'); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <form id="FormMassiveTags" name="FormMassiveTags">

                            <div class="form-group my-1">
                                <label for="artist" class="sr-only"><?php echo __('tag_artist'); ?></label>
                                <input type="text" class="form-control form-control-sm" id="artist" name="artist" placeholder="<?php echo __('tag_artist'); ?>"
                                        maxlength="255">
                            </div>

                            <div class="form-group my-1">
                                <label for="album" class="sr-only"><?php echo __('tag_album'); ?></label>
                                <input type="text" class="form-control form-control-sm" id="album" name="album" placeholder="<?php echo __('tag_album'); ?>"
                                       maxlength="255">
                            </div>

                            <div class="row my-1">
                                <div class="form-group col-xl-4 col-sm-4 col-md-12 w-100 my-1">
                                    <label for="genre" class="sr-only"><?php echo __('tag_genre'); ?></label>
                                    <input type="text" class="form-control form-control-sm" id="genre" name="genre" placeholder="<?php echo __('tag_genre'); ?>"
                                         maxlength="20">
                                </div>

                                <div class="form-group col-xl-4 col-sm-4 col-md-12 w-100 my-1">
                                    <label for="year" class="sr-only"><?php echo __('tag_year'); ?></label>
                                    <input type="number" class="form-control form-control-sm" id="year" name="year" placeholder="<?php echo __('tag_year'); ?>">
                                </div>

                                <div class="form-group col-xl-4 col-sm-4 col-md-12 w-100 my-1">
                                    <label for="live" class="sr-only"><?php echo __('tag_live'); ?></label>
                                    <select class="form-control form-control-sm" id="live" name="live">
                                        <option value="0"><?php echo __('tag_live_official'); ?></option>
                                        <option value="1"><?php echo __('tag_live_live'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div id="rating_div">
                                <label for="tags_rating" class="sr-only"><?php echo __('tag_rating'); ?></label>

                                <input type="range" id="tags_rating" name="tags_rating" oninput="printValue(tags_rating, tags_rating_output);"
                                       maxlength="5" max="5" min="0" value="0" list="tags_rating_ticks">

                                <output for="tags_rating" id="tags_rating_output">0</output>

                                <datalist id="tags_rating_ticks">
                                    <?php
                                        for($i=0; $i<6; $i++) {
                                            ?>
                                                <option><?php echo $i; ?></option>
                                            <?php
                                        }
                                    ?>
                                </datalist>
                            </div>

                        </form>

                        <div id="myImage"></div>

                        <input type="file" name="uploadFile" id="uploadFile" accept='image/*' onchange="readImage(this.files);">

                    </div>

                    <div class="modal-footer row w-100 no-gutters">
                        <input type="button" class="btn btn-success col mx-1 my-1 px-1" name="submit" id="submit"
                               value="<?php echo __('tag_form_submit'); ?>" onclick="editFiles();">

                        <input type="button" class="btn btn-warning col mx-1 my-1 px-1" name="clearEdit" id="clearEdit"
                               value="<?php echo __('search_text_clear'); ?>" onclick="resetFormMassiveTags();">

                        <input type="button" class="btn btn-danger col mx-1 my-1 px-1" name="cancelEdit" id="cancelEdit"
                               value="<?php echo __('search_text_cancel'); ?>" onclick="cancelTheEdit();">
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

    // Εμφάνιση του media select
    static function displayChooseMediaSelect()
    {
        global $mediaKinds;

        ?>
        <div id="ChooseMediaKind" class="form-group col-10">

            <label for="mediakind" class="sr-only">Media Kind</label>
            <select class="form-control form-control-sm id="mediakind" name="mediakind"
                    onchange="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>, true, false);">
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
        <?php
    }

    /**
     * Εμφάνιση των στοιχείων επιλογής playlist
     *
     * @param $userID {int} To userID του χρήστη
     */
    static function displayChoosePlaylistElements($userID)
    {
        ?>
            <div id="ChoosePlaylist">
                <form id="formChoosePlaylist">

                    <div class="input-group">

                        <label for="playlist" class="sr-only">playlist</label>
                        <select class="form-control form-control-sm" id="playlist" name="playlist">
                            <option value="">
                                <?php echo __('choose_playlist'); ?>
                            </option>
                            <?php

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

                        <div class="my-auto mx-2">
                            <span class="mdi mdi-play mdi-24px hasCursorPointer" id="playPlaylist"
                                  onclick="playMyPlaylist(0, <?php echo PLAYLIST_LIMIT; ?>, true);" title="<?php echo __('play_file'); ?>">
                            </span>

                            <span class="mdi mdi-playlist-plus mdi-24px hasCursorPointer" data-toggle="modal" data-target="#insertPlaylistWindow"
                                  id="insertPlaylistClick"
                                  onclick="displayInsertPlaylistWindow();" title="<?php echo __('create_playlist'); ?>">
                            </span>

                            <span class="mdi mdi-delete mdi-24px hasCursorPointer" id="deletePlaylistClick"
                                  onclick="deletePlaylist();" title="<?php echo __('delete_playlist'); ?>">
                            </span>

                            <?php Page::getHelp('help_manual_playlists'); ?>
                        </div>

                    </div>

                </form>



            </div>


        <?php


    }

    /**
     * Εμφάνιση των στοιχείων επιλογής playlist
     *
     * @param $userID {int} To userID του χρήστη
     */
    static function displayChooseSmartPlaylistElements()
    {
        $user = new User();

        $userID = $user->getUserID($user->getSession('username'));      // Επιστρέφει το id του user με username στο session

        ?>
        <form id="formChooseSmartPlaylist">
            <div id="ChooseSmartPlaylist" class="row w-100 my-1 py-0 no-gutters">

                <div class="form-group col-lg-2 w-100 my-auto">
                    <label for="smartPlaylist" class="sr-only"><?php echo __('choose_playlist'); ?></label>
                    <select class="form-control form-control-sm" name="smartPlaylist" id="smartPlaylist">
                        <option value="">
                            <?php echo __('choose_playlist'); ?>
                        </option>
                        <?php

                        // H λίστα με τις manual playlists
                        $smartPlaylists = MyDB::getTableArray('smart_playlists', 'id, playlist_name, playlist_data',
                            'user_id=?', array($userID), null, null, null);

                        foreach ($smartPlaylists as $playlist) {
                            ?>
                            <option value="<?php echo $playlist['id']; ?>">
                                <?php echo  $playlist['playlist_name']; ?>
                            </option>

                            <?php
                        }
                        ?>
                    </select>
                </div>

                <div class="col-lg-3 px-1 w-100 my-auto text-center">
                    <span class="mdi mdi-playlist-plus mdi-24px hasCursorPointer" id="jsInsertSmartPlaylistClick"
                           title="<?php echo __('create_smart_playlist'); ?>" onclick="displayInsertSmartPlaylistWindow();" >
                    </span>
                    <span class="mdi mdi-delete mdi-24px hasCursorPointer" id="jdDeleteSmartPlaylistClick"
                           title="<?php echo __('delete_smart_playlist'); ?>" onclick="deleteSmartPlaylist();" >
                    </span>
                    <span class="mdi mdi-content-save mdi-24px hasCursorPointer" id="jsSaveSmartPlaylist"
                           title="<?php echo __('save_smart_playlist'); ?>" onclick="saveSmartPlaylist();" >
                    </span>
                    <span class="mdi mdi-email-open-outline mdi-24px hasCursorPointer" id="jsLoadSmartPlaylist"
                           title="<?php echo __('load_smart_playlist'); ?>" onclick="loadSmartPlaylist();" >
                    </span>

                    <?php Page::getHelp('help_smart_playlists'); ?>
                </div>

            </div>
        </form>

        <?php


    }

    // Εμφάνιση παραθύρου προσθήκης playlist
    static function displayInsertPlaylistWindow()
    {
        ?>
        <div class="modal fade" id="insertPlaylistWindow" tabindex="-1" role="dialog" aria-labelledby="insertPlaylistWindow" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="insertPlaylistModalLabel"><?php echo __('create_playlist'); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <form id="insertPlaylist" name="insertPlaylist">
                            <div class="row w-100 py-1 px-1 no-gutters">
                                <div class="form-group col-12 my-auto">
                                    <label for="playlistName" class="sr-only"><?php echo __('playlist_name'); ?></label>
                                    <input type="text" class="form-control" id="playlistName" name="playlistName"
                                           placeholder="<?php echo __('playlist_name'); ?>">
                                </div>
                            </div>
                        </form>

                    </div>

                    <div class="modal-footer row w-100 no-gutters">
                        <input type="button" class="btm btn-success btn-sm PlaylistButton col mx-1 my-1 px-1" id="insertPlaylistButton" name="insertPlaylistButton" onclick="createPlaylist();"
                               value="<?php echo __('create_playlist'); ?>">
                        <input type="button" class="btm btn-danger btn-sm col mx-1 my-1 px-1" name="cancelPlaylist" id="cancelPlaylist" value="<?php echo __('search_text_cancel'); ?>" onclick="cancelCreatePlaylist();">
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    // Παράθυρο δημιουργίας μιας smart playlist
    static function displayInsertSmartPlaylistWindow()
    {
        ?>
            <div id="insertSmartPlaylistWindow" class="bg-light w-75 my-5 ml-auto mr-auto fixed-top">
                <form id="insertSmartPlaylist" name="insertSmartPlaylist">
                    <div class="row w-100 py-1 px-1 text-center no-gutters">
                        <div class="form-group col-lg-5 my-auto py-1 w-100">
                            <label for="smartPlaylistName" class="sr-only">Smart Playlist</label>
                            <input type="text" class="form-control form-control-sm" id="smartPlaylistName" name="smartPlaylistName">
                        </div>
                            <input type="button" class="btm btn-success btn-sm PlaylistButton my-1 col-lg-3  w-100 ml-auto" id="insertSmartPlaylistButton" name="insertSmartPlaylistButton" onclick="createSmartPlaylist();"
                                 value="<?php echo __('create_playlist'); ?>">
                            <input type="button" class="btm btn-danger btn-sm  my-1 col-lg-3  w-100 ml-auto" name="cancelSmartPlaylist" id="cancelSmartPlaylist" value="<?php echo __('search_text_cancel'); ?>" onclick="cancelCreateSmartPlaylist();">
                    </div>
                </form>
            </div>
        <?php
    }

    /**
     * Εμφάνιση παραθύρου για επιλογή sleep timer
     */
    static function displaySleepTimer()
    {
        ?>

        <div class="modal fade" id="insertSleepTimerWindow" tabindex="-1" role="dialog" aria-labelledby="insertSleepTimerWindow" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sleepTimerModalLabel"><?php echo __('sleep_timer'); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <form id="sleepTimer" name="sleepTimer">
                            <div class="form-group">
                                <label for="sleepMinutes" class="sr-only">Sleep Timer</label>
                                <select id="sleepMinutes" name="sleepMinutes" class="form-control w-100">
                                    <option value="1">1 <?php echo __('text_minute'); ?></option>
                                    <option value="5">5 <?php echo __('text_minutes'); ?></option>
                                    <option value="10">10 <?php echo __('text_minutes'); ?></option>
                                    <option value="15">15 <?php echo __('text_minutes'); ?></option>
                                    <option value="30">30 <?php echo __('text_minutes'); ?></option>
                                    <option value="60">1 <?php echo __('text_hour'); ?></option>
                                    <option value="120">2 <?php echo __('text_hours'); ?></option>
                                    <option value="300">6 <?php echo __('text_hours'); ?></option>
                                </select>
                            </div>

                            <div class="w-100 text-center">
                                <span id="theSleepTimer" class="font-weight-bold"></span>
                            </div>

                        </form>

                    </div>

                    <div class="modal-footer row w-100 no-gutters">
                        <input type="button" class="btn btn-success btn-sm col my-1 mx-1 px-1" id="startSleepTimerButton" name="startSleepTimerButton"
                               onclick="startSleepTimer();"
                             value="<?php echo __('start_sleep_timer'); ?>">
                        <input type="button" class="btn btn-danger btn-sm col my-1 mx-1 px-1" name="cancelSleepTimer" id="cancelSleepTimer"
                            value="<?php echo __('search_text_cancel'); ?>" onclick="cancelTheSleepTimer();">
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

    // Εμφάνιση του παραθύρου για αναζήτηση
    public function displaySearchWindow()
    {

        if($_SESSION['PlaylistCounter']==0) {

            $fields = MyDB::getTableFields('music_tags',array('id'));

            ?>

            <div class="modal fade" id="search" tabindex="-1" role="dialog" aria-labelledby="search" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="searchModalLabel"><?php echo __('search_text_search'); ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">

                            <?php

                            // Εμφάνιση στοιχείων για επιλογή της smart playlist
                            $this->displayChooseSmartPlaylistElements();

                            $this->displayInsertSmartPlaylistWindow(); // Εμφάνιση παραθύρου προσθήκης smart playlist

                            ?>

                            <form id="SearchForm" name="SearchForm">
                                <?php

                                for($counter=0;$counter<2;$counter++) {

                                    ?>

                                    <div id="searchRow<?php echo $counter; ?>" class="<?php if($counter==0) echo 'isHidden'; else echo 'isVisible'; ?>" >
                                        <div class="row py-1 px-1 no-gutters">

                                            <div class="form-group col-lg-2 w-100 my-auto">
                                                <label for="search_field<?php echo $counter; ?>" class="sr-only">search_field<?php echo $counter; ?></label>
                                                <select class="form-control form-control-sm search_field" name="search_field<?php echo $counter; ?>" id="search_field<?php echo $counter; ?>">
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
                                            </div>


                                            <div class="form-group col-lg-2 w-100 my-auto">
                                                <label for="search_equality<?php echo $counter; ?>" class="sr-only">search_equality<?php echo $counter; ?></label>
                                                <select class="form-control form-control-sm search_equality" name="search_equality<?php echo $counter; ?>" id="search_equality<?php echo $counter; ?>">

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
                                            </div>

                                            <div id="search_text_group<?php echo $counter; ?>" class="search_text_group form-group col-lg-4 w-100 my-auto">
                                                <label for="search_text<?php echo $counter; ?>" class="sr-only">search_text<?php echo $counter; ?></label>
                                                <input type="text" class="form-control form-control-sm search_text" name="search_text<?php echo $counter; ?>" id="search_text<?php echo $counter; ?>">
                                            </div>

                                            <div class="form-group col-lg-2 w-100 my-auto">
                                                <label for="search_operator<?php echo $counter; ?>" class="sr-only">search_operator<?php echo $counter; ?></label>
                                                <select class="form-control form-control-sm search_operator" name="search_operator<?php echo $counter; ?>" id="search_operator<?php echo $counter; ?>">
                                                    <option value="OR">
                                                        <?php echo __('search_or'); ?>
                                                    </option>

                                                    <option value="AND">
                                                        <?php echo __('search_and'); ?>
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-2 w-100 text-right my-auto">
                                                <span class="mdi mdi-plus-box-outline mdi-24px hasCursorPointer" id="jsAddSearchRow"
                                                       title="<?php echo __('add_search_row'); ?>" onclick="addSearchRow();">
                                                </span>
                                                <span class="mdi mdi-minus-box-outline mdi-24px hasCursorPointer" id="jsRemoveSearchRow"
                                                       title="<?php echo __('remove_search_row'); ?>" onclick="removeSearchRow(<?php echo $counter; ?>);">
                                                </span>
                                                <span class="mdi mdi-plus-circle mdi-24px hasCursorPointer" id="jsAddGroup"
                                                       title="<?php echo __('add_group_row'); ?>" onclick="addOrAndToGroup(<?php echo $counter; ?>);">
                                                </span>
                                            </div>

                                        </div>
                                    </div>

                                    <?php
                                }
                                ?>
                            </form>
                        </div>

                        <div id="searchButtons" class="modal-footer row w-100 no-gutters">
                            <input type="button" class="btn btn-success col px-1 my-1 mx-1" name="searching" id="searching"
                                   value="<?php echo __('search_text_search'); ?>" onclick="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>, true, false);">

                            <input type="button" class="btn btn-dark col px-1 my-1 mx-1" name="duplicates" id="duplicates"
                                   value="<?php echo __('search_text_duplicates'); ?>" onclick="findDuplicates(0,<?php echo PLAYLIST_LIMIT; ?>, true);">

                            <input type="button" class="btn btn-dark col px-1 my-1 mx-1" name="playedQueue" id="playedQueue"
                                   value="<?php echo __('search_text_played_queue'); ?>" onclick="loadPlayedQueuePlaylist();">

                            <input type="button" class="btn btn-warning col px-1 my-1 mx-1" name="jsClearSearch" id="jsClearSearch"
                                   value="<?php echo __('search_text_clear'); ?>" onclick="clearSearch();">

                            <input type="button" class="btn btn-danger col px-1 my-1 mx-1" name="cancelSearch" id="cancelSearch"
                                   value="<?php echo __('search_text_cancel'); ?>" onclick="cancelTheSearch();" >
                        </div>
                    </div>
                </div>
            </div>

            <?php
        } else {
        ?>
            <div class="modal fade" id="search" tabindex="-1" role="dialog" aria-labelledby="search" aria-hidden="true">

            </div>
            <?php
        }
        ?>


        <script type="text/javascript">

            // περνάει στην javascript τα options των αντίστοιχων select
            var liveOptions = <?php echo json_encode([__('tag_live_official'),__('tag_live_live')]); ?>;

            var ratingOptions = <?php echo json_encode([0,1,2,3,4,5]); ?>;

        </script>

        <?php
    }

    /**
     * Εμφάνιση διάφορων εργαλείων
     *
     * @param $UserGroup {int} Το user group του χρήστη
     */
    static function displaySomeTools()
    {
        ?>

        <div class="collapse navbar-collapse w-75 ml-auto mr-auto" id="navbarNavToolbar">
            <div class="navbar-nav nav-pills">
                    <input type="button" class="btn btm-sm btn-dark nav-item nav-link my-1 text-white px-1"
                           name="sendToJukebox" id="sendToJukebox"
                           value="<?php echo __('send_to_jukebox'); ?>" onclick="sendToJukeboxList();">
                    <input type="button" class="btn btn-sm btn-dark nav-item nav-link my-1 text-white px-1"
                           name="displaySleepTimer" id="displaySleepTimer"
                           data-toggle="modal" data-target="#insertSleepTimerWindow"
                           value="<?php echo __('sleep_timer'); ?>" onclick="displayTheSleepTimer();">
            </div>
        </div>
        <?php
    }

    /**
     * Εμφάνιση των edit buttons
     *
     * @param $UserGroup {int} Το user group του χρήστη
     */
    static function displayEditButtons($UserGroup)
    {
        if($UserGroup==1) {
            ?>
            <div>
                <span class="mdi mdi-delete mdi-24px hasCursorPointer"
                       title="<?php echo __('delete_file'); ?>"
                       onclick="deleteFile(0);">
                </span>

                <span class="mdi mdi-pencil mdi-24px hasCursorPointer" data-toggle="modal" data-target="#editTag"
                       title="<?php echo __('edit_file'); ?>"
                       onclick="openMassiveTagsWindow();" >
                </span>

                <span class="mdi mdi-file-export mdi-24px hasCursorPointer"
                       title="<?php echo __('export_playlist'); ?>"
                       onclick="exportPlaylist();" >
                </span>

            </div>

            <?php
        }
    }

    /**
     * Εμφάνιση του playlist container
     *
     * @param $offset {int} Το τρέχον σημείο της λίστας
     * @param $step {int} Ο αριθμός εγγραφών ανα σελίδα
     */
    public function displayPlaylistContainer($offset,$step)
    {
        ?>
        <div id="playlist_container">

            <?php
            $playlist = new PlaylistSearch();

            if($_SESSION['PlaylistCounter']==0) {
                $_SESSION['condition']=null;   // Μηδενίζει το τρέχον search query
                $_SESSION['arrayParams']=null;

                $playlist->fieldsArray = null;
                $playlist->offset = $offset;
                $playlist->step = $step;
                $playlist->duplicates = null;
                $playlist->mediaKind = null;
                $playlist->tabID = null;
                $playlist->loadPlaylist = null;
                $playlist->votePlaylist = false;
                $playlist->getPlaylist();
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

}