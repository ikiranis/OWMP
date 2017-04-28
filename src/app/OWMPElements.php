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

    // Εμφανίζει την μπάρα με τα controls
    static function displayControls($element, $fullscreen)
    {
        ?>

        <div id="<?php echo $element; ?>">

            <input type="button" class="<?php if($fullscreen) echo 'prev_button_white fullscreen_button_img'; else echo 'prev_button_black video_controls_button_img'; ?>"
                   title="<?php echo __('previous_song'); ?>"
                   onclick="prevSong();">

            <input type="button" class="pause_play_button <?php if($fullscreen) echo 'play_button_white fullscreen_button_img'; else echo 'play_button video_controls_button_img'; ?>"
                   title="<?php echo __('play_file'); ?>"
                   onclick="playSong();">

            <input type="button" class="<?php if($fullscreen) echo 'next_button_white fullscreen_button_img'; else echo 'next_button_black video_controls_button_img'; ?>"
                   title="<?php echo __('next_song'); ?>"
                   onclick="nextSong();">

            <input type="button" class="<?php if($fullscreen) echo 'fullscreen_button_minimize fullscreen_button_img'; else echo 'fullscreen_button_maximize video_controls_button_img'; ?>"
                   title="<?php echo __('toggle_fullscreen'); ?>"
                   onclick="toggleFullscreen();">


            <?php

            if($fullscreen) { // Αν είναι σε fullscreen
                ?>
                <input type="button"
                       class="<?php if ($fullscreen) echo 'gif_button fullscreen_button_img'; else echo 'gif_button video_controls_button_img'; ?>"
                       title="<?php echo __('toggle_giphy'); ?>"
                       onclick="giphyToggle();">

                <input type="button"
                       class="<?php if ($fullscreen) echo 'fullscreen_button_info fullscreen_button_img'; else echo 'fullscreen_button_info video_controls_button_img'; ?>"
                       title="<?php echo __('toggle_overlay'); ?>"
                       onclick="interfaceToggle();">

                <?php
            } else { // Αν δεν είναι σε fullscreen
                ?>
                <input type="button" class="<?php echo 'shuffle_button video_controls_button_img'; ?>"
                       title="<?php echo __('toggle_shuffle'); ?>"
                       onclick="toggleShuffle();">

                <?php
            }

            ?>
        </div>

        <?php
    }

    // Εμφανίζει τα browse buttons
    static function getBrowseButtons($operation, $offset, $step)
    {

        if($operation=='search') {
            ?>

            <div id="browseButtons">
                <input id="previous" class="myButton" type="button" value="<?php echo __('search_previous'); ?>"
                       onclick="searchPlaylist(<?php if ($offset > 0) echo $offset - $step; else echo '0'; ?>,<?php echo $step; ?>);">
                <input id="next" class="myButton" type="button" value="<?php echo __('search_next'); ?>"
                       onclick="searchPlaylist(<?php if (($offset + $step) < $_SESSION['$countThePlaylist']) echo $offset + $step; else echo $offset; ?>,<?php echo $step; ?>);">
            </div>

            <?php
        }

        if($operation=='duplicates') {
            ?>

            <div id="browseButtons">
                <input id="previous" class="myButton" type="button" value="<?php echo __('search_previous'); ?>"
                       onclick="findDuplicates(<?php if ($offset > 0) echo $offset - $step; else echo '0'; ?>,<?php echo $step; ?>);">
                <input id="next" class="myButton" type="button" value="<?php echo __('search_next'); ?>"
                       onclick="findDuplicates(<?php if (($offset + $step) < $_SESSION['$countThePlaylist']) echo $offset + $step; else echo $offset; ?>,<?php echo $step; ?>);">
            </div>

            <?php
        }

        if($operation=='votePlaylist') {
            ?>

            <div id="browseButtons">
                <input id="previous" class="myButton" type="button" value="<?php echo __('search_previous'); ?>"
                       onclick="getVotePlaylist(<?php if ($offset > 0) echo $offset - $step; else echo '0'; ?>,<?php echo $step; ?>);">
                <input id="next" class="myButton" type="button" value="<?php echo __('search_next'); ?>"
                       onclick="getVotePlaylist(<?php if (($offset + $step) < $_SESSION['$countThePlaylist']) echo $offset + $step; else echo $offset; ?>,<?php echo $step; ?>);">
            </div>

            <?php
        }


    }

    // Εμφανίζει την πρώτη γραμμή με τις επικεφαλίδες στην playlist
    static function displayPlaylistTitle()
    {
        ?>

        <div class="tag kind"></div>

        <div class="tag delete_file">
            <input type="checkbox" id="checkAll" name="checkAll"
                   onchange="changeCheckAll('checkAll', 'check_item[]');">
        </div>


        <div class="tag song_name playlistTittle" title="<?php echo __('tag_title'); ?>">
            <?php echo __('tag_title'); ?>
        </div>
        <div class="tag artist playlistTittle" title="<?php echo __('tag_artist'); ?>">
            <?php echo __('tag_artist'); ?>
        </div>
        <div class="tag album playlistTittle" title="<?php echo __('tag_album'); ?>">
            <?php echo __('tag_album'); ?>
        </div>
        <div class="tag genre playlistTittle" title="<?php echo __('tag_genre'); ?>">
            <?php echo __('tag_genre'); ?>
        </div>
        <div class="tag song_year playlistTittle" title="<?php echo __('tag_year'); ?>">
            <?php echo __('tag_year'); ?>
        </div>
        <div class="tag play_count playlistTittle" title="<?php echo __('tag_play_count'); ?>">
            <?php echo __('tag_play_count'); ?>
        </div>
        <div class="tag rating playlistTittle" title="<?php echo __('tag_rating'); ?>">
            <?php echo __('tag_rating'); ?>
        </div>
        <div class="tag date_added playlistTittle" title="<?php echo __('tag_date_added'); ?>">
            <?php echo __('tag_date_added'); ?>
        </div>

        <?php
    }

    // Επιστρέφει $searchArray για ένα πεδίο και την τιμή του
    static function getSearchArray($field, $value)
    {
        $searchArray []= array ('search_field' => $field, 'search_text' => $value,
            'search_operator'=> 'OR', 'search_equality' => 'equal');

        return $searchArray;
    }

    // Εμφανίζει την playlist με πλήρη τα στοιχεία
    static function displayFullPlaylist($track, $loadPlaylist=null, $votePlaylist=null)
    {

        if(!$votePlaylist) {
            $conn = new MyDB();
            $user = new User();

            $UserGroupID = $user->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης
        }

        ?>

        <div id="fileID<?php echo $track['id']; ?>" class="track"
             onmouseover="displayCoverImage('fileID<?php echo $track['id']; ?>');"
             onmouseout="hideCoverImage();">


            <div
                class="tag kind <?php if ($track['kind'] == 'Music') echo 'kind_music'; else echo 'kind_music_video'; ?>"
                title="<?php if ($track['kind'] == 'Music') echo 'Music'; else echo 'Music Video'; ?>"></div>


            <div class="tag delete_file">

                <?php

                if ($track['kind'] == 'Music') {
                    if($coverImagePath = self::getAlbumImagePath($track['album_artwork_id'], 'small')) {

                        ?>
                        <img class="coverImage" src="<?php echo $coverImagePath; ?>">
                        <?php
                    }
                }
                ?>

                <input type="checkbox" id="check_item[]" name="check_item[]"
                       value="<?php echo $track['id']; ?>">

                <input type="button" class="play_button playlist_button_img"
                       title="<?php echo __('play_file'); ?>"
                       onclick="loadNextVideo(<?php echo $track['id']; ?>); myVideo.play()">

                <input type="button" class="vote_button playlist_button_img"
                       title="<?php echo __('vote_song'); ?>"
                       onclick="voteSong(<?php echo $track['id']; ?>);">

                <?php
                if (!$loadPlaylist) { ?>
                    <input type="button" class="playlist_add_button playlist_button_img"
                           title="<?php echo __('add_to_playlist'); ?>"
                           onclick="addToPlaylist(<?php echo $track['id']; ?>);">
                    <?php
                } else { ?>
                    <input type="button" class="playlist_remove_button playlist_button_img"
                           title="<?php echo __('remove_from_playlist'); ?>"
                           onclick="removeFromPlaylist(<?php echo $track['id']; ?>);">
                    <?php
                }
                ?>

                <?php
                if ($UserGroupID == 1) {
                    ?>
                    <input type="button" class="delete_button playlist_button_img"
                           title="<?php echo __('delete_file'); ?>"
                           onclick="deleteFile(<?php echo $track['id']; ?>);">
                    <?php
                }
                ?>
            </div>


            <div class="tag song_name" title="<?php echo $track['song_name']; ?>">
                <span class="searchableItem" onclick="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>,true,
                <?php echo htmlentities(json_encode(OWMPElements::getSearchArray('song_name', $track['song_name']))); ?>);">
                    <?php echo $track['song_name']; ?>
                </span>
            </div>
            <div class="tag artist" title="<?php echo $track['artist']; ?>">
                <span class="searchableItem"  onclick="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>,true,
                <?php echo htmlentities(json_encode(OWMPElements::getSearchArray('artist', $track['artist']))); ?>);">
                    <?php echo $track['artist']; ?>
                </span>
            </div>
            <div class="tag album" title="<?php echo $track['album']; ?>">
                <span class="searchableItem"  onclick="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>,true,
                <?php echo htmlentities(json_encode(OWMPElements::getSearchArray('album', $track['album']))); ?>);">
                    <?php echo $track['album']; ?>
                </span>
            </div>
            <div class="tag genre" title="<?php echo $track['genre']; ?>">
                <span class="searchableItem"  onclick="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>,true,
                <?php echo htmlentities(json_encode(OWMPElements::getSearchArray('genre', $track['genre']))); ?>);">
                    <?php echo $track['genre']; ?>
                </span>
            </div>
            <div class="tag song_year"
                 title="<?php if ($track['song_year'] == '0') echo ''; else echo $track['song_year']; ?>">
                <span class="searchableItem"  onclick="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>,true,
                <?php echo htmlentities(json_encode(OWMPElements::getSearchArray('song_year', $track['song_year']))); ?>);">
                    <?php if ($track['song_year'] == '0') echo ''; else echo $track['song_year']; ?>
                </span>
            </div>
            <div class="tag play_count" title="<?php echo $track['play_count']; ?>">
                <?php echo $track['play_count']; ?>
            </div>
            <div class="tag rating" title="<?php echo(($track['rating'] / 20)); ?>">
                <?php echo(($track['rating'] / 20)); ?>
            </div>
            <div class="tag date_added" title="<?php echo $track['date_added']; ?>">
                <?php echo date(DATE_FORMAT, strtotime($track['date_added'])); ?>
            </div>
        </div>

        <?php
    }

    // Εμφανίζει τα περιεχόμενα της playlist με ελάχιστα στοιχεία
    static function displaySmallPlaylist($track)
    {
        ?>

        <div id="fileID<?php echo $track['id']; ?>" class="track">

            <div class="tag delete_file">
                <input type="button" class="vote_button playlist_button_img"
                       title="<?php echo __('vote_song'); ?>"
                       onclick="voteSong(<?php echo $track['id']; ?>);">
            </div>

            <div class="tag song_name">
                <span class="the_song_name"><?php echo $track['song_name']; ?></span>
                <span class="the_song_artist"><?php echo $track['artist']; ?></span>
            </div>

        </div>

        <?php
    }

    // Εμφανίζει την playlist με βάση διάφορα keys αναζήτησης
    static function getPlaylist($fieldsArray=null, $offset, $step, $duplicates=null, $mediaKind=null, $tabID=null,
                                $loadPlaylist=null, $votePlaylist)
    {

        $condition='';
        $arrayParams=array();


//        print_r($fieldsArray);

        if($fieldsArray) {
            foreach ($fieldsArray as $field) {

                if ($field['search_text'] === '0')  // Βάζει ένα κενό όταν μηδέν, αλλιώς το νομίζει null
                    $searchText = ' ' . $field['search_text'];
                else
                    $searchText = $field['search_text'];


                if ((!$field == null) && (!$searchText == null)) {  // αν ο πίνακας δεν είναι κενός και αν το search text δεν είναι κενό

                    $fieldType = MyDB::getTableFieldType('music_tags', $field['search_field']);  // παίρνει το type του field
//                    trigger_error($fieldType);
                    if ($fieldType == 'int(11)' || $fieldType == 'tinyint(4)' || $fieldType == 'datetime') {   // αν το type είναι νούμερο
                        if ($fieldType == 'datetime')
                            $searchText = $field['search_text'];
                        else {
                            if ($field['search_field'] == 'rating')
                                $searchText = intval($field['search_text']) * 20;
                            else $searchText = intval($field['search_text']);  // μετατροπή του κειμένου σε νούμερο
                        }

                        $equality = $field['search_equality'];
                        switch ($equality) {
                            case 'equal':
                                $equality_sign = '=';
                                break;
                            case 'greater':
                                $equality_sign = '>';
                                break;
                            case 'less':
                                $equality_sign = '<';
                                break;
                        }

                        $condition = $condition . $field['search_field'] . $equality_sign . '? ' . $field['search_operator'] . ' ';
                        $arrayParams[] = $searchText;
                    } else {   // αν είναι string
                        $searchText = ClearString($field['search_text']);
                        $condition = $condition . $field['search_field'] . ' LIKE ? ' . $field['search_operator'] . ' ';
                        $arrayParams[] = '%' . $searchText . '%';
                    }


                }
            }
        }

        if (!$condition=='') {
            $condition = Utilities::cutLastString($condition, 'OR ');
//            $condition = page::cutLastString($condition, 'AND ');

            $_SESSION['condition']=$condition;  // Το κρατάει σε session για μελοντική χρήση
            $_SESSION['arrayParams']=$arrayParams;
        }
        else {
            $condition=null;
            $_SESSION['condition']=null;  // Το κρατάει σε session για μελοντική χρήση
            $_SESSION['arrayParams']=null;
        }



        if(isset($_SESSION['condition']))
            $condition=$_SESSION['condition'];

        if(isset($_SESSION['arrayParams']))
            $arrayParams=$_SESSION['arrayParams'];

        // Επιλογές για join ώστε να πάρουμε το media kind από το files
        if(isset($mediaKind)) {
            if (!$condition=='')
                $condition = '(' . $condition . ')' . ' AND files.kind=? ';
            else $condition.=  ' files.kind=? ';

            $arrayParams[]=$mediaKind; // προσθέτει και την παράμετρο του $mediakind στις παραμέτρους του query
        }


        if(!isset($_SESSION['PlaylistCounter'])){
            $_SESSION['PlaylistCounter']=0;
            $_SESSION['condition']=null;
            $_SESSION['arrayParams']=null;
        }

        if(!$loadPlaylist)
            $joinFieldsArray= array('firstField'=>'id', 'secondField'=>'id');
        else $joinFieldsArray= array('firstField'=>'id', 'secondField'=>'file_id');

        $playlistToPlay=null;
        $playlist=null;

        if(!$votePlaylist) {
            if (!$tabID)  // Αν δεν έρχεται από function
                $tabID = TAB_ID;  // Την πρώτη φορά που τρέχει η εφαρμογή το παίρνει από το TAB_ID
        }



        // Το όνομα του temporary user playlist table για τον συγκεκριμένο χρήστη
        if($votePlaylist) {
            $tempUserPlaylist = JUKEBOX_LIST_NAME;
        } else {
            $tempUserPlaylist = CUR_PLAYLIST_STRING . $tabID;
        }

        $tempPlayedQueuePlaylist=PLAYED_QUEUE_PLAYLIST_STRING . $tabID;


        if($duplicates==null) {   // κανονική λίστα
            if ($_SESSION['PlaylistCounter'] == 0) {
//                $playlistToPlay = MyDB::getTableArray('music_tags', 'music_tags.id', $condition, $arrayParams, 'date_added DESC', 'files', $joinFieldsArray); // Ολόκληρη η λίστα


                // Αν είναι true το $loadPlaylist τότε δεν χρειάζεται να δημιουργηθεί temporary table. Υπάρχει ήδη
                // από την manual playlist
                if(!$loadPlaylist) {
                    $myQuery = MyDB::createQuery('music_tags', 'music_tags.id', $condition, 'date_added DESC', 'files', $joinFieldsArray);


                    // Αν δεν υπάρχει ήδη το σχετικό table το δημιουργούμε
                    self::checkTempPlaylist($tempUserPlaylist);

                    // Δημιουργία και ενός played queue playlist
                    self::checkTempPlaylist($tempPlayedQueuePlaylist);

                    // αντιγραφή του playlist σε αντίστοιχο $tempUserPlaylist table ώστε ο player να παίζει από εκεί
                    MyDB::copyFieldsToOtherTable('file_id', $tempUserPlaylist, $myQuery, $arrayParams);
                }

                $tableCount = MyDB::countTable($tempUserPlaylist);

                $_SESSION['$countThePlaylist'] = $tableCount;
            }

            // Η λίστα προς εμφάνιση
            if(!$loadPlaylist) {  // Αν το $loadPlaylist είναι false
                $playlist = MyDB::getTableArray('music_tags', null, $condition, $arrayParams,
                    'date_added DESC LIMIT ' . $offset . ',' . $step, 'files', $joinFieldsArray);
            }
            else { // αλλιώς κάνει join με τον $tempUserPlaylist
                $joinFieldsArray = array('firstField' => 'id', 'secondField' => 'file_id');
                $mainTables = array('music_tags', 'files');

                $playlist = MyDB::getTableArray($mainTables, 'music_tags.*, files.path, files.filename, files.hash, files.kind',
                    null, null, 'date_added DESC LIMIT ' . $offset . ',' . $step, $tempUserPlaylist, $joinFieldsArray);
            }



        }
        else {  // εμφάνιση διπλών εγγραφών
//                $playlistToPlay = self::getFilesDuplicates(null,null); // Ολόκληρη η λίστα

            if (!$tabID)  // Αν δεν έρχεται από function
                $tabID = TAB_ID;  // Την πρώτη φορά που τρέχει η εφαρμογή το παίρνει από το TAB_ID

            // Το όνομα του temporary user playlist table για τον συγκεκριμένο χρήστη
            $tempUserPlaylist = CUR_PLAYLIST_STRING . $tabID;

            // Την πρώτη φορά αντιγράφει την λίστα των διπλοεγγραφών στην $tempUserPlaylist
            if ($_SESSION['PlaylistCounter'] == 0) {

                $myQuery = 'SELECT files.id as file_id
                            FROM files JOIN music_tags on files.id=music_tags.id 
                            WHERE hash IN (SELECT hash FROM OWMP.files GROUP BY hash HAVING count(*) > 1) ORDER BY hash';

                // αντιγραφή του playlist σε αντίστοιχο $tempUserPlaylist table ώστε ο player να παίζει από εκεί
                MyDB::copyFieldsToOtherTable('file_id', $tempUserPlaylist, $myQuery, null);

                $tableCount = MyDB::countTable($tempUserPlaylist);

                $_SESSION['$countThePlaylist'] = $tableCount;
            }


            // Κάνει join την $tempUserPlaylist με τα music_tags και files για εμφάνιση της playlist
            $joinFieldsArray = array('firstField' => 'id', 'secondField' => 'file_id');
            $mainTables = array('music_tags', 'files');

            $playlist = MyDB::getTableArray($mainTables, 'music_tags.*, files.path, files.filename, files.hash, files.kind',
                null, null, 'files.hash DESC LIMIT ' . $offset . ',' . $step, $tempUserPlaylist, $joinFieldsArray);

        }


        $counter=0;

//        if(!$votePlaylist) {
//            $UserGroupID = $conn->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης
//        }


        // Αρχίζει η εμφάνιση της playlist
        if($playlist) {
            ?>

            <div id="playlist_content">

                <?php
                // TODO δεν παίζουν οι σελίδες όταν εμφανίζει manual playlists ή την ουρά

                if (!$duplicates && !$votePlaylist) {
                    OWMPElements::getBrowseButtons('search', $offset, $step);
                } else {
                    if($duplicates) {
                        OWMPElements::getBrowseButtons('duplicates', $offset, $step);
                    }

                    if($votePlaylist) {
                        OWMPElements::getBrowseButtons('votePlaylist', $offset, $step);
                    }
                }

                ?>

                <div id="playlistTable">
                    <?php

                    // Αν δεν είναι η σελίδα vote εμφανίζει τον τίτλο
                    if(!$votePlaylist && !$_SESSION['mobile']) {
                        OWMPElements::displayPlaylistTitle();
                    }


                    foreach ($playlist as $track) {

                        if(!$votePlaylist && !$_SESSION['mobile']) { // Αν δεν είναι η σελίδα vote ή mobile
                            // Εμφανίζει την λίστα με τα πλήρη στοιχεία
                            OWMPElements::displayFullPlaylist($track,$loadPlaylist,$votePlaylist);
                        } else { // Αν είναι η σελίδα vote
                            // Εμφανίζει την λίστα με ελάχιστα στοιχεία
                            OWMPElements::displaySmallPlaylist($track);
                        }

                        $counter++;
                    }


                    $offset = intval($offset);
                    $step = intval($step);
                    ?>


                </div>

                <?php


                // Εμφάνιση των browse buttons
                if (!$duplicates && !$votePlaylist) {
                    OWMPElements::getBrowseButtons('search', $offset, $step);
                } else {
                    if($duplicates) {
                        OWMPElements::getBrowseButtons('duplicates', $offset, $step);
                    }

                    if($votePlaylist) {
                        OWMPElements::getBrowseButtons('votePlaylist', $offset, $step);
                    }
                }

                ?>

                <div id="error_container">
                    <div id="alert_error"></div>
                </div>

            </div>

            <script type="text/javascript">
                var playlistCount = <?php echo $_SESSION['$countThePlaylist']; ?>;
            </script>

            <?php
        }

        if ($_SESSION['PlaylistCounter'] == 0 && !$votePlaylist) {
            ?>

            <script type="text/javascript">
                init();
            </script>

            <?php
        }

        $_SESSION['PlaylistCounter']++;

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
        $conn->CreateConnection();

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
                               pattern='^[a-zA-Z][a-zA-Z0-9-_\.]{4,15}$'
                               maxlength="15" required type="text" name="username" value="<?php echo $item['username']; ?>">
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
        <div class="ListTable">

            <?php



            foreach($paths as $path)
            {
                ?>
                <div class="PathsRow" id="PathID<?php echo $path['id']; ?>">
                    <form class="table_form paths_form" id="paths_formID<?php echo $path['id']; ?>">

                        <span class="ListColumn"><input class="input_field"
                                                        placeholder="<?php echo __('paths_file_path'); ?>"
                                                        maxlength="255" required type="text" name="file_path" id="file_path"
                                                        value="<?php echo $path['file_path']; ?>"
                                                        onclick="displayBrowsePath('paths_formID<?php echo $path['id']; ?>');" ></span>

                        <span class="ListColumn">
                            <select class="input_field" name="kind" id="kind" >
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
                        </span>

                        <input type="button" class="update_button button_img" name="update_path" title="<?php echo __('update_row'); ?>" onclick="updatePath(<?php echo $path['id']; ?>);"">

                        <input type="button" class="delete_button button_img <?php if($counter==1) echo 'dontDelete'; ?>" name="delete_path" title="<?php echo __('delete_row'); ?>" onclick="deletePath(<?php echo $path['id']; ?>);"">

                        <input type="button" class="message" id="messagePathID<?php echo $path['id']; ?>">
                    </form>

                </div>
                <?php
                $counter++;
            }
            ?>

        </div>
        <input type="button" class="myButton insert_row" name="insert_path" onclick="insertPath();" value="<?php echo __('insert_row'); ?>">

        <?php
    }

    // Εμφανίζει τα input για επιλογή των διάφορων download paths
    static function getDownloadPaths()
    {

        // Τα αποτελέσματα του download_paths σε array
        $downloadPathsArray = MyDB::getTableArray('download_paths', null, null, null, null, null, null);

        ?>
        <div class="ListTable">

            <?php

            foreach ($downloadPathsArray as $item) {
                ?>

                <div class="PathsRow" id="<?php echo $item['path_name']; ?>">
                    <form class="table_form paths_form" id="form<?php echo $item['path_name']; ?>">
                        <span class="ListColumn"><input class="input_field" disabled
                                                        placeholder="<?php echo __('options_option'); ?>"
                                                        type="text" name="option_name" value="<?php echo $item['path_name']; ?>"></span>
                        <span class="ListColumn"><input class="input_field"
                                                        placeholder="<?php echo __('paths_file_path'); ?>"
                                                        maxlength="255" required type="text" name="file_path"
                                                        id="file_path"
                                                        value="<?php echo $item['file_path']; ?>"
                                                        onclick="displayBrowsePath('form<?php echo $item['path_name']; ?>');"></span>

                        <input type="button" class="update_button button_img" name="update_path"
                               title="<?php echo __('update_row'); ?>"
                               onclick="updateDownloadPath('<?php echo $item['path_name']; ?>');">

                        <input type="button" class="message" id="message_<?php echo $item['path_name']; ?>">
                    </form>
                </div>

                <?php
            }
            ?>

        </div>

        <?php
    }

    // Εμφάνιση των στοιχείων για κατέβασμα από YouTube
    static function displayYoutubeDownloadElements()
    {
        global $mediaKinds;

        if(VIDEO_FILE_UPLOAD || MUSIC_FILE_UPLOAD) { // Έλεγχος αν έχει οριστεί κάποιο FILE_UPLOAD αλλιώς να μην ενεργοποιεί το κουμπί του youtube

            if(VIDEO_FILE_UPLOAD) {
                $checkVideoFileUpload = FilesIO::createDirectory(VIDEO_FILE_UPLOAD);

                if(!$checkVideoFileUpload['result']) {
                    echo $checkVideoFileUpload['message'];
                }
            } else {
                echo '<p class="general_fail">'.__('no_main_music_video_path').'</p>';
            }

            if(MUSIC_FILE_UPLOAD) {
                $checkAudioFileUpload = FilesIO::createDirectory(MUSIC_FILE_UPLOAD);

                if(!$checkAudioFileUpload['result']) {
                    echo $checkAudioFileUpload['message'];
                }
            } else {
                echo '<p class="general_fail">'.__('no_main_music_path').'</p>';
            }


            if( $checkVideoFileUpload['result'] || $checkAudioFileUpload['result'] ) {

                ?>
                <div>
                    <textarea id="youTubeUrl" name="youTubeUrl"></textarea>

                    <select name="youtubeMediaKind" id="youtubeMediaKind">
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

                    <input type="button" class="myButton syncButton" id="downloadYouTube" name="downloadYouTube"
                           onclick="downloadTheYouTube();"
                           value="<?php echo __('sync_youtube'); ?>" >

                    <input type="hidden" id="MusicVideoPathOK" value="<?php if(VIDEO_FILE_UPLOAD) echo $checkVideoFileUpload['result']; ?>">
                    <input type="hidden" id="MusicPathOK" value="<?php if(MUSIC_FILE_UPLOAD) echo $checkAudioFileUpload['result']; ?>">

                </div>

                <?php
            }

        }
        else {
            echo '<p>'.__('youtube_error').'</p>';
        }
    }

    // Έλεγχος και εμφάνιση απαιτήσεων
    static function checkRequirements()
    {
        ?>

        <p>ffmpeg:
            <?php
            if (Utilities::checkIfLinuxProgramInstalled('ffmpeg')) {
                echo 'Installed';
            } else {
                echo 'Not Installed';
            }
            ?>
        </p>

        <p>lame:
            <?php
            if (Utilities::checkIfLinuxProgramInstalled('lame')) {
                echo 'Installed';
            } else {
                echo 'Not Installed';
            }
            ?>
        </p>

        <p>youtube-dl:
            <?php
            if (Utilities::checkIfLinuxProgramInstalled('youtube-dl')) {
                echo 'Installed';
            } else {
                echo 'Not Installed';
            }
            ?>
        </p>

        <?php
    }

    // Εμφανίζει τα διάφορα κουμπιά συγχρονισμού
    static function getSyncJobsButtons()
    {
        $conn = new MyDB();
        $user = new User();
        $conn->CreateConnection();

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
                        <input type="file" name="uploadSQLFile" id="uploadSQLFile" onchange="uploadFile(this.files)">

                        <input type="button" class="myButton syncButton" id="restoreDatabase" name="restoreDatabase" onclick="restoreTheBackup();"
                               value="<?php echo __('start_restore'); ?>">
                        <?php Page::getHelp('help_database_backup'); ?>
                    </p>

                </form>

            </div>

            <?php
        }
        else echo '<p>'.__('only_for_admin').'</p>';
    }

    // Επιστρέφει τις διπλές εγγραφές με βάση το hash
    // Δεν χρησιμοποιείται
    static function getFilesDuplicates ($offset, $step)
    {
        $conn = new MyDB();

        $conn->createConnection();

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

    // Δημιουργεί ένα κατάλληλο array ώστε να αντιγραφεί σε προσωρινό table
    // Δεν χρησιμοποιείται
    static function makePlaylistArrayToCopy($arrayToCopy)
    {
        $counter=0;
        foreach($arrayToCopy as $item) {
            $newArray[]=array('id'=>$counter, 'file_id'=>$item['id']);
            $counter++;
        }

        return $newArray;
    }

    // Σβήνει ένα αρχείο και την αντίστοιχη εγγραφή στην βάση
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

    // Επιστρέφει τo fullpath από τα files με $id
    static function getFullPathFromFileID($id)
    {
        $conn = new MyDB();

        $conn->createConnection();

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

    // upload ενός image κι εισαγωγή στην βάση
    static function uploadAlbumImage($image, $mime)
    {
        $conn = new MyDB();

        $hash=SyncFiles::hashString($image); // Δημιουργούμε hash της εικόνας

        if(!$coverArtID=SyncFiles::searchForImageHash($hash)) {  // Ψάχνουμε αν το hash της εικόνας υπάρχει ήδη

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

            $checkAlbumCoversDir = FilesIO::createDirectory(ALBUM_COVERS_DIR . $imageDir); // Αν δεν υπάρχει ο φάκελος τον δημιουργούμε
            if(!$checkAlbumCoversDir['result']) {  // Αν είναι false τερματίζουμε την εκτέλεση
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
                    self::createSmallerImage($file, 'ico');
                }
            }

        }
        else $coverID=$coverArtID;


        return $coverID;


    }

    // Ελέγχει αν ένα image είναι valid
    static function checkValidImage($myImage)
    {

        $html = VALID_IMAGE_SCRIPT_ADDRESS.'?imagePath='.$myImage;
        $response = file_get_contents($html);
        $decoded = json_decode($response, true);

        if($decoded) {
            foreach ($decoded as $items) {
                $result = $items;
                return $result;
            }
        } else return false;
    }

    // Αναλόγως το extension επιστρέφει την εικόνα στο $image
    static function openImage($myImage) {
        $extension = pathinfo($myImage, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($myImage);
                break;
            case 'gif':
                $image = imagecreatefromgif($myImage);
                break;
            case 'png':
                $image = imagecreatefrompng($myImage);
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

    // Δημιουργεί μικρότερες εκδόσεις μίας εικόνας. Thumb, small, large.
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
            case 'ico':
                $newWidth = 32;
                $newHeight = 32;
                $newFilename = str_replace('.'.$extension, '.ico', $imageFilename);
                break;
        }

        // Δημιουργεί το image με νέες διαστάσεις
        $newImage = ImageCreateTrueColor($newWidth, $newHeight);
        imagecopyResampled ($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $oldImageWidth, $oldImageHeight);

        // Σώζει το image
        if($imageSize!=='ico') {
            if (imagejpeg($newImage, $imagePath . '/' . $newFilename)) {
                $result = true;
            } else {
                $result = false;
            }
        } else {
//            trigger_error($imagePath . '/' . $newFilename);
            if (imagepng($newImage, $imagePath . '/' . $newFilename)) {

                $result = true;
            } else {
                $result = false;
            }
        }

        imagedestroy($image); //  clean up image storage
        imagedestroy($newImage);

        return $result;

    }

    // Επιστρέφει το fullpath του album cover για το $id
    static function getAlbumImagePath($id, $imageSize)
    {
        $conn = new MyDB();

        $conn->createConnection();

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

                if(FilesIO::fileExists($icoImage)) {
                    $icoExist = true;
                } else {
                    $icoExist = false;
                }

                switch ($imageSize) {
                    case 'small': if($smallExist) {
                        $result = $smallImage;
                    } break;
                    case 'thumb': if($thumbExist) {
                        $result = $thumbImage;
                    } break;
                    case 'ico': if($icoExist) {
                        $result = $icoImage;
                    } break;
                }

                if($imageSize=='big' && $_SESSION['mobile'] && $smallExist) {
                    $result = $smallImage;
                }
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

    // Εκτελεί την linux εντολή για μετατροπή ενός ALAC σε mp3
    static function execConvertALAC ($source, $target, $bitrate)
    {
        // Μετατροπή ALAC σε απλό mp3. Το δημιουργεί καταρχήν σε temp dir (INTERNAL_CONVERT_PATH)
        print shell_exec('ffmpeg -i "'.$source.'" -ac 2 -f wav - | lame -b '.$bitrate.' - "'.$target.'" ');
    }

    // Επιστρέφει το λινκ με το artwork cover από το itunes API
    static function getItunesCover($search)
    {

        $html = 'https://itunes.apple.com/search?term='.urlencode($search);
//        trigger_error($html);
        $response = file_get_contents($html);
        $decoded = json_decode($response, true);

        if($decoded) {
            // Αν είναι mobile παίρνει την μικρή έκδοση.
            if(!$_SESSION['mobile']) {
                $coverSize = '1400x1400';
            } else {
                $coverSize = '250x250';
            }

            foreach ($decoded['results'] as $items) {
                $artwork = $items['artworkUrl100'];
                $artwork = str_replace('100x100', $coverSize, $artwork);
                return $artwork;
            }
        } else return false;
    }

    // Επιστρέφει το λινκ με το gif από το giphy API
    static function getGiphy($search)
    {

        $html = 'https://api.giphy.com/v1/gifs/search?q='.urlencode($search).'&api_key='.GIPHY_API;
//        trigger_error($html);
        $response = file_get_contents($html);
        $decoded = json_decode($response, true);

        if($decoded) {
            foreach ($decoded['data'] as $items) {
                $giphy = $items['images']['downsized_large']['url'];
                return $giphy;
            }
        } else return false;
    }

    // Δημιουργεί έναν νέο πίνακα για temporary playlist με το όνομα $table
    static function createPlaylistTempTable($table)
    {
        $conn = new MyDB();
        $conn->CreateConnection();

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

    // Ελέγχει αν υπάρχει ένα $tempPlaylist και αν δεν υπάρχει το δημιουργεί και κάνει σχετική εγγραφή στο playlist_tables
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

    // Επιστρέφει μία τυχαία εγγραφή από τον $table
    static function getRandomPlaylistID($table, $tableCount)
    {
        $conn = new MyDB();
        $conn->CreateConnection();

        $sql='SELECT * FROM '.$table.' LIMIT '.$tableCount.',1';

        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute();

        if($item=$stmt->fetch(\PDO::FETCH_ASSOC))
        {
            $result=array('playlist_id' => $item['id'], 'file_id' => $item['file_id']);
        }
        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Εισάγει ένα $fileID στο $tempPlaylist
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

    // Προσθέτει μία ψήφο στο table votes
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

    // Επιστρέφει το σύνολο ψήφων για κάθε file_id
    static function getVotes()
    {
        $conn = new MyDB();
        $conn->CreateConnection();

        $sql='SELECT file_id, count(*) as numberOfVotes FROM votes GROUP BY file_id';

        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute();

        $result=$stmt->fetchAll();

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Επιστρέφει σε πίνακα (song_name, artist) τα στοιχεία του τρέχοντος τραγουδιού
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

    // Στέλνει τα στοιχεία του τραγουδιού στον icecast server
    static function sendToIcecast ($songInfo)
    {

        $html = 'http://'.ICECAST_USER.':'.ICECAST_PASS.'@'.ICECAST_SERVER.'/admin/metadata?mount=/'.
            ICECAST_MOUNT.'&mode=updinfo&song='.urlencode($songInfo);

        $response = file_get_contents($html);
        $decoded = json_decode($response, true);


        if($decoded) {
            return true;
        } else return false;

    }

}