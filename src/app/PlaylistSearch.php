<?php
/**
 *
 * File: PlaylistSearch.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 06/06/2017
 * Time: 02:15
 *
 */

namespace apps4net\parrot\app;

use apps4net\framework\MyDB;
use apps4net\framework\User;
use apps4net\framework\Utilities;


class PlaylistSearch extends OWMPElements
{
    // Attributes για την αναζήτηση
    public $fieldsArray=null;  // Το json array που περιέχει τα πεδία για το search query
    public $offset; // το offset για το limit στο sql query
    public $step; // το βήμα για το limit στο sql query
    public $duplicates=null;  // true αν θέλουμε να ψάξει για duplicates
    public $mediaKind=null;  // ορίζεται το media kind για το οποίο θα γίνει η αναζήτηση. null για όλα
    public $tabID;  // To temp ID για το τρέχον session του tab που τρέχει η εφαρμογή
    public $loadPlaylist=null;  // true αν πρόκειται για manual playlist
    public $votePlaylist;   // true αν θέλουμε να εμφανιστεί η λίστα για την σελίδα των votes
    public $condition = null; // το sql query για search
    public $arrayParams = array(); // οι παράμετροι που θα περάσουν στο sql query
    public $joinFieldsArray;   // Τα πεδία που θα γίνουν join
    public $tempUserPlaylist; // Το όνομα του temporary user playlist table για τον συγκεκριμένο χρήστη
    public $mainTables; // Οι πίνακες που θα γίνουν join
    public $playlist; // H playlist που θα εμφανιστεί

    // Εμφανίζει τα browse buttons
    public function getBrowseButtons()
    {
        $operation = '';

        // Έλεγχος για το τι είδους λίστα εμφανίζει
        if (!$this->duplicates && !$this->votePlaylist) {
            $operation = 'search';
        } else {
            if($this->duplicates) {
                $operation = 'duplicates';
            }

            if($this->votePlaylist) {
                $operation = 'votePlaylist';
            }
        }

        // Εμφάνιση των κουμπιών
        if($operation=='search') {
            ?>

            <div id="browseButtons">
                <input id="previous" class="myButton" type="button" value="<?php echo __('search_previous'); ?>"
                       onclick="searchPlaylist(<?php if ($this->offset > 0) echo $this->offset - $this->step; else echo '0'; ?>,<?php echo $this->step; ?>);">
                <input id="next" class="myButton" type="button" value="<?php echo __('search_next'); ?>"
                       onclick="searchPlaylist(<?php if (($this->offset + $this->step) < $_SESSION['$countThePlaylist']) echo $this->offset + $this->step; else echo $this->offset; ?>,<?php echo $this->step; ?>);">
            </div>

            <?php
        }

        if($operation=='duplicates') {
            ?>

            <div id="browseButtons">
                <input id="previous" class="myButton" type="button" value="<?php echo __('search_previous'); ?>"
                       onclick="findDuplicates(<?php if ($this->offset > 0) echo $this->offset - $this->step; else echo '0'; ?>,<?php echo $this->step; ?>);">
                <input id="next" class="myButton" type="button" value="<?php echo __('search_next'); ?>"
                       onclick="findDuplicates(<?php if (($this->offset + $this->step) < $_SESSION['$countThePlaylist']) echo $this->offset + $this->step; else echo $this->offset; ?>,<?php echo $this->step; ?>);">
            </div>

            <?php
        }

        if($operation=='votePlaylist') {
            ?>

            <div id="browseButtons">
                <input id="previous" class="myButton" type="button" value="<?php echo __('search_previous'); ?>"
                       onclick="getVotePlaylist(<?php if ($this->offset > 0) echo $this->offset - $this->step; else echo '0'; ?>,<?php echo $this->step; ?>);">
                <input id="next" class="myButton" type="button" value="<?php echo __('search_next'); ?>"
                       onclick="getVotePlaylist(<?php if (($this->offset + $this->step) < $_SESSION['$countThePlaylist']) echo $this->offset + $this->step; else echo $this->offset; ?>,<?php echo $this->step; ?>);">
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
    public function displayFullPlaylist($track)
    {

        if(!$this->votePlaylist) {
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
                if (!$this->loadPlaylist) { ?>
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
                <?php echo htmlentities(json_encode(self::getSearchArray('song_name', $track['song_name']))); ?>);">
                    <?php echo $track['song_name']; ?>
                </span>
            </div>
            <div class="tag artist" title="<?php echo $track['artist']; ?>">
                <span class="searchableItem"  onclick="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>,true,
                <?php echo htmlentities(json_encode(self::getSearchArray('artist', $track['artist']))); ?>);">
                    <?php echo $track['artist']; ?>
                </span>
            </div>
            <div class="tag album" title="<?php echo $track['album']; ?>">
                <span class="searchableItem"  onclick="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>,true,
                <?php echo htmlentities(json_encode(self::getSearchArray('album', $track['album']))); ?>);">
                    <?php echo $track['album']; ?>
                </span>
            </div>
            <div class="tag genre" title="<?php echo $track['genre']; ?>">
                <span class="searchableItem"  onclick="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>,true,
                <?php echo htmlentities(json_encode(self::getSearchArray('genre', $track['genre']))); ?>);">
                    <?php echo $track['genre']; ?>
                </span>
            </div>
            <div class="tag song_year"
                 title="<?php if ($track['song_year'] == '0') echo ''; else echo $track['song_year']; ?>">
                <span class="searchableItem"  onclick="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>,true,
                <?php echo htmlentities(json_encode(self::getSearchArray('song_year', $track['song_year']))); ?>);">
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

