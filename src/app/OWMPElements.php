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

        <div id="<?php echo $element; ?>" <?php if ($fullscreen) echo 'class="bgc10 c2"'; ?>>

            <span class="<?php if ($fullscreen) echo 'mdi mdi-skip-previous mdi-light mdi-36px hasCursorPointer'; else echo 'mdi mdi-skip-previous mdi-dark mdi-36px hasCursorPointer'; ?>"
                  title="<?php echo __('previous_song'); ?>"
                  onclick="prevSong();">
            </span>

            <span class="pause_play_button <?php if ($fullscreen) echo 'mdi mdi-pause mdi-light mdi-36px hasCursorPointer'; else echo 'mdi mdi-pause mdi-dark mdi-36px hasCursorPointer'; ?>"
                  title="<?php echo __('play_file'); ?>"
                  onclick="playSong();">
            </span>

            <span class="<?php if ($fullscreen) echo 'mdi mdi-skip-next mdi-light mdi-36px hasCursorPointer'; else echo 'mdi mdi-skip-next mdi-dark mdi-36px hasCursorPointer'; ?>"
                  title="<?php echo __('next_song'); ?>"
                  onclick="nextSong();">
            </span>

            <span class="<?php if ($fullscreen) echo 'mdi mdi-fullscreen-exit mdi-light mdi-36px hasCursorPointer'; else echo 'mdi mdi-fullscreen mdi-dark mdi-36px hasCursorPointer'; ?>"
                  title="<?php echo __('toggle_fullscreen'); ?>"
                  onclick="toggleFullscreen();">
            </span>


            <?php

            if ($fullscreen) { // Αν είναι σε fullscreen
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

        <div id="overlay_volume" class="row h-100 fixed-top no-gutters">
            <div id="overlay_volume_text" class="col-sm-2 col-6 ml-auto mr-auto text-white text-center px-2 py-2">

            </div>
        </div>

        <div id="overlay" class="fixed-top row w-100 h-100 no-gutters" ondblclick="displayFullscreenControls();">

            <div class="row w-100 fixed-top no-gutters">

                <div id="overlay_rating" class="col-lg-2 col-3 text-left px-3 my-auto">

                </div>

                <div class="col-lg-8 col-6 w-100 text-white text-center row no-gutters">
                    <span id="jsOverlayTrackTime" class="col-lg-1 col-4 my-auto">00:00</span>
                    <input type=range class="o-trackTime--overlay__range col-lg-10 col-4 my-auto" min="0" max="100"
                           list="overlay_track_ticks" value="0" oninput="controlTrack();">
                    <span id="jsOverlayTotalTrackTime" class="col-lg-1 col-4 my-auto">00:00</span>

                    <div id="chozenManualPlaylist" class="col-lg-2 col-3 text-white mx-auto" ></div>
                </div>

                <div id="overlay_play_count" class="col-lg-2 col-3 text-right w-100 text-white px-3 my-auto">

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
                    <span id="overlay_genre" class="col-12"></span>
                    <span id="overlay_time" class="col-12"></span>
                </div>

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

        <form class="validate-form row no-gutters" id="FormTags" name="FormTags">

            <input type="hidden" id="songID" name="songID">

            <div class="form-group col-12 my-1 px-1">
                <label for="title" class="sr-only"><?php echo __('tag_title'); ?></label>
                <input type="text" class="form-control form-control-sm" id="title" name="title"
                       placeholder="<?php echo __('tag_title'); ?>"
                    <?php echo $disabled . ' ' . $readonly; ?> maxlength="255">
            </div>

            <div class="form-group col-12 my-1 px-1">
                <label for="artist" class="sr-only"><?php echo __('tag_artist'); ?></label>
                <input type="text" class="form-control form-control-sm" id="artist" name="artist"
                       placeholder="<?php echo __('tag_artist'); ?>"
                    <?php echo $disabled . ' ' . $readonly; ?> maxlength="255">
            </div>

            <div class="form-group col-12 my-1 px-1">
                <label for="album" class="sr-only"><?php echo __('tag_album'); ?></label>
                <input type="text" class="form-control form-control-sm" id="album" name="album"
                       placeholder="<?php echo __('tag_album'); ?>"
                    <?php echo $disabled . ' ' . $readonly; ?> maxlength="255">
            </div>

            <div class="row col-12 no-gutters">
                <div class="form-group col-md-4 col-12 w-100 my-1 px-1">
                    <label for="genre" class="sr-only"><?php echo __('tag_genre'); ?></label>
                    <input type="text" class="form-control form-control-sm" id="genre" name="genre"
                           placeholder="<?php echo __('tag_genre'); ?>"
                        <?php echo $disabled . ' ' . $readonly; ?> maxlength="20">
                </div>

                <div class="form-group col-md-4 col-12 w-100 my-1 px-1">
                    <label for="year" class="sr-only"><?php echo __('tag_year'); ?></label>
                    <input type="number" class="form-control form-control-sm" id="year" name="year"
                           placeholder="<?php echo __('tag_year'); ?>"
                        <?php echo $disabled . ' ' . $readonly; ?>>
                </div>

                <div class="form-group col-md-4 col-12 w-100 my-1 px-1">
                    <label for="live" class="sr-only"><?php echo __('tag_live'); ?></label>
                    <select class="form-control form-control-sm" id="live"
                            name="live" <?php echo $disabled . ' ' . $readonly; ?>>
                        <option value="0"><?php echo __('tag_live_official'); ?></option>
                        <option value="1"><?php echo __('tag_live_live'); ?></option>
                    </select>
                </div>
            </div>

            <div id="rating_div" class="col-12">
                <label for="rating" class="sr-only"><?php echo __('tag_rating'); ?></label>

                <input type="range" id="rating" name="rating" oninput="printValue(rating, rating_output);"
                       maxlength="5" max="5" min="0" list="rating_ticks">

                <output for="rating" id="rating_output">0</output>

                <datalist id="rating_ticks">
                    <?php
                    for ($i = 0; $i < 6; $i++) {
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

                <div class="row no-gutters">
                    <div class="form-group my-1 col-12 px-1">
                        <label for="play_count" class="sr-only"><?php echo __('tag_play_count'); ?></label>
                        <input type="number" class="form-control form-control-sm" id="play_count" name="play_count"
                               placeholder="<?php echo __('tag_play_count'); ?>"
                            <?php echo $disabled; ?> readonly>
                    </div>

                    <div class="form-group col-12 my-1 px-1">
                        <label for="date_added" class="sr-only"><?php echo __('tag_date_added'); ?></label>
                        <input type="text" class="form-control form-control-sm" id="date_added" name="date_added"
                               placeholder="<?php echo __('tag_date_added'); ?>"
                            <?php echo $disabled; ?> maxlength="20" readonly>
                    </div>

                    <div class="form-group col-12 my-1 px-1">
                        <label for="date_played" class="sr-only"><?php echo __('tag_date_played'); ?></label>
                        <input type="text" class="form-control form-control-sm" id="date_played" name="date_played"
                               placeholder="<?php echo __('tag_date_played'); ?>"
                            <?php echo $disabled; ?> maxlength="20" readonly>
                    </div>

					<div class="form-group col-12 my-1 px-1">
						<label for="video_dimensions" class="sr-only"><?php echo __('tag_video_dimensions'); ?></label>
						<input type="text" class="form-control form-control-sm" id="video_dimensions" name="video_dimensions"
							   placeholder="<?php echo __('tag_video_dimensions'); ?>"
                            <?php echo $disabled; ?> readonly>
					</div>

                    <div class="form-group col-12 my-1 px-1">
                        <label for="path_filename" class="sr-only"><?php echo __('tag_path_filename'); ?></label>
                        <input type="text" class="form-control form-control-sm" id="path_filename" name="path_filename"
                               placeholder="<?php echo __('tag_path_filename'); ?>"
                            <?php echo $disabled; ?> maxlength="255" readonly>
                    </div>
                </div>

            </details>


        </form>


        <?php
    }


    /**
     * Εμφάνιση των εγγραφών των options σε μορφή form fields για editing
     */
    static function getOptionsInFormFields()
    {
        $conn = new MyDB();

        $options = $conn->getTableArray('options', null, 'setting=?', array(1), null, null, null);  // Παίρνει τα δεδομένα του πίνακα options σε array


        ?>

        <div class="ListTable">

            <?php

            foreach ($options as $option) {
                ?>
                <div id="OptionID<?php echo $option['option_id']; ?>">
                    <form class="row no-gutters" id="options_formID<?php echo $option['option_id']; ?>">

                        <div class="form-group my-1 col-lg col-12 px-1">
                            <label for="option_name" class="sr-only"><?php echo __('options_option'); ?></label>
                            <input type="text" class="form-control form-control-sm" id="option_name" name="option_name"
                                   placeholder="<?php echo __('options_option'); ?>"
                                   value="<?php echo $option['option_name']; ?>">
                        </div>

                        <div class="form-group my-1 col-lg col-12 px-1">
                            <label for="option_value" class="sr-only"><?php echo __('options_value'); ?></label>
                            <input type="<?php echo ($option['encrypt'] == 0) ? 'text' : 'password'; ?>"
                                   class="form-control form-control-sm" id="option_value" name="option_value"
                                   placeholder="<?php echo __('options_value'); ?>"
                                   value="<?php echo ($option['encrypt'] == 0) ? $option['option_value'] : ''; ?>"
                                   title="<?php echo __('valid_option'); ?>" maxlength="255" required>
                        </div>

                        <div class="my-auto col-lg col-12 text-center px-1">
                            <span class="mdi mdi-checkbox-marked-circle mdi-24px hasCursorPointer"
                                   id="update_option"
                                   title="<?php echo __('update_row'); ?>"
                                   onclick="updateOption(<?php echo $option['option_id']; ?>);">
                            </span>

                            <input type="button" class="message" id="messageOptionID<?php echo $option['option_id']; ?>">
                        </div>
                    </form>

                    <script type="text/javascript">

                        checkTheFocus('options_formID<?php echo $option["option_id"]; ?>');

                    </script>

                </div>
                <?php
            }
            ?>


        </div>

        <?php


    }

    // Εμφάνιση των εγγραφών των χρηστών σε μορφή form fields για editing
    static function getUsersInFormFields()
    {
        $conn = new MyDB();
        $user = new User();
        MyDB::createConnection();

        $UserGroupID = $user->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης
        $userID = $user->getUserID($conn->getSession('username'));      // Επιστρέφει το id του user με username στο session

        global $UserGroups;

        if ($UserGroupID == 1)
            $sql = 'SELECT * FROM user JOIN user_details on user.user_id=user_details.user_id';
        else $sql = 'SELECT * FROM user JOIN user_details on user.user_id=user_details.user_id WHERE user.user_id=?';

        $stmt = MyDB::$conn->prepare($sql);

        $counter = 1;

        if ($UserGroupID == 1)
            $stmt->execute();
        else $stmt->execute(array($userID));

        ?>
        <div>

            <?php

            while ($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                ?>
                <div id="UserID<?php echo $item['user_id']; ?>">
                    <form class="row no-gutters w-100" id="users_formID<?php echo $item['user_id']; ?>">

                        <div class="form-group col-lg col-12 my-1 px-1">
                            <label for="theUsername" class="sr-only"><?php echo __('users_username'); ?></label>
                            <input type="text" class="form-control form-control-sm" id="theUsername" name="theUsername"
                                   placeholder="<?php echo __('users_username'); ?>" title="<?php echo __('valid_username'); ?>"
                                   pattern="^[a-zA-Z][a-zA-Z0-9-_\.]{4,15}$" maxlength="15" required
                                   value="<?php echo $item['username']; ?>">
                        </div>

                        <div class="form-group col-lg col-12 my-1 px-1">
                            <label for="email" class="sr-only"><?php echo __('users_email'); ?></label>
                            <input type="text" class="form-control form-control-sm" id="email" name="email"
                                   placeholder="<?php echo __('users_email'); ?>" title="<?php echo __('valid_email'); ?>"
                                   maxlength="50" required
                                   value="<?php echo $item['email']; ?>">
                        </div>

                        <div class="form-group col-lg col-12 my-1 px-1">
                            <label for="password<?php echo $item['user_id']; ?>" class="sr-only"><?php echo __('users_password'); ?></label>
                            <input type="password" class="form-control form-control-sm" id="password<?php echo $item['user_id']; ?>"
                                   name="password"
                                   placeholder="<?php echo __('users_password'); ?>" title="<?php echo __('valid_register_password'); ?>"
                                   maxlength="15" value="">
                        </div>

                        <div class="form-group col-lg col-12 my-1 px-1">
                            <label for="<?php echo $item['user_id']; ?>" class="sr-only"><?php echo __('users_repeat_password'); ?></label>
                            <input type="password" class="form-control form-control-sm" id="<?php echo $item['user_id']; ?>"
                                   name="repeat_password"
                                   placeholder="<?php echo __('users_repeat_password'); ?>" title="<?php echo __('valid_register_repeat_password'); ?>"
                                   maxlength="15" value="">
                        </div>

                        <div class="form-group col-lg col-12 my-1 px-1">
                            <label for="usergroup" class="sr-only">User Group</label>
                            <select class="form-control form-control-sm" id="usergroup" name="usergroup"
                                    <?php echo ($UserGroupID != 1) ? ' disabled=disabled' : ''; ?> >
                                <?php
                                foreach ($UserGroups as $UserGroup) {
                                    ?>
                                    <option value="<?php echo $UserGroup['id']; ?>"
                                        <?php if ($UserGroup['id'] == $item['user_group']) echo 'selected=selected'; ?>>
                                        <?php echo $UserGroup['group_name']; ?>
                                    </option>

                                    <?php
                                }
                                ?>
                            </select>
                         </div>

                        <div class="form-group col-lg col-12 my-1 px-1">
                            <label for="fname" class="sr-only"><?php echo __('users_firstname'); ?></label>
                            <input type="text" class="form-control form-control-sm" id="fname" name="fname"
                                   placeholder="<?php echo __('users_firstname'); ?>" title="<?php echo __('valid_fname'); ?>"
                                   pattern='^[a-zA-ZΆ-Ϋά-ώ][a-zA-ZΆ-Ϋά-ώ0-9-_\.]{2,15}$' maxlength="15" value="<?php echo $item['fname']; ?>">
                        </div>

                        <div class="form-group col-lg col-12 my-1 px-1">
                            <label for="lname" class="sr-only"><?php echo __('users_lastname'); ?></label>
                            <input type="text" class="form-control form-control-sm" id="lname" name="lname"
                                   placeholder="<?php echo __('users_firstname'); ?>" title="<?php echo __('valid_lname'); ?>"
                                   pattern='^[a-zA-ZΆ-Ϋά-ώ][a-zA-ZΆ-Ϋά-ώ0-9-_\.]{2,25}$' maxlength="25" value="<?php echo $item['lname']; ?>">
                        </div>

                        <div class="col-lg col-12 my-auto px-1">
                            <span class="mdi mdi-checkbox-marked-circle mdi-24px hasCursorPointer" id="update_user"
                                   title="<?php echo __('update_row'); ?>"
                                   onclick="updateUser(<?php echo $item['user_id']; ?>);">
                            </span>

                            <span class="mdi mdi-delete mdi-24px hasCursorPointer <?php echo ($counter == 1) ? 'dontDelete' : ''; ?>"
                                   id="delete_user" title="<?php echo __('delete_row'); ?>"
                                   onclick="deleteUser(<?php echo $item['user_id']; ?>);">
                            </span>

                            <input type="button" class="message" id="messageUserID<?php echo $item['user_id']; ?>">
                        </div>

                    </form>

                    <script type="text/javascript">

                        checkTheFocus('users_formID<?php echo $item["user_id"]; ?>');

                    </script>

                </div>
                <?php
                $counter++;
            }
            ?>

        </div>

        <?php
        if ($UserGroupID == 1) {  // Αν είναι admin ο user εμφάνισε κουμπί για προσθήκη νέου user
            ?>

            <div class="row text-center w-100">
                <input type="button" class="btn btn-warning btn-sm col-lg-6 col-12 ml-auto mr-auto" name="insert_user" onclick="insertUser();"
                       value="<?php echo __('insert_row'); ?>">
            </div>
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

        <div class="modal fade" id="browsePathWindow" tabindex="-1" role="dialog" aria-labelledby="browsePathWindow"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="elementModalLabel"><span id="chosenPathText"></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">


                        <table id="pathsContainer" class="table table-hover table-striped table-sm table-nowrap">
                            <tbody id="displayPaths">

                            </tbody>
                        </table>


                    </div>

                    <div class="modal-footer row w-100 no-gutters">

                        <input type="button" class="btn btn-success ml-auto" name="submit" id="submit"
                               value="<?php echo __('import_path'); ?>" onclick="importPath();">

                        <input type="button" class="btn btn-danger mr-auto" name="cancelBrowse" id="cancelBrowse"
                               value="<?php echo __('search_text_cancel'); ?>" onclick="cancelTheBrowse();">
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

    // Εμφανίζει τα input fields για τα paths
    static function getPathsInFormFields()
    {
        $conn = new MyDB();
        global $mediaKinds;


        $paths = $conn->getTableArray('paths', null, null, null, null, null, null);  // Παίρνει τα δεδομένα του πίνακα paths σε array

        if (empty($paths)) {  // Αν δεν επιστρέψει κανένα αποτέλεσμα, σετάρουμε εμείς μια πρώτη γραμμή στο array
            $paths[] = array('id' => '0', 'file_path' => '', 'kind' => '', 'main' => '');
        }

        $counter = 1;


        ?>
        <div>

            <?php


            foreach ($paths as $path) {
                ?>
                <div id="PathID<?php echo $path['id']; ?>">
                    <form class="row no-gutters my-1" id="paths_formID<?php echo $path['id']; ?>">

                        <div class="form-group my-1 w-100 col-lg-5 col-12 px-1">
                            <label for="file_path" class="sr-only"><?php echo __('paths_file_path'); ?></label>
                            <input type="text" class="form-control form-control-sm" id="file_path" name="file_path"
                                   maxlength="255" required placeholder="<?php echo __('paths_file_path'); ?>"
                                   value="<?php echo $path['file_path']; ?>"
                                   onclick="displayBrowsePath('paths_formID<?php echo $path['id']; ?>');">
                        </div>

                        <div class="form-group my-1 w-100 col-lg-5 col-12 px-1">
                            <label for="kind" class="sr-only">Media Kind</label>
                            <select class="form-control form-control-sm" id="kind" name="kind">
                                <?php
                                foreach ($mediaKinds as $mediaKind) {
                                    ?>
                                    <option value="<?php echo $mediaKind; ?>"
                                        <?php if ($mediaKind == $path['kind']) echo 'selected=selected'; ?>>
                                        <?php echo $mediaKind ?>
                                    </option>

                                    <?php
                                }
                                ?>
                            </select>
                        </div>


                        <div class="col-lg-2 col-12 px-1 text-center">
                                <span class="mdi mdi-checkbox-marked-circle mdi-24px hasCursorPointer" id="update_path"
                                      title="<?php echo __('update_row'); ?>"
                                      onclick="updatePath(<?php echo $path['id']; ?>);">
                                </span>

                            <span class="mdi mdi-delete mdi-24px hasCursorPointer <?php if ($counter == 1) echo 'dontDelete'; ?>"
                                  id="delete_path" title="<?php echo __('delete_row'); ?>"
                                  onclick="deletePath(<?php echo $path['id']; ?>);">
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

        <div class="row no-gutters">
            <input type="button" class="btn btn-warning btn-sm ml-auto mr-auto col-lg-6 col-12" name="insert_path"
                   onclick="insertPath();" value="<?php echo __('insert_row'); ?>">
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

                        <div class="form-group my-1 w-100 col-lg-5 col-12 px-1 my-1">
                            <label for="option_name" class="sr-only"><?php echo $item['path_name']; ?></label>
                            <input type="text" class="form-control form-control-sm" id="option_name" name="option_name"
                                   placeholder="<?php echo __('options_option'); ?>"
                                   value="<?php echo $item['path_name']; ?>"
                                   disabled>
                        </div>

                        <div class="form-group my-1 w-100 col-lg-5 col-12 px-1 my-1">
                            <label for="file_path" class="sr-only"><?php echo $item['file_path']; ?></label>
                            <input type="text" class="form-control form-control-sm" id="file_path" name="file_path"
                                   placeholder="<?php echo __('paths_file_path'); ?>" maxlength="255" required
                                   value="<?php echo $item['file_path']; ?>"
                                   onclick="displayBrowsePath('form<?php echo $item['path_name']; ?>');">
                        </div>

                        <div class="col-lg-2 col-12 px-1 text-center my-auto">
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

        if (VIDEO_FILE_UPLOAD) {
            $uploadDir = VIDEO_FILE_UPLOAD . $fileDir;

            $this->checkVideoFileUpload = FilesIO::createDirectory($uploadDir);

            if (!$this->checkVideoFileUpload['result']) {
                echo $this->checkVideoFileUpload['message'];
            }
        } else {
            echo '<p class="isFail">' . __('no_main_music_video_path') . '</p>';
        }

        if (MUSIC_FILE_UPLOAD) {
            $uploadDir = MUSIC_FILE_UPLOAD . $fileDir;
            $this->checkAudioFileUpload = FilesIO::createDirectory($uploadDir);

            if (!$this->checkAudioFileUpload['result']) {
                echo $this->checkAudioFileUpload['message'];
            }
        } else {
            echo '<p class="isFail">' . __('no_main_music_path') . '</p>';
        }

    }

    // Εμφάνιση των στοιχείων για κατέβασμα από YouTube
    public function displayYoutubeDownloadElements()
    {
        global $mediaKinds;

        // Έλεγχος αν έχει οριστεί κάποιο FILE_UPLOAD αλλιώς να μην ενεργοποιεί το κουμπί του youtube
        if (VIDEO_FILE_UPLOAD || MUSIC_FILE_UPLOAD) {

            // Έλεγχος αν υπάρχουν κι έχουν δικαιώματα τα directories για upload από το youtube
            $this->checkYoutubeUploadDirectories();

            if ($this->checkVideoFileUpload['result'] || $this->checkAudioFileUpload['result']) {

                ?>
                <div class="row py-1 no-gutters">

                    <div class="form-group col-12 px-1">
                        <label for="o-youTube__textArea" class="sr-only">Youtube Videos</label>
                        <textarea class="form-control" id="o-youTube__textArea" name="o-youTube__textArea"
                                  rows="3"></textarea>
                    </div>

                    <div class="form-group my-1 col-lg-6 col-12 px-1">
                        <label for="jsMediaKind" class="sr-only">Media Kind</label>
                        <select class="form-control form-control-sm" id="jsMediaKind" name="jsMediaKind">
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

                    <div class="col-lg-6 col-12 px-1">
                        <input type="button" class="btn btn-warning w-100" id="jsDownloadYouTube"
                               name="jsDownloadYouTube"
                               onclick="downloadTheYouTube();"
                               value="<?php echo __('sync_youtube'); ?>">

                        <input type="hidden" id="jsMusicVideoPathOK"
                               value="<?php if (VIDEO_FILE_UPLOAD) echo $this->checkVideoFileUpload['result']; ?>">
                        <input type="hidden" id="jsMusicPathOK"
                               value="<?php if (MUSIC_FILE_UPLOAD) echo $this->checkAudioFileUpload['result']; ?>">
                    </div>

                </div>

                <?php
            }

        } else {
            echo '<p>' . __('youtube_error') . '</p>';
        }
    }

    /**
     * Display Upload files element
     *
     * @param $problematicPaths
     */
    public function displayUploadFilesElement($problematicPaths)
    {
        ?>
            <details>
                <summary> <?php echo __('upload_files'); ?> </summary>

                <div class="custom-file col-lg-6 col-12 px-1 my-1">
                    <input type="file" class="custom-file-input" name="jsMediaFiles" id="jsMediaFiles"
                           accept=".mp4, .m4v, .mp3, .m4a, .flac" onchange="UploadFiles.startUpload(<?php echo htmlentities(json_encode($problematicPaths)); ?>);" multiple>
                    <label class="custom-file-label" for="customFile">Choose files</label>
                </div>

            </details>
        <?php
    }

    /**
     * Έλεγχος αν οι φάκελοι υπάρχουν κι έχουν δικαιώματα εγγραφής
     *
     * @return array
     */
    public function checkFoldersPermissions()
    {
        // Το array με τα download paths
        global $downloadPaths;

        $problematicPaths = array();

        foreach ($downloadPaths as $key=>$path) {
            if (is_dir($path)) { // Αν υπάρχει ο φάκελος
                if (!is_writable($path)) {  // Αν δεν έχει δικαιώματα εγγραγφής
                    $problematicPaths[$key] = 5; // access denied error
                } else {
                    $problematicPaths[$key] = 0; // Success
                }
            } else { // Αν ο φάκελος δεν υπάρχει
                $problematicPaths[$key] = 3; // not found error
            }

        }

        return $problematicPaths;

    }

    /**
     * Display if there is a problem with a path
     *
     * @param $problematicPaths
     */
    public function displayFoldersPermissions($problematicPaths)
    {
        // Το array με τα download paths
        global $downloadPaths;

        foreach ($downloadPaths as $key=>$path) {
            if($problematicPaths[$key]!==0) { // If has an error
                switch ($problematicPaths[$key]) {
                    case 5: echo '<p class="isFail">ERROR! ' . __('cant_write_to_path') . ' ' . $path . '. ' . __('give_permissions') . '</p>'; break;
                    case 3: echo '<p class="isFail">' . __('path_does_not_exist') . ': ' . $path . '</p>'; break;
                }
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
            if (function_exists('gd_info')) {
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
            if (function_exists('xml_set_object')) {
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

    /**
     * Εμφανίζει τα διάφορα κουμπιά συγχρονισμού
     */
    static function getSyncJobsButtons()
    {
        $conn = new MyDB();
        $user = new User();
        MyDB::createConnection();

        global $mediaKinds;

        $UserGroupID = $user->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης

        if ($UserGroupID == 1) {
            ?>
            <div id="syncButtons">
                <form id="syncForm" name="syncForm">

                    <div class="row px-1 my-1 no-gutters">

                        <div class="form-group my-auto col-lg-6 col-12">
                            <label for="mediakind" class="sr-only">Label text</label>
                            <select class="form-control form-control-sm" id="mediakind" name="mediakind">
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

                        <div class="px-3 col-lg-6 col-12 my-auto">
                            <input type="button" class="btn btn-success w-75 syncButton" id="startSync" name="startSync"
                                   onclick="startTheSync('sync');"
                                   value="<?php echo __('Synchronize'); ?>">
                            <input type="hidden" id="jsGDOK"
                                   value="<?php if (function_exists('gd_info')) echo 'true'; else 'false'; ?>">
                            <?php Page::getHelp('help_sync'); ?>
                        </div>

                    </div>


                    <div class="row my-2 no-gutters">

                        <div class="col-lg-4 col-12 px-1 my-1">
                            <input type="button" class="btn btn-warning w-75 syncButton" id="startClear" name="startClear"
                                   onclick="startTheSync('clear');"
                                   value="<?php echo __('sync_clear'); ?>">
                            <?php Page::getHelp('help_clear_db'); ?>
                        </div>

                        <div class="col-lg-4 col-12 px-1 my-1">
                            <input type="button" class="btn btn-warning w-75 syncButton" id="startHash" name="startHash"
                                   onclick="startTheSync('hash');"
                                   value="<?php echo __('sync_hash'); ?>">
                            <?php Page::getHelp('help_hash'); ?>
                        </div>

                        <div class="col-lg-4 col-12 px-1 my-1">
                            <input type="button" class="btn btn-warning w-75 syncButton" id="startFileMetadata"
                                   name="startFileMetadata" onclick="startTheSync('metadata');"
                                   value="<?php echo __('sync_metadata'); ?>">
                            <?php Page::getHelp('help_metadata'); ?>
                        </div>
                    </div>

                    <div class="row my-2 no-gutters">

                        <div class="col-lg-4 col-12 px-1 my-1">
                            <input type="button" class="btn btn-warning w-75 syncButton" id="startJsonImport"
                                   name="startJsonImport" onclick="startTheSync('json_import');"
                                   value="<?php echo __('sync_json'); ?>">
                            <?php Page::getHelp('help_playlist_export'); ?>
                        </div>

                        <div class="col-lg-4 col-12 px-1 my-1">
                            <input type="button" class="btn btn-warning w-75 syncButton" id="startCoverConvert"
                                   name="startCoverConvert" onclick="startTheSync('coverConvert');"
                                   value="<?php echo __('cover_convert'); ?>">
                            <?php Page::getHelp('help_convert_covers'); ?>
                        </div>

                        <div class="col-lg-4 col-12 px-1 my-1">
                            <input type="button" class="btn btn-warning w-75 syncButton" id="backupDatabase" name="backupDatabase"
                                   onclick="startTheBackup();"
                                   value="<?php echo __('start_backup'); ?>">
                            <?php Page::getHelp('help_database_backup'); ?>
                        </div>
                    </div>

                    <!--                    <p>-->
                    <!--                        <input type="button" class="myButton syncButton" id="startUpdate" name="startUpdate" onclick="startTheUpdate();"-->
                    <!--                               value="Update">-->
                    <!--                    </p>-->

                    <div class="row my-2 no-gutters w-100">

                        <div class="custom-file col-lg-6 col-12 px-1 my-1">
                            <input type="file" class="custom-file-input" name="uploadSQLFile" id="uploadSQLFile"
                                   accept='.sql' onchange="jsUploadFile(this.files)">
                            <label class="custom-file-label" for="customFile">Choose file</label>
                        </div>

                        <!--                        <input type="file" name="jsMediaFiles" id="jsMediaFiles"-->
                        <!--                               accept=".sql"-->
                        <!--                               onchange="UploadFiles.startUpload();" multiple>-->

                        <div class="col-lg-6 col-12 px-1 my-1 text-center">
                            <input type="button" class="btn btn-danger w-75 syncButton" id="restoreDatabase" name="restoreDatabase"
                                   onclick="restoreTheBackup();"
                                   value="<?php echo __('start_restore'); ?>">

                            <?php Page::getHelp('help_database_backup'); ?>
                        </div>
                    </div>

                </form>

            </div>

            <?php
        } else {
            echo '<p>' . __('only_for_admin') . '</p>';
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
    static function getFilesDuplicates($offset, $step)
    {
        MyDB::createConnection();

        $sql = 'SELECT files.id as id, song_name, artist, genre, date_added, play_count, rating, song_year FROM files JOIN music_tags on files.id=music_tags.id WHERE hash IN (SELECT hash FROM OWMP.files GROUP BY hash HAVING count(*) > 1) ORDER BY hash';

        if (isset($offset))
            $sql = $sql . ' LIMIT ' . $offset . ',' . $step;

        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute();

        $result = $stmt->fetchAll();

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
        $counter = 0;
        foreach ($arrayToCopy as $item) {
            $newArray[] = array('id' => $counter, 'file_id' => $item['id']);
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

        $file = MyDB::getTableArray('files', '*', 'id=?', array($id), null, null, null);   // Παίρνει το συγκεκριμένο αρχείο

        $filesArray = array('path' => $file[0]['path'],
            'filename' => $file[0]['filename']);

        $fullPath = DIR_PREFIX . $filesArray['path'] . $filesArray['filename'];   // Το full path του αρχείου

        if (file_exists($fullPath)) {  // αν υπάρχει το αρχείο, σβήνει το αρχείο μαζί με την εγγραφή στην βάση
            if (unlink($fullPath)) {
                if ($deleteMusicTags = $conn->deleteRowFromTable('music_tags', 'id', $id))
                    if ($deleteFile = $conn->deleteRowFromTable('files', 'id', $id))
                        $result = true;
                    else $result = false;
            }
        } else {  // Αν δεν υπάρχει το αρχείο σβήνει μόνο την εγγραφή στην βάση
            if ($deleteMusicTags = $conn->deleteRowFromTable('music_tags', 'id', $id))
                if ($deleteFile = $conn->deleteRowFromTable('files', 'id', $id))
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

        $sql = 'SELECT path, filename FROM files WHERE id=?';

        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute(array($id));

        if ($item = $stmt->fetch(\PDO::FETCH_ASSOC))

            $result = $item['path'] . urldecode($item['filename']);

        else $result = false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    static function get_content($URL)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $URL);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * Εκτελεί την linux εντολή για μετατροπή ενός ALAC σε mp3
     *
     * @param $source {string} Το path του αρχικού αρχείου
     * @param $target {string} Το path του τελικού αρχείου
     * @param $bitrate {int} To bitrate της μετατροπής
     */
    static function execConvertALAC($source, $target, $bitrate)
    {
        // Μετατροπή ALAC σε απλό mp3. Το δημιουργεί καταρχήν σε temp dir (INTERNAL_CONVERT_PATH)
        print shell_exec('ffmpeg -i "' . $source . '" -ac 2 -f wav - | lame -b ' . $bitrate . ' - "' . $target . '" ');
    }

    /**
     * Δημιουργεί έναν νέο πίνακα για temporary playlist με το όνομα $table
     *
     * @param $table {string} To όνομα του πίνακα που θα δημιουργηθεί
     * @return bool True or False για την επιτυχία
     */
    static function createPlaylistTempTable($table, $temp = false)
    {
        $conn = new MyDB();
        MyDB::createConnection();

        $sql = 'CREATE TABLE ' . $table . ' (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `file_id` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=' . ($temp ? 'MEMORY' : 'InnoDB') . ' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;';

        $stmt = MyDB::$conn->prepare($sql);


        if ($stmt->execute())

            $result = true;

        else $result = false;

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
            self::createPlaylistTempTable($tempPlaylist, true); // Δημιουργούμε το table

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

        $sql = 'SELECT * FROM ' . $table . ' LIMIT ' . $tableCount . ',1';

        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute();

        if ($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $result = array(
                'playlist_id' => $item['id'],
                'file_id' => $item['file_id']
            );
        } else $result = false;

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
        if (!MyDB::getTableFieldValue($tempPlaylist, 'file_id=?', array($fileID), 'id')) {
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

        $userIP = $_SESSION['user_IP'];  // H ip του χρήστη

        $conn = new MyDB();

        if (!MyDB::getTableFieldValue('votes', 'voter_ip=?', $userIP, 'id')) {
            $sql = 'INSERT INTO votes (file_id,voter_ip) VALUES(?,?)';

            trigger_error('User vote ' . $userIP);

            if ($conn->insertInto($sql, array($fileID, $userIP))) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Προσθέτει μία ψήφο στο table votes
     *
     * @param $fileID {int} Το id του αρχείου στο οποίο θα προστεθεί μία ψήφος
     * @return bool True or False για την επιτυχία
     */
    static function queueSong($fileID)
    {
        $conn = new MyDB();

		$sql = 'INSERT INTO queue (file_id) VALUES(?)';

		if ($conn->insertInto($sql, array($fileID))) {
			return true;
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

        $sql = 'SELECT file_id, count(*) as numberOfVotes FROM votes GROUP BY file_id';

        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute();

        $result = $stmt->fetchAll();

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    /**
     * Επιστρέφει το επόμενο τραγούδι που είναι στην ουρά
     */
    static function getQueueSong()
    {
        MyDB::createConnection();

        $sql = 'SELECT file_id FROM queue LIMIT 1';

        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmt->closeCursor();
        $stmt = null;

        return $result['file_id'];
    }

    /**
     * Επιστρέφει σε πίνακα (song_name, artist) τα στοιχεία του τρέχοντος τραγουδιού
     *
     * @param $id {int} Το id του αρχείου
     * @return bool|mixed Array με τα στοιχεία του τραγουδιού ή False για αποτυχία
     */
    static function getSongInfo($id)
    {

        if (isset($id)) {
            $currentSongID = $id;
        } else {
            $currentSongID = Progress::getCurrentSong();
        }

        if ($currentSongID) { // Το id του τραγουδιού
            if ($currentSong = MyDB::getTableArray('music_tags', 'song_name, artist, id',
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
    static function sendToIcecast($songInfo)
    {

        $html = 'http://' . ICECAST_USER . ':' . ICECAST_PASS . '@' . ICECAST_SERVER . '/admin/metadata?mount=/' .
            ICECAST_MOUNT . '&mode=updinfo&song=' . urlencode($songInfo);

        $response = file_get_contents($html);
        $decoded = json_decode($response, true);


        if ($decoded) {
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
                                <input type="text" class="form-control form-control-sm" id="artist" name="artist"
                                       placeholder="<?php echo __('tag_artist'); ?>"
                                       maxlength="255">
                            </div>

                            <div class="form-group my-1">
                                <label for="album" class="sr-only"><?php echo __('tag_album'); ?></label>
                                <input type="text" class="form-control form-control-sm" id="album" name="album"
                                       placeholder="<?php echo __('tag_album'); ?>"
                                       maxlength="255">
                            </div>

                            <div class="row my-1">
                                <div class="form-group col-xl-4 col-sm-4 col-md-12 w-100 my-1">
                                    <label for="genre" class="sr-only"><?php echo __('tag_genre'); ?></label>
                                    <input type="text" class="form-control form-control-sm" id="genre" name="genre"
                                           placeholder="<?php echo __('tag_genre'); ?>"
                                           maxlength="20">
                                </div>

                                <div class="form-group col-xl-4 col-sm-4 col-md-12 w-100 my-1">
                                    <label for="year" class="sr-only"><?php echo __('tag_year'); ?></label>
                                    <input type="number" class="form-control form-control-sm" id="year" name="year"
                                           placeholder="<?php echo __('tag_year'); ?>">
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

                                <input type="range" id="tags_rating" name="tags_rating"
                                       oninput="printValue(tags_rating, tags_rating_output);"
                                       maxlength="5" max="5" min="0" value="0" list="tags_rating_ticks">

                                <output for="tags_rating" id="tags_rating_output">0</output>

                                <datalist id="tags_rating_ticks">
                                    <?php
                                    for ($i = 0; $i < 6; $i++) {
                                        ?>
                                        <option><?php echo $i; ?></option>
                                        <?php
                                    }
                                    ?>
                                </datalist>
                            </div>

                        </form>

                        <div id="myImage"></div>

                        <div class="custom-file my-2">
                            <input type="file" class="custom-file-input" name="uploadFile" id="uploadFile"
                                   accept='image/*' onchange="readImage(this.files);">
                            <label class="custom-file-label" for="customFile">Choose file</label>
                        </div>

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
            <select class="form-control form-control-sm" id="mediakind" name="mediakind"
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
                                <?php echo $playlist['playlist_name']; ?>
                            </option>

                            <?php
                        }
                        ?>
                    </select>

                    <div class="my-auto mx-2">
                            <span class="mdi mdi-play mdi-24px hasCursorPointer" id="playPlaylist"
                                  onclick="playMyPlaylist(0, <?php echo PLAYLIST_LIMIT; ?>, true);"
                                  title="<?php echo __('play_file'); ?>">
                            </span>

                        <span class="mdi mdi-playlist-plus mdi-24px hasCursorPointer" data-toggle="modal"
                              data-target="#insertPlaylistWindow"
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
                                <?php echo $playlist['playlist_name']; ?>
                            </option>

                            <?php
                        }
                        ?>
                    </select>
                </div>

                <div class="col-lg-3 px-1 w-100 my-auto text-center">
                    <span class="mdi mdi-playlist-plus mdi-24px hasCursorPointer" id="jsInsertSmartPlaylistClick"
                          title="<?php echo __('create_smart_playlist'); ?>"
                          onclick="displayInsertSmartPlaylistWindow();">
                    </span>
                    <span class="mdi mdi-delete mdi-24px hasCursorPointer" id="jdDeleteSmartPlaylistClick"
                          title="<?php echo __('delete_smart_playlist'); ?>" onclick="deleteSmartPlaylist();">
                    </span>
                    <span class="mdi mdi-content-save mdi-24px hasCursorPointer" id="jsSaveSmartPlaylist"
                          title="<?php echo __('save_smart_playlist'); ?>" onclick="saveSmartPlaylist();">
                    </span>
                    <span class="mdi mdi-email-open-outline mdi-24px hasCursorPointer" id="jsLoadSmartPlaylist"
                          title="<?php echo __('load_smart_playlist'); ?>" onclick="loadSmartPlaylist();">
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
        <div class="modal fade" id="insertPlaylistWindow" tabindex="-1" role="dialog"
             aria-labelledby="insertPlaylistWindow" aria-hidden="true">
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
                        <input type="button" class="btm btn-success btn-sm PlaylistButton col mx-1 my-1 px-1"
                               id="insertPlaylistButton" name="insertPlaylistButton" onclick="createPlaylist();"
                               value="<?php echo __('create_playlist'); ?>">
                        <input type="button" class="btm btn-danger btn-sm col mx-1 my-1 px-1" name="cancelPlaylist"
                               id="cancelPlaylist" value="<?php echo __('search_text_cancel'); ?>"
                               onclick="cancelCreatePlaylist();">
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
                        <input type="text" class="form-control form-control-sm" id="smartPlaylistName"
                               name="smartPlaylistName">
                    </div>
                    <input type="button" class="btm btn-success btn-sm PlaylistButton my-1 col-lg-3  w-100 ml-auto"
                           id="insertSmartPlaylistButton" name="insertSmartPlaylistButton"
                           onclick="createSmartPlaylist();"
                           value="<?php echo __('create_playlist'); ?>">
                    <input type="button" class="btm btn-danger btn-sm  my-1 col-lg-3  w-100 ml-auto"
                           name="cancelSmartPlaylist" id="cancelSmartPlaylist"
                           value="<?php echo __('search_text_cancel'); ?>" onclick="cancelCreateSmartPlaylist();">
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

        <div class="modal fade" id="insertSleepTimerWindow" tabindex="-1" role="dialog"
             aria-labelledby="insertSleepTimerWindow" aria-hidden="true">
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
                        <input type="button" class="btn btn-success btn-sm col my-1 mx-1 px-1"
                               id="startSleepTimerButton" name="startSleepTimerButton"
                               onclick="startSleepTimer();"
                               value="<?php echo __('start_sleep_timer'); ?>">
                        <input type="button" class="btn btn-danger btn-sm col my-1 mx-1 px-1" name="cancelSleepTimer"
                               id="cancelSleepTimer"
                               value="<?php echo __('search_text_cancel'); ?>" onclick="cancelTheSleepTimer();">
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

    /**
     * Display the fields options for select
     *
     * @param $fields
     */
    private function displayFieldsOptions($fields)
    {
        foreach ($fields as $field) {
            ?>
            <option value="<?php echo $field; ?>">
                <?php
                switch ($field) {
                    case 'song_name':
                        echo __('tag_title');
                        break;
                    case 'artist':
                        echo __('tag_artist');
                        break;
                    case 'genre':
                        echo __('tag_genre');
                        break;
                    case 'date_added':
                        echo __('tag_date_added');
                        break;
                    case 'play_count':
                        echo __('tag_play_count');
                        break;
                    case 'date_last_played':
                        echo __('tag_date_played');
                        break;
                    case 'rating':
                        echo __('tag_rating');
                        break;
                    case 'album':
                        echo __('tag_album');
                        break;
                    case 'video_height':
                        echo __('tag_video_height');
                        break;
                    case 'video_width':
                        echo __('tag_video_width');
                        break;
                    case 'filesize':
                        echo __('tag_filesize');
                        break;
                    case 'track_time':
                        echo __('tag_track_time');
                        break;
                    case 'song_year':
                        echo __('tag_year');
                        break;
                    case 'live':
                        echo __('tag_live');
                        break;
                    case 'album_artwork_id':
                        echo __('tag_album_artwork_id');
                        break;
                }
                ?>
            </option>

            <?php
        }
    }

    // Εμφάνιση του παραθύρου για αναζήτηση
    public function displaySearchWindow()
    {

        if ($_SESSION['PlaylistCounter'] == 0) {

            $fields = MyDB::getTableFields('music_tags', array('id'));

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

                                for ($counter = 0; $counter < 2; $counter++) {

                                    ?>

                                    <div id="searchRow<?php echo $counter; ?>"
                                         class="<?php if ($counter == 0) echo 'isHidden'; else echo 'isVisible'; ?>">
                                        <div class="row py-1 px-1 no-gutters">

                                            <div class="form-group col-lg-2 w-100 my-auto">
                                                <label for="search_field<?php echo $counter; ?>" class="sr-only">search_field<?php echo $counter; ?></label>
                                                <select class="form-control form-control-sm search_field"
                                                        name="search_field<?php echo $counter; ?>"
                                                        id="search_field<?php echo $counter; ?>">
                                                    <?php
                                                        $this->displayFieldsOptions($fields);
                                                    ?>
                                                </select>
                                            </div>


                                            <div class="form-group col-lg-2 w-100 my-auto">
                                                <label for="search_equality<?php echo $counter; ?>" class="sr-only">search_equality<?php echo $counter; ?></label>
                                                <select class="form-control form-control-sm search_equality"
                                                        name="search_equality<?php echo $counter; ?>"
                                                        id="search_equality<?php echo $counter; ?>">

                                                    <option value="equal">
                                                        <?php echo __('search_equal'); ?>
                                                    </option>

                                                    <option value="greater">
                                                        <?php echo __('search_greater'); ?>
                                                    </option>

                                                    <option value="less">
                                                        <?php echo __('search_less'); ?>
                                                    </option>

													<option value="not">
                                                        <?php echo __('search_not'); ?>
													</option>
                                                </select>
                                            </div>

                                            <div id="search_text_group<?php echo $counter; ?>"
                                                 class="search_text_group form-group col-lg-4 w-100 my-auto">
                                                <label for="search_text<?php echo $counter; ?>" class="sr-only">search_text<?php echo $counter; ?></label>
                                                <input type="text" class="form-control form-control-sm search_text"
                                                       name="search_text<?php echo $counter; ?>"
                                                       id="search_text<?php echo $counter; ?>">
                                            </div>

                                            <div class="form-group col-lg-2 w-100 my-auto">
                                                <label for="search_operator<?php echo $counter; ?>" class="sr-only">search_operator<?php echo $counter; ?></label>
                                                <select class="form-control form-control-sm search_operator"
                                                        name="search_operator<?php echo $counter; ?>"
                                                        id="search_operator<?php echo $counter; ?>">
                                                    <option value="OR">
                                                        <?php echo __('search_or'); ?>
                                                    </option>

                                                    <option value="AND">
                                                        <?php echo __('search_and'); ?>
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-2 w-100 text-right my-auto">
                                                <span class="mdi mdi-plus-box-outline mdi-24px hasCursorPointer"
                                                      id="jsAddSearchRow"
                                                      title="<?php echo __('add_search_row'); ?>"
                                                      onclick="addSearchRow();">
                                                </span>
                                                <span class="mdi mdi-minus-box-outline mdi-24px hasCursorPointer"
                                                      id="jsRemoveSearchRow"
                                                      title="<?php echo __('remove_search_row'); ?>"
                                                      onclick="removeSearchRow(<?php echo $counter; ?>);">
                                                </span>
                                                <span class="mdi mdi-plus-circle mdi-24px hasCursorPointer"
                                                      id="jsAddGroup"
                                                      title="<?php echo __('add_group_row'); ?>"
                                                      onclick="addOrAndToGroup(<?php echo $counter; ?>);">
                                                </span>
                                            </div>

                                        </div>
                                    </div>

                                    <?php
                                }
                                ?>
                            </form>
                        </div>

                        <div class="form-group row col-lg-8 col-12 w-100 mx-auto mb-2">
                            <label for="sort_by" class="input-group-text form-control col-4"><?php echo __('sort_by'); ?></label>
                            <select class="form-control form-control col-4" name="sort_by" id="sort_by">
                                <?php
                                    $this->displayFieldsOptions($fields);
                                ?>
                            </select>

                            <select class="form-control form-control col-4" name="order" id="order">
                                <option value="DESC"><?php echo __('order_desc'); ?></option>
                                <option value="ASC"><?php echo __('order_asc'); ?></option>
                            </select>
                        </div>

                        <div id="searchButtons" class="modal-footer row w-100 no-gutters">
                            <input type="button" class="btn btn-success col px-1 my-1 mx-1" name="searching"
                                   id="searching"
                                   value="<?php echo __('search_text_search'); ?>"
                                   onclick="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>, true, false);">

                            <input type="button" class="btn btn-dark col px-1 my-1 mx-1" name="duplicates"
                                   id="duplicates"
                                   value="<?php echo __('search_text_duplicates'); ?>"
                                   onclick="findDuplicates(0,<?php echo PLAYLIST_LIMIT; ?>, true);">

                            <input type="button" class="btn btn-dark col px-1 my-1 mx-1" name="playedQueue"
                                   id="playedQueue"
                                   value="<?php echo __('search_text_played_queue'); ?>"
                                   onclick="loadPlayedQueuePlaylist();">

                            <input type="button" class="btn btn-warning col px-1 my-1 mx-1" name="jsClearSearch"
                                   id="jsClearSearch"
                                   value="<?php echo __('search_text_clear'); ?>" onclick="clearSearch();">

                            <input type="button" class="btn btn-danger col px-1 my-1 mx-1" name="cancelSearch"
                                   id="cancelSearch"
                                   value="<?php echo __('search_text_cancel'); ?>" onclick="cancelTheSearch();">
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
            var liveOptions = <?php echo json_encode([__('tag_live_official'), __('tag_live_live')]); ?>;

            var ratingOptions = <?php echo json_encode([0, 1, 2, 3, 4, 5]); ?>;

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
        if ($UserGroup == 1) {
            ?>
            <div>
                <span class="mdi mdi-delete mdi-24px hasCursorPointer"
                      title="<?php echo __('delete_file'); ?>"
                      onclick="deleteFile(0);">
                </span>

                <span class="mdi mdi-pencil mdi-24px hasCursorPointer" data-toggle="modal" data-target="#editTag"
                      title="<?php echo __('edit_file'); ?>"
                      onclick="openMassiveTagsWindow();">
                </span>

                <span class="mdi mdi-file-export mdi-24px hasCursorPointer"
                      title="<?php echo __('export_playlist'); ?>"
                      onclick="exportPlaylist();">
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
    public function displayPlaylistContainer($offset, $step)
    {
        ?>
        <div id="playlist_container">

            <?php
            $playlist = new PlaylistSearch();

            if ($_SESSION['PlaylistCounter'] == 0) {
                $_SESSION['condition'] = null;   // Μηδενίζει το τρέχον search query
                $_SESSION['arrayParams'] = null;

                $playlist->fieldsArray = null;
                $playlist->offset = $offset;
                $playlist->step = $step;
                $playlist->duplicates = null;
                $playlist->mediaKind = null;
                $playlist->tabID = null;
                $playlist->loadPlaylist = null;
                $playlist->votePlaylist = false;
                $playlist->getPlaylist();
            } else {
                ?>

                <div id="playlist_content"></div>

                <?php
            }

            ?>
        </div>
        <?php
    }

    /**
     * Display the results container window
     */
    public function displayResultsContainer()
    {
        ?>

        <div class="modal fade" id="o-resultsContainer" tabindex="-1" role="dialog" aria-labelledby="o-resultsContainer" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="elementModalLabel">Activity</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">


                        <div class="o-resultsContainer_text"> </div>


                    </div>

                    <div class="modal-footer row w-100 no-gutters">
                        <input type="button" class="btn btn-dark ml-auto mr-auto"
                               value="<?php echo __('close_text'); ?>"
                               onclick="$('#o-resultsContainer').modal('hide');">
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

}
