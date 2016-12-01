<?php

/**
 * File: OWMP.php
 * Created by rocean
 * Date: 19/06/16
 * Time: 23:18
 * Βασική class του OWMP
 */

class OWMP
{

    static function showVideo () {

        
        $tags = new Page();
        $conn = new RoceanDB();
        $UserGroup=$conn->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης

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
            array('name' => 'track_time',
                'fieldtext' => __('tag_track_time'),
                'type' => 'text',
                'required' => 'no',
                'maxlength' => '10',
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

        <video id="myVideo" width="100%" autoplay preload="auto" onerror="failed(event)"></video>

        <div id="overlay_volume">
            <span id="overlay_volume_text">
                
            </span>
        </div>

        <!--        Fullscreen overlay elements-->
        <div id="overlay">
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


    static function showPlaylistWindow ($offset, $step) {

        $fields=RoceanDB::getTableFields('music_tags',array('id'));

        global $mediaKinds;

        $tags = new Page();
        $conn = new RoceanDB();
        $UserGroup=$conn->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης

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
                
                    <input type="button" class="myButton" name="cancelEdit" id="cancelEdit" value="<?php echo __('search_text_cancel'); ?>" onclick="cancelEdit();">

                    <input type="button" class="myButton" name="clearEdit" id="clearEdit" value="<?php echo __('search_text_clear'); ?>" onclick="clearEdit();">
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
                <select name="playlist" id="playlist" >
                    <option value="">
                        <?php echo __('choose_playlist'); ?>
                    </option>
                    <?php

                    $userID=$conn->getUserID($conn->getSession('username'));      // Επιστρέφει το id του user με username στο session
                    // H λίστα με τις manual playlists
                    $manualPlaylists = RoceanDB::getTableArray('manual_playlists', 'id, playlist_name', 'user_id=?', array($userID), null, null, null);

                    foreach ($manualPlaylists as $playlist) {
                        ?>
                        <option value="<?php echo $playlist['id']; ?>">
                            <?php echo  $playlist['playlist_name']; ?>
                        </option>

                        <?php
                    }
                    ?>
                </select>
            </div>

            <input type="button" id="playPlaylist" onclick="playPlaylist();" title="<?php echo __('play_file'); ?>">
            <input type="button" id="insertPlaylistClick" onclick="displayInsertPlaylistWindow();" title="<?php echo __('create_playlist'); ?>">
            <input type="button" id="deletePlaylistClick" onclick="alert('not ready yet');" title="<?php echo __('delete_playlist'); ?>">
        
            <div id="insertPlaylistWindow">
                <form id="insertPlaylist" name="insertPlaylist">
                    <input type="text" id="playlistName" name="playlistName">
                    <input type="button" class="myButton PlaylistButton" id="insertPlaylistButton" name="insertPlaylistButton" onclick="createPlaylist();"
                           value="<?php echo __('create_playlist'); ?>">
                    <input type="button" class="myButton" name="cancelPlaylist" id="cancelPlaylist" value="<?php echo __('search_text_cancel'); ?>" onclick="cancelCreatePlaylist();">
                </form>
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
                            <input type="button" class="myButton" name="searching" id="searching" value="<?php echo __('search_text_search'); ?>" onclick="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>, true);">

                            <input type="button" class="myButton" name="duplicates" id="duplicates" value="<?php echo __('search_text_duplicates'); ?>" onclick="findDuplicates(0,<?php echo PLAYLIST_LIMIT; ?>, true);">

                            <input type="button" class="myButton" name="clearSearch" id="clearSearch" value="<?php echo __('search_text_clear'); ?>" onclick="clearSearch();">

                            <input type="button" class="myButton" name="cancelSearch" id="cancelSearch" value="<?php echo __('search_text_cancel'); ?>" onclick="cancelTheSearch();" >
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

            // Εμφάνιση των edit buttons
            $UserGroupID=$conn->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης

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
                    self::getPlaylist(null,$offset,$step,null,null);
                }
                else {
                    ?>

                        <div id="playlist_content"></div>

                    <?php
                }

            ?>

            <div id="error_container">
                <div id="alert_error"></div>
            </div>

        </div>

        <?php



    }

    // Εμφανίζει την playlist με βάση διάφορα keys αναζήτησης
    static function getPlaylist($fieldsArray=null, $offset, $step, $duplicates=null, $mediaKind=null, $tabID=null, $loadPlaylist=null) {
        $conn = new RoceanDB();

        $condition='';
        $arrayParams=array();

        if($fieldsArray) {
            foreach ($fieldsArray as $field) {

                if ($field['search_text'] === '0')  // Βάζει ένα κενό όταν μηδέν, αλλιώς το νομίζει null
                    $searchText = ' ' . $field['search_text'];
                else
                    $searchText = $field['search_text'];


                if ((!$field == null) && (!$searchText == null)) {  // αν ο πίνακας δεν είναι κενός και αν το search text δεν είναι κενό

                    $fieldType = RoceanDB::getTableFieldType('music_tags', $field['search_field']);  // παίρνει το type του field
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
            $condition = page::cutLastString($condition, 'OR ');
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

        if($duplicates==null) {   // κανονική λίστα
            if ($_SESSION['PlaylistCounter'] == 0) {
//                $playlistToPlay = RoceanDB::getTableArray('music_tags', 'music_tags.id', $condition, $arrayParams, 'date_added DESC', 'files', $joinFieldsArray); // Ολόκληρη η λίστα

                if(!$tabID)  // Αν δεν έρχεται από function
                    $tabID=TAB_ID;  // Την πρώτη φορά που τρέχει η εφαρμογή το παίρνει από το TAB_ID

                // Το όνομα του temporary user playlist table για τον συγκεκριμένο χρήστη
                $tempUserPlaylist=CUR_PLAYLIST_STRING . $conn->getSession('username') . '_' . $tabID;

                // Αν είναι true το $loadPlaylist τότε δεν χρειάζεται να δημιουργηθεί temporary table. Υπάρχει ήδη
                // από την manual playlist
                if(!$loadPlaylist) {
                    $myQuery = RoceanDB::createQuery('music_tags', 'music_tags.id', $condition, 'date_added DESC', 'files', $joinFieldsArray);

                    // Αν δεν υπάρχει ήδη το σχετικό table το δημιουργούμε
                    if (!RoceanDB::checkIfTableExist($tempUserPlaylist)) {
                        self::createPlaylistTempTable($tempUserPlaylist); // Δημιουργούμε το table

                        // κάνουμε την σχετική εγγραφή τον πίνακα playlist_tables
                        $sql = 'INSERT INTO playlist_tables (table_name, last_alive) VALUES(?,?)';
                        $playlistTableArray = array($tempUserPlaylist, date('Y-m-d H:i:s'));
                        $conn->ExecuteSQL($sql, $playlistTableArray);
                    }

                    // αντιγραφή του playlist σε αντίστοιχο $tempUserPlaylist table ώστε ο player να παίζει από εκεί
                    RoceanDB::copyFieldsToOtherTable('file_id', $tempUserPlaylist, $myQuery, $arrayParams);
                }

                $tableCount = RoceanDB::countTable($tempUserPlaylist);

                $_SESSION['$countThePlaylist'] = $tableCount;
            }

            // Η λίστα προς εμφάνιση
            if(!$loadPlaylist) {  // Αν το $loadPlaylist είναι false
                $playlist = RoceanDB::getTableArray('music_tags', null, $condition, $arrayParams,
                    'date_added DESC LIMIT ' . $offset . ',' . $step, 'files', $joinFieldsArray);
            }
            else { // αλλιώς κάνει join με τον $tempUserPlaylist
                $joinFieldsArray = array('firstField' => 'id', 'secondField' => 'file_id');
                $mainTables = array('music_tags', 'files');

                $playlist = RoceanDB::getTableArray($mainTables, 'music_tags.*, files.path, files.filename, files.hash, files.kind',
                    null, null, null, $tempUserPlaylist, $joinFieldsArray);
            }


            // TODO για τις manual playlists
            // να εμφανίζει progress gif
            // να πατάς κάτι και να ξαναγυρνάει στην κανονική playlist

        }
        else {  // εμφάνιση διπλών εγγραφών
            if ($_SESSION['PlaylistCounter'] == 0) {
                // TODO να κάνω και το duplicates με την αντιγραφή πινάκων
                $playlistToPlay = OWMP::getFilesDuplicates(null,null); // Ολόκληρη η λίστα
                $_SESSION['$countThePlaylist'] = count($playlistToPlay);
            }

            $playlist = OWMP::getFilesDuplicates($offset,$step);
        }
        

        $counter=0;
        $UserGroupID=$conn->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης

        if($playlist) {
            ?>

            <div id="playlist_content">

                <?php

                if ($duplicates == null) {
                    ?>
                    <div id="browseButtons">
                        <input id="previous" class="myButton" type="button" value="<?php echo __('search_previous'); ?>"
                               onclick="searchPlaylist(<?php if ($offset > 0) echo $offset - $step; ?>,<?php echo $step; ?>);">
                        <input id="next" class="myButton" type="button" value="<?php echo __('search_next'); ?>"
                               onclick="searchPlaylist(<?php if (($offset + $step) < $_SESSION['$countThePlaylist']) echo $offset + $step; ?>,<?php echo $step; ?>);">
                    </div>
                    <?php
                } else {
                    ?>
                    <div id="browseButtons">
                        <input id="previous" class="myButton" type="button" value="<?php echo __('search_previous'); ?>"
                               onclick="findDuplicates(<?php if ($offset > 0) echo $offset - $step; ?>,<?php echo $step; ?>);">
                        <input id="next" class="myButton" type="button" value="<?php echo __('search_next'); ?>"
                               onclick="findDuplicates(<?php if (($offset + $step) < $_SESSION['$countThePlaylist']) echo $offset + $step; ?>,<?php echo $step; ?>);">
                    </div>
                    <?php
                }

                ?>

                <div id="playlistTable">


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


                    foreach ($playlist as $track) {
                        ?>
                        <div id="fileID<?php echo $track['id']; ?>" class="track"
                             onmouseover="displayCoverImage('fileID<?php echo $track['id']; ?>');"
                             onmouseout="hideCoverImage();">


                            <div
                                class="tag kind <?php if ($track['kind'] == 'Music') echo 'kind_music'; else echo 'kind_music_video'; ?>"
                                title="<?php if ($track['kind'] == 'Music') echo 'Music'; else echo 'Music Video'; ?>"></div>


                            <div class="tag delete_file">

                                <?php

                                $coverImagePath = self::getAlbumImagePath($track['album_artwork_id']);

                                if ($track['kind'] == 'Music' && $coverImagePath) {

                                    ?>
                                    <img class="coverImage" src="<?php echo ALBUM_COVERS_DIR . $coverImagePath; ?>">
                                    <?php
                                }
                                ?>

                                <input type="checkbox" id="check_item[]" name="check_item[]"
                                       value="<?php echo $track['id']; ?>">

                                <input type="button" class="play_button playlist_button_img"
                                       title="<?php echo __('play_file'); ?>"
                                       onclick="loadNextVideo(<?php echo $track['id']; ?>);">

                                <input type="button" class="playlist_add_button playlist_button_img"
                                       title="<?php echo __('add_to_playlist'); ?>"
                                       onclick="addToPlaylist(<?php echo $track['id']; ?>);">

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
                                <?php echo $track['song_name']; ?>
                            </div>
                            <div class="tag artist" title="<?php echo $track['artist']; ?>">
                                <?php echo $track['artist']; ?>
                            </div>
                            <div class="tag album" title="<?php echo $track['album']; ?>">
                                <?php echo $track['album']; ?>
                            </div>
                            <div class="tag genre" title="<?php echo $track['genre']; ?>">
                                <?php echo $track['genre']; ?>
                            </div>
                            <div class="tag song_year"
                                 title="<?php if ($track['song_year'] == '0') echo ''; else echo $track['song_year']; ?>">
                                <?php if ($track['song_year'] == '0') echo ''; else echo $track['song_year']; ?>
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
                        $counter++;
                    }


                    $offset = intval($offset);
                    $step = intval($step);
                    ?>


                </div>

                <?php
                if ($duplicates == null) {
                    ?>
                    <div id="browseButtons">
                        <input id="previous" class="myButton" type="button" value="<?php echo __('search_previous'); ?>"
                               onclick="searchPlaylist(<?php if ($offset > 0) echo $offset - $step; ?>,<?php echo $step; ?>);">
                        <input id="next" class="myButton" type="button" value="<?php echo __('search_next'); ?>"
                               onclick="searchPlaylist(<?php if (($offset + $step) < $_SESSION['$countThePlaylist']) echo $offset + $step; ?>,<?php echo $step; ?>);">
                    </div>
                    <?php
                } else {
                    ?>
                    <div id="browseButtons">
                        <input id="previous" class="myButton" type="button" value="<?php echo __('search_previous'); ?>"
                               onclick="findDuplicates(<?php if ($offset > 0) echo $offset - $step; ?>,<?php echo $step; ?>);">
                        <input id="next" class="myButton" type="button" value="<?php echo __('search_next'); ?>"
                               onclick="findDuplicates(<?php if (($offset + $step) < $_SESSION['$countThePlaylist']) echo $offset + $step; ?>,<?php echo $step; ?>);">
                    </div>
                    <?php
                }

                ?>

            </div>

            <?php
        }




            if ($_SESSION['PlaylistCounter'] == 0) {
                ?>

                <script type="text/javascript">

//                    var files = <?php //echo json_encode($playlistToPlay); ?>//;


                    init();

                </script>

                <?php
            }

            $_SESSION['PlaylistCounter']++;

    }

    // Εμφάνιση των εγγραφών των options σε μορφή form fields για editing
    static function getOptionsInFormFields () {
        $conn = new RoceanDB();

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
    static function getUsersInFormFields () {
        $conn = new RoceanDB();
        $conn->CreateConnection();

        $UserGroupID=$conn->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης
        $userID=$conn->getUserID($conn->getSession('username'));      // Επιστρέφει το id του user με username στο session

        global $UserGroups;

        if($UserGroupID==1)
            $sql = 'SELECT * FROM user JOIN user_details on user.user_id=user_details.user_id';
        else $sql = 'SELECT * FROM user JOIN user_details on user.user_id=user_details.user_id WHERE user.user_id=?';

        $stmt = RoceanDB::$conn->prepare($sql);

        $counter=1;

        if($UserGroupID==1)
            $stmt->execute();
        else $stmt->execute(array($userID));

        ?>
        <div class="ListTable UsersList">




            <?php


            while($item=$stmt->fetch(PDO::FETCH_ASSOC))
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
    
    static function getPathsInFormFields() {
        $conn = new RoceanDB();
        global $mediaKinds;


        $paths=$conn->getTableArray('paths', null, null, null, null, null, null);  // Παίρνει τα δεδομένα του πίνακα paths σε array

        if(empty($paths)) {  // Αν δεν επιστρέψει κανένα αποτέλεσμα, σετάρουμε εμείς μια πρώτη γραμμή στο array
            $paths[]=array('id'=>'0', 'file_path'=>'', 'kind'=>'', 'main'=>'');
        }

        $counter=1;


        ?>
        <div class="ListTable">

            <?php

            // TODO όταν επιλέγεις main να κάνει main μόνο το συγκεκριμένο του mediakind και not main τα υπόλοιπα
            foreach($paths as $path)
            {
                ?>
                <div class="PathsRow" id="PathID<?php echo $path['id']; ?>">
                    <form class="table_form paths_form" id="paths_formID<?php echo $path['id']; ?>">

                        <span class="ListColumn"><input class="input_field"
                                                        placeholder="<?php echo __('paths_file_path'); ?>"
                                                        maxlength="255" required type="text" name="file_path" value="<?php echo $path['file_path']; ?>"></span>
                        <span class="ListColumn">
                            <select class="input_field" name="kind" >
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

                        <span class="ListColumn">
                            <select class="input_field" name="main" >
                                <option value="0" <?php if($path['main']==0) echo 'selected=selected'; ?> >
                                    not main
                                </option>
                                <option value="1" <?php if($path['main']==1) echo 'selected=selected'; ?> >
                                    main
                                </option>
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


    static function showConfiguration () {

        $conn = new RoceanDB();
        $UserGroup=$conn->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης

        ?>
        <h2><?php echo __('nav_item_2'); ?></h2>


        <?php

        if($UserGroup==1) {  // Αν ο χρήστης είναι admin
            ?>
            <details>
                <summary><?php echo __('settings_options'); ?></summary>
                <?php self::getOptionsInFormFields() ?>
            </details>



            <?php
        }
        ?>

        <details>
            <summary><?php echo __('settings_users'); ?></summary>
            <?php self::getUsersInFormFields() ?>
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


    // εμφάνιση των επιλογών συγχρονισμού
    static function showSynchronization () {

        ?>
            <details>
                <summary><?php echo __('settings_paths'); ?></summary>
                <?php self::getPathsInFormFields(); ?>
            </details>

        <?php

        $conn = new RoceanDB();
        $conn->CreateConnection();

        global $mediaKinds;

        $UserGroupID=$conn->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης

        if($UserGroupID==1) {
            ?>
            <div id="syncButtons">

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

                <input type="button" class="myButton syncButton" id="startSync" name="startSync" onclick="startSync('sync');"
                       value="<?php echo __('Synchronize'); ?>">
    
                <input type="button" class="myButton syncButton" id="startClear" name="startClear" onclick="startSync('clear');"
                       value="<?php echo __('sync_clear'); ?>">

                <input type="button" class="myButton syncButton" id="startHash" name="startHash" onclick="startSync('hash');"
                       value="<?php echo __('sync_hash'); ?>">

                <input type="button" class="myButton syncButton" id="startFileMetadata" name="startFileMetadata" onclick="startSync('metadata');"
                       value="<?php echo __('sync_metadata'); ?>">

                <input type="button" class="myButton syncButton" id="startJsonImport" name="startJsonImport" onclick="startSync('json_import');"
                       value="<?php echo __('sync_json'); ?>">


                <?php
                if(FILE_UPLOAD) { // Έλεγχος αν έχει οριστεί FILE_UPLOAD αλλιώς να μην ενεργοποιεί το κουμπί του youtube

                    if(self::createDirectory(FILE_UPLOAD)) {

                        ?>
                        <p>
                            <textarea id="youTubeUrl" name="youTubeUrl"></textarea>
                            <input type="button" class="myButton syncButton" id="downloadYouTube" name="downloadYouTube"
                                   onclick="downloadYouTube();"
                                   value="<?php echo __('sync_youtube'); ?>" >
                        </p>

                        <?php
                    }

                }
                else echo '<p>'.__('youtube_error').'</p>';
                ?>


                   
            </div>

            
            
            <div id="SyncDetails">
                <div id="progress"></div>
            </div>
            

            <script type="text/javascript">

                checkProcessAlive();

            </script>


            <?php
        }
        else echo '<p>'.__('only_for_admin').'</p>';
    }

    // Εμφάνιση των logs
    static function showLogs ()
    {
        ?>
        <h2><?php echo __('nav_item_4'); ?></h2>
        <?php

        $conn = new RoceanDB();
        $conn->CreateConnection();

        $sql = 'SELECT * FROM logs ORDER BY log_date DESC LIMIT 0,100';

        $stmt = RoceanDB::$conn->prepare($sql);

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
        while ($item = $stmt->fetch(PDO::FETCH_ASSOC)) {
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

    // Επιστρέφει τις διπλές εγγραφές με βάση το hash
    static function getFilesDuplicates ($offset, $step) {

        $conn = new RoceanDB();

        $conn->createConnection();

        $sql='SELECT files.id as id, song_name, artist, genre, date_added, play_count, rating, song_year FROM files JOIN music_tags on files.id=music_tags.id WHERE hash IN (SELECT hash FROM OWMP.files GROUP BY hash HAVING count(*) > 1) ORDER BY hash';

        if(isset($offset))
            $sql=$sql.' LIMIT '.$offset.','.$step;
        
        $stmt = RoceanDB::$conn->prepare($sql);

        $stmt->execute();

        $result=$stmt->fetchAll();

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Δημιουργεί ένα κατάλληλο array ώστε να αντιγραφεί σε προσωρινό table
    static function makePlaylistArrayToCopy($arrayToCopy) {
        $counter=0;
        foreach($arrayToCopy as $item) {
            $newArray[]=array('id'=>$counter, 'file_id'=>$item['id']);
            $counter++;
        }

        return $newArray;
    }


    
    
    // Σβήνει μόνο το αρχείο στον δίσκο
    static function deleteOnlyFile($fullPath) {
        if (file_exists($fullPath)) {  // αν υπάρχει το αρχείο, σβήνει το αρχείο 
            if (unlink($fullPath)) 
                $result = true;
            else $result = false;
        } else $result = false;
        
        return $result;
    }
    
    // Σβήνει ένα αρχείο και την αντίστοιχη εγγραφή στην βάση
    static function deleteFile($id) {
        $conn = new RoceanDB();
        
        $file=RoceanDB::getTableArray('files','*', 'id=?', array($id),null, null, null);   // Παίρνει το συγκεκριμένο αρχείο

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
    static function getFullPathFromFileID($id) {
        $conn = new RoceanDB();

        $conn->createConnection();

        $sql='SELECT path, filename FROM files WHERE id=?';

        $stmt = RoceanDB::$conn->prepare($sql);

        $stmt->execute(array($id));

        if($item=$stmt->fetch(PDO::FETCH_ASSOC))

            $result=$item['path'].urldecode($item['filename']);

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // πραγματικός έλεγχος αν ένα αρχείο υπάρχει, γιατί παίζει μερικές φορές λόγω cashe να επιστρέφει λάθος αποτέλεσμα η file_exists
    static function fileExists($path){
        return (@fopen($path,"r")==true);
    }


    // TODO να δημιουργεί και thumbnail
    // upload ενός image κι εισαγωγή στην βάση
    static function uploadAlbumImage($image, $mime) {
        $conn = new RoceanDB();

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

            self::createDirectory(ALBUM_COVERS_DIR . $imageDir); // Αν δεν υπάρχει ο φάκελος τον δημιουργούμε

            $file = ALBUM_COVERS_DIR . $imageDir . $timestampFilename . $imageExtension;  // Το πλήρες path που θα γραφτεί το αρχείο

            $success = file_put_contents($file, $image);  // Κάνει την τελική εγγραφή του image σε αρχείο

            if ($success) {  // Αν το αρχείο δημιουργηθεί κανονικά κάνουμε εγγραφή στην βάση

                $sql = 'INSERT INTO album_arts (path, filename, hash) VALUES(?,?,?)';   // Εισάγει στον πίνακα album_arts

                $artsArray = array($imageDir, $timestampFilename.$imageExtension, $hash);

                $coverID=$conn->ExecuteSQL($sql, $artsArray); // Παίρνουμε το id της εγγραφής που έγινε
            }

        }
        else $coverID=$coverArtID;


        return $coverID;


    }


    // Επιστρέφει το fullpath του album cover για το $id
    static function getAlbumImagePath($id) {
        $conn = new RoceanDB();

        $conn->createConnection();

        $sql='SELECT path, filename FROM album_arts WHERE id=?';

        $stmt = RoceanDB::$conn->prepare($sql);

        $stmt->execute(array($id));

        if($item=$stmt->fetch(PDO::FETCH_ASSOC))

            $result=$item['path'] . $item['filename'];

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    // Ελέγχει την ύπαρξη ενός directory και αν μπορεί το δημιουργεί, όταν δεν υπάρχει
    static function createDirectory($dir) {
        if (!is_dir($dir)) { // Αν δεν υπάρχει ο φάκελος τον δημιουργούμε
            if (mkdir($dir, 0777, true)) {
                if (!is_writable($dir))
                    exit('<p>'.__('cant_write_to_path'). ' '.$dir . '. '.__('give_permissions').'</p>');
            }
            else exit('<p>'.__('cant_create_path').' ' . $dir.'. '.__('create_the_path').'</p>');
        } else if(!is_writable($dir))
            exit('<p>'.__('cant_write_to_path').' ' . $dir . '. '.__('give_permissions').'</p>');
        
        return true;
    }


    // Εκτελεί την linux εντολή για μετατροπή ενός ALAC σε mp3
    static function execConvertALAC ($source, $target, $bitrate) {
        // Μετατροπή ALAC σε απλό mp3. Το δημιουργεί καταρχήν σε temp dir (INTERNAL_CONVERT_PATH)
        print shell_exec('ffmpeg -i "'.$source.'" -ac 2 -f wav - | lame -b '.$bitrate.' - "'.$target.'" ');
    }


    // Επιστρέφει το id ενός youtube video από το url του
    // Source from http://code.runnable.com/VUpjz28i-V4jETgo/get-youtube-video-id-from-url-for-php
    static function getYoutubeID($url){
        $video_id = false;
        $url = parse_url($url);
        if (strcasecmp($url['host'], 'youtu.be') === 0)
        {
            #### (dontcare)://youtu.be/<video id>
            $video_id = substr($url['path'], 1);
        }
        elseif (strcasecmp($url['host'], 'www.youtube.com') === 0)
        {
            if (isset($url['query']))
            {
                parse_str($url['query'], $url['query']);
                if (isset($url['query']['v']))
                {
                    #### (dontcare)://www.youtube.com/(dontcare)?v=<video id>
                    $video_id = $url['query']['v'];
                }
            }
            if ($video_id == false)
            {
                $url['path'] = explode('/', substr($url['path'], 1));
                if (in_array($url['path'][0], array('e', 'embed', 'v')))
                {
                    #### (dontcare)://www.youtube.com/(whitelist)/<video id>
                    $video_id = $url['path'][1];
                }
            }
        }
        return $video_id;
    }
    
    // Επιστρέφει τον τίτλο του βίντεο μέσω του Youtube API
    // Details @ https://developers.google.com/youtube/v3/getting-started
    static function getYoutubeTitle($url){
        $youtubeID=self::getYoutubeID($url);

        trigger_error($youtubeID);
            
        $html = 'https://www.googleapis.com/youtube/v3/videos?id='.$youtubeID.'&key='.YOUTUBE_API.'&part=snippet';
        $response = file_get_contents($html);
        $decoded = json_decode($response, true);
        foreach ($decoded['items'] as $items) {
            $title= $items['snippet']['title'];
            return $title;
        }
    }



    // Μετατροπή Ελληνικών και Κυριλικών χαρακτήρων σε λατινικούς
    static function GrCyr2Latin($string) {
        $cyr = array(
            'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
            'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
            'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
            'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'
    );
        $lat = array(
            'a','b','v','g','d','e','io','zh','z','i','y','k','l','m','n','o','p',
            'r','s','t','u','f','h','ts','ch','sh','sht','a','i','y','e','yu','ya',
            'A','B','V','G','D','E','Io','Zh','Z','I','Y','K','L','M','N','O','P',
            'R','S','T','U','F','H','Ts','Ch','Sh','Sht','A','I','Y','e','Yu','Ya'
    );
        $greek   = array('α','ά','Ά','Α','β','Β','γ', 'Γ', 'δ','Δ','ε','έ','Ε','Έ','ζ','Ζ','η','ή','Η','θ','Θ','ι','ί','ϊ','ΐ','Ι','Ί', 'κ','Κ','λ','Λ','μ','Μ','ν','Ν','ξ','Ξ','ο','ό','Ο','Ό','π','Π','ρ','Ρ','σ','ς', 'Σ','τ','Τ','υ','ύ','Υ','Ύ','φ','Φ','χ','Χ','ψ','Ψ','ω','ώ','Ω','Ώ',"'","'",',',':','+');
        $english = array('a', 'a','A','A','b','B','g','G','d','D','e','e','E','E','z','Z','i','i','I','th','Th', 'i','i','i','i','I','I','k','K','l','L','m','M','n','N','x','X','o','o','O','O','p','P' ,'r','R','s','s','S','t','T','u','u','Y','Y','f','F','ch','Ch','ps','Ps','o','o','O','O','','','_','-','-');
        $string  = str_replace($greek, $english, $string);
        $string  = str_replace($cyr, $lat, $string);
        return $string;
    }


    // Κατεβάζει ένα βίντεο από το Youtube
    static function downloadYoutube($url) {
        Page::setLastMomentAlive(false);
        
        $myYear = date('Y');
        $myMonth = date('m');
        $fileDir = $myYear . '/' . $myMonth . '/';  // O φάκελος που θα γραφτεί το αρχείο

        $uploadDir=FILE_UPLOAD . $fileDir;
        self::createDirectory($uploadDir); // Αν δεν υπάρχει ο φάκελος τον δημιουργούμε

        // Παίρνει τον τίτλο του βίντεο και τον μετατρέπει σε greeklish αν χρειάζεται
        $title=self::getYoutubeTitle($url);

//        trigger_error($title);

        $title=str_replace("/",'',$title);
        $title=self::GrCyr2Latin(ClearString($title));

//        trigger_error($title);

        // το όνομα του αρχείου που θα κατεβάσει με το full path
        $outputfilename = shell_exec('youtube-dl --get-filename -f "bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best" -o "'.$uploadDir.$title.'.%(ext)s" '.$url);
        // κατεβάζει το βίντεο
        $result=shell_exec('youtube-dl -f "bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best" -o "'.$uploadDir.$title.'.%(ext)s" '.$url);

//        trigger_error($result);

        $outputfilename=str_replace("\n",'',$outputfilename);

        Page::setLastMomentAlive(true);  // To timestamp της συγκεκριμένης στιγμής
        
        // έλεγχος αν έχει κατέβει το βίντεο
        if(OWMP::fileExists($outputfilename))
            return $outputfilename;
        else return false;
    }


    // Επιστρέφει το λινκ με το artwork cover από το itunes API
    static function getItunesCover($search){

        $html = 'https://itunes.apple.com/search?term='.urlencode($search);
//        trigger_error($html);
        $response = file_get_contents($html);
        $decoded = json_decode($response, true);

        if($decoded) {
            foreach ($decoded['results'] as $items) {
                $artwork = $items['artworkUrl100'];
                $artwork = str_replace('100x100', '1400x1400', $artwork);
                return $artwork;
            }
        } else return false;
    }

    // Επιστρέφει το λινκ με το gif από το giphy API
    static function getGiphy($search){

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

    
    // Εισάγει τις αρχικές τιμές στον πίνακα Options και στον progress
    static function startBasicOptions()
    {

        $conn = new RoceanDB();

//        if(!$conn->getOption('interval_value'))
//            $conn->createOption('interval_value', '5', 1, 0);
        
//        if(!$conn->getOption('mail_host'))
//            $conn->createOption('mail_host', 'smtp.gmail.com', 1, 0);
//
//        if(!$conn->getOption('mail_username'))
//            $conn->createOption('mail_username', 'username', 1, 0);
//
//        if(!$conn->getOption('mail_password')) {
//            $conn->createOption('mail_password', '12345678',1,1);
//
//        if(!$conn->getOption('mail_from'))
//            $conn->createOption('mail_from', 'username@mail.com', 1, 0);
//
//        if(!$conn->getOption('mail_from_name'))
//            $conn->createOption('mail_from_name', 'name', 1, 0);
        
        // Οι αρχικές τιμές στον πίνακα options
        if(!$conn->getOption('convert_alac_files'))
            $conn->createOption('convert_alac_files', 'false', 1, 0);
        
        if(!$conn->getOption('playlist_limit'))
            $conn->createOption('playlist_limit', '150', 1, 0);
        
        if(!$conn->getOption('dir_prefix'))
            $conn->createOption('dir_prefix', '/', 1, 0);
        
        if(!$conn->getOption('syncItunes'))
            $conn->createOption('syncItunes', 'false', 1, 0);

        if(!$conn->getOption('web_folder_path'))
            $conn->createOption('web_folder_path', 'var/www/html/', 1, 0);

        if(!$conn->getOption('date_format'))
            $conn->createOption('date_format', 'Y-m-d', 1, 0);

        
        // Οι αρχικές τιμές στον πίνακα progress
        if(!Page::checkIfProgressNameExists('progressInPercent'))
            Page::createProgressName('progressInPercent');

        if(!Page::checkIfProgressNameExists('progressMessage'))
            Page::createProgressName('progressMessage');

        if(!Page::checkIfProgressNameExists('killCommand')) {
            Page::createProgressName('killCommand');
            Page::setKillCommand('0');
        }

        if(!Page::checkIfProgressNameExists('lastMomentAlive')) {
            Page::createProgressName('lastMomentAlive');
            Page::setLastMomentAlive(true);
        }


        
        
    }
    
    
    // Δημιουργεί έναν νέο πίνακα για temporary playlist με το όνομα $table
    static function createPlaylistTempTable($table) {
        $conn = new RoceanDB();
        $conn->CreateConnection();

        $sql = 'CREATE TABLE '.$table.' (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `file_id` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;';
        
        $stmt = RoceanDB::$conn->prepare($sql);


        if($stmt->execute()) 

            $result = true;

        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }


    static function getRandomPlaylistID($table, $tableCount) {
        $conn = new RoceanDB();
        $conn->CreateConnection();

        $sql='SELECT * FROM '.$table.' LIMIT '.$tableCount.',1';

        $stmt = RoceanDB::$conn->prepare($sql);

        $stmt->execute();

        if($item=$stmt->fetch(PDO::FETCH_ASSOC))
        {
            $result=array('playlist_id' => $item['id'], 'file_id' => $item['file_id']);
        }
        else $result=false;

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }
    
    
    
    
    
}