    // Μετατρέπει το $field array σε sql query string
    //      @param array $field  Το array με τα πεδία που είναι να μπουν στο sql search query
    //      @return: string $this->condition = To search query
    //      @return: array $this->arrayParams = To array με τις παραμέτρους για το search
    public function getFieldString($field)
    {
        if ($field['search_text'] === '0')  // Βάζει ένα κενό όταν είναι μηδέν, αλλιώς το νομίζει null
            $searchText = ' ' . $field['search_text'];
        else
            $searchText = $field['search_text'];

        if ((!$field == null) && (!$searchText == null)) {  // αν ο πίνακας δεν είναι κενός και αν το search text δεν είναι κενό

            $fieldType = MyDB::getTableFieldType('music_tags', $field['search_field']);  // παίρνει το type του field

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

                // Τελικό sql query
                $this->condition = $this->condition . $field['search_field'] . $equality_sign . '? ' . $field['search_operator'] . ' ';
                $this->arrayParams[] = $searchText;
            } else {   // αν είναι string
                $searchText = ClearString($field['search_text']);
                $this->condition = $this->condition . $field['search_field'] . ' LIKE ? ' . $field['search_operator'] . ' ';
                $this->arrayParams[] = '%' . $searchText . '%';
            }


        }

    }

    // Θέτει τις τιμές του query σε sessions
    public function setQuerySessions()
    {
        if (!$this->condition=='') {
            $_SESSION['condition']=$this->condition;  // Το κρατάει σε session για μελοντική χρήση
            $_SESSION['arrayParams']=$this->arrayParams;
        }
        else {
            $_SESSION['condition']=null;  // Το κρατάει σε session για μελοντική χρήση
            $_SESSION['arrayParams']=null;
        }

        // Όταν τρέχει για πρώτη φορά η εφαρμογή
        if(!isset($_SESSION['PlaylistCounter'])){
            $_SESSION['PlaylistCounter']=0;
            $_SESSION['condition']=null;
            $_SESSION['arrayParams']=null;
        }

    }

    // Αν υπάρχει προηγούμενο query παίρνει τις τιμές από αυτό
    public function getQueryFromSessions()
    {
        if( isset($_SESSION['condition']) && isset($_SESSION['arrayParams']) ) {
            $this->condition = $_SESSION['condition'];
            $this->arrayParams = $_SESSION['arrayParams'];

            return true;
        } else {
            return false;
        }

    }

    // Προσθέτει στο query το join με τα files  με βάση το $this->mediaKind
    public function insertMediaKindJoin()
    {
        // Επιλογές για join ώστε να πάρουμε το media kind από το files
        if(isset($this->mediaKind)) {
            if (!$this->condition=='')
                $this->condition = '(' . $this->condition . ')' . ' AND files.kind=? ';
            else $this->condition.=  ' files.kind=? ';

            $this->arrayParams[]=$this->mediaKind; // προσθέτει και την παράμετρο του $mediakind στις παραμέτρους του query
        }

    }

    // Διαβάζει το json array $fieldsArray και επιστρέφει το search query μαζί με τους παραμέτρους
    //      @param: array $this->fieldsArray = Το json array που θα διαβάσει
    //      @return: string $this->condition = To search query
    //      @return: array $this->arrayParams = To array με τις παραμέτρους για το search
    public function getSearchElements()
    {

        // Αν υπάρχει προηγούμενο query παίρνει τις τιμές από αυτό. Αν όχι κάνει τους νέους υπολογισμούς
        if(!$this->getQueryFromSessions()) {

            if ($this->fieldsArray) { // Αν έχει δοθεί json array με τα πεδία
                foreach ($this->fieldsArray as $field) {
                    // Μετατρέπει το $field array σε sql query string
                    $this->getFieldString($field);
                }

                // Καθαρισμός το τελικού string
                $this->condition = Utilities::cutLastString($this->condition, 'OR ');
                //            $condition = page::cutLastString($condition, 'AND ');

            } else { // αλλιώς τα αρχικοποιεί
                $this->condition = null;
                $this->arrayParams = array();
            }

            // Θέτει τις τιμές του query σε sessions για να υπάρχουν για επόμενη χρήση
            $this->setQuerySessions();

        }

        // Προσθέτει στο query το join με τα files  με βάση το $this->mediaKind
        $this->insertMediaKindJoin();

    }

    // Επιστρέφει τις διπλοεγγραφές
    public function getDuplicateRecords()
    {
        // Την πρώτη φορά αντιγράφει την λίστα των διπλοεγγραφών στην $tempUserPlaylist
        if ($_SESSION['PlaylistCounter'] == 0) {

            $myQuery = 'SELECT files.id as file_id
                            FROM files JOIN music_tags on files.id=music_tags.id 
                            WHERE hash IN (SELECT hash FROM OWMP.files GROUP BY hash HAVING count(*) > 1) ORDER BY hash';

            // αντιγραφή του playlist σε αντίστοιχο $tempUserPlaylist table ώστε ο player να παίζει από εκεί
            MyDB::copyFieldsToOtherTable('file_id', $this->tempUserPlaylist, $myQuery, null);

            $_SESSION['$countThePlaylist'] = MyDB::countTable($this->tempUserPlaylist);
        }


        // Κάνει join την $tempUserPlaylist με τα music_tags και files για εμφάνιση της playlist
        $this->playlist = MyDB::getTableArray($this->mainTables, 'music_tags.*, files.path, files.filename, files.hash, files.kind',
            null, null, 'files.hash DESC LIMIT ' . $this->offset . ',' . $this->step, $this->tempUserPlaylist, $this->joinFieldsArray);
    }

    // Επιστρέφει την αρχική playlist
    public function getStartupPlaylist()
    {

        // Δημιουργούμε τα temporary tables
        // Αν είναι true το $loadPlaylist τότε δεν χρειάζεται να δημιουργηθεί temporary table. Υπάρχει ήδη
        // από την manual playlist
        if(!$this->loadPlaylist) { // Αν δεν είναι manual playlist
            $myQuery = MyDB::createQuery('music_tags', 'music_tags.id', $this->condition, 'date_added DESC', 'files', $this->joinFieldsArray);

            // Αν δεν υπάρχει ήδη το σχετικό table το δημιουργούμε
            self::checkTempPlaylist($this->tempUserPlaylist);

            // Δημιουργία και ενός played queue playlist
            self::checkTempPlaylist(PLAYED_QUEUE_PLAYLIST_STRING . $this->tabID);

            // αντιγραφή του playlist σε αντίστοιχο $tempUserPlaylist table ώστε ο player να παίζει από εκεί
            MyDB::copyFieldsToOtherTable('file_id', $this->tempUserPlaylist, $myQuery, $this->arrayParams);
        }

        // Μετράει τις εγγραφές που βρήκε
        $_SESSION['$countThePlaylist'] = MyDB::countTable($this->tempUserPlaylist);

    }

    // Παίρνει τα περιεχόμενα της playlist που ψάχνουμε
    public function getPlaylistResults()
    {

        if(!$this->loadPlaylist)
            $this->joinFieldsArray = array('firstField'=>'id', 'secondField'=>'id');
        else {
            $this->joinFieldsArray = array('firstField'=>'id', 'secondField'=>'file_id');
        }

        $this->mainTables = array('music_tags', 'files');

        if (!$this->tabID) {  // Αν δεν έρχεται από το attribute της κλάσης
            $this->tabID = TAB_ID;  // Την πρώτη φορά που τρέχει η εφαρμογή το παίρνει από το TAB_ID
        }

        // Το όνομα του temporary user playlist table για τον συγκεκριμένο χρήστη
        if($this->votePlaylist) {
            $this->tempUserPlaylist = JUKEBOX_LIST_NAME;
        } else {
            $this->tempUserPlaylist = CUR_PLAYLIST_STRING . $this->tabID;
        }

        if($this->duplicates==null) {   // κανονική λίστα
            // Όταν φορτώσει για πρώτη φορά η εφαρμογή
            if ($_SESSION['PlaylistCounter'] == 0) {
                $this->getStartupPlaylist();
            }

            // Η λίστα προς εμφάνιση
            if(!$this->loadPlaylist) {  // Αν το $this->loadPlaylist είναι false. Δηλαδή δεν είναι manual playlist
                $this->playlist = MyDB::getTableArray('music_tags', null, $this->condition, $this->arrayParams,
                    'date_added DESC LIMIT ' . $this->offset . ',' . $this->step, 'files', $this->joinFieldsArray);
            }
            else { // αλλιώς κάνει join με τον $this->tempUserPlaylist. Όταν είναι manual playlist δηλαδή
                $this->playlist = MyDB::getTableArray($this->mainTables, 'music_tags.*, files.path, files.filename, files.hash, files.kind',
                    null, null, 'date_added DESC LIMIT ' . $this->offset . ',' . $this->step, $this->tempUserPlaylist, $this->joinFieldsArray);
            }


        } else {  // εμφάνιση διπλών εγγραφών
            $this->getDuplicateRecords();
        }

    }

    // Εμφανίζει τα περιεχόμενα της playlist
    public function displayPlaylistContent()
    {
        $counter = 0;

        ?>

        <div id="playlist_content">

            <?php
            // TODO δεν παίζουν οι σελίδες όταν εμφανίζει manual playlists ή την ουρά

            // Εμφάνιση των κουμπιών previous/next
            $this->getBrowseButtons();

            ?>

            <div id="playlistTable">
                <?php

                // Αν δεν είναι η σελίδα vote εμφανίζει τον τίτλο
                if(!$this->votePlaylist && !$_SESSION['mobile']) {
                    self::displayPlaylistTitle();
                }


                foreach ($this->playlist as $track) {

                    if(!$this->votePlaylist && !$_SESSION['mobile']) { // Αν δεν είναι η σελίδα vote ή mobile
                        // Εμφανίζει την λίστα με τα πλήρη στοιχεία
                        $this->displayFullPlaylist($track);
                    } else { // Αν είναι η σελίδα vote
                        // Εμφανίζει την λίστα με ελάχιστα στοιχεία
                        self::displaySmallPlaylist($track);
                    }

                    $counter++;
                }


                $this->offset = intval($this->offset);
                $this->step = intval($this->step);
                ?>


            </div>

            <?php

            // Εμφάνιση των κουμπιών previous/next
            $this->getBrowseButtons();

            ?>

            <div id="error_container">
                <div class="alert_error bgc9"></div>
            </div>

        </div>

        <?php
    }

    // Εμφανίζει την playlist με βάση διάφορα keys αναζήτησης
    //    @param string $this->fieldsArray Το json array που περιέχει τα πεδία για το search query
    //    @param integer $this->offset Το offset για το limit στο sql query
    //    @param integer $this->step  Tο βήμα για το limit στο sql query
    //    @param boolean $this->duplicates   Τrue αν θέλουμε να ψάξει για duplicates
    //    @param string $this->mediaKind   Ορίζεται το media kind για το οποίο θα γίνει η αναζήτηση. null για όλα
    //    @param string $this->tabID   To temp ID για το τρέχον session του tab που τρέχει η εφαρμογή
    //    @param boolean $this->loadPlaylist   True αν πρόκειται για manual playlist
    //    @param boolean $this->votePlaylist    True αν θέλουμε να εμφανιστεί η λίστα για την σελίδα των votes
    //    @param string $this->condition  Το sql query για search
    //    @param array $this->arrayParams  Οι παράμετροι που θα περάσουν στο sql query
    public function getPlaylist()
    {

        // Διαβάζει το json array $fieldsArray και επιστρέφει το search query μαζί με τους παραμέτρους
        $this->getSearchElements();

        // Παίρνει τα περιεχόμενα της playlist που ψάχνουμε
        $this->getPlaylistResults();

        // Αρχίζει η εμφάνιση της playlist
        if(isset($this->playlist)) {

            // Εμφανίζει τα περιεχόμενα της playlist
            $this->displayPlaylistContent();

            ?>

            <script type="text/javascript">
                var playlistCount = <?php echo $_SESSION['$countThePlaylist']; ?>;
            </script>

            <?php
        }

        if ($_SESSION['PlaylistCounter'] == 0 && !$this->votePlaylist) {
            ?>

            <script type="text/javascript">
                init();
            </script>

            <?php
        }

        $_SESSION['PlaylistCounter']++;

    }

}