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
 * Κλάση που χειρίζεται την αναζήτηση και εμφάνιση της playlist
 *
 * Βασική μέθοδος που καλείται
 *
 * getPlaylist()
 *
 */

namespace apps4net\parrot\app;

use apps4net\framework\MyDB;
use apps4net\framework\Page;
use apps4net\framework\User;
use apps4net\framework\Utilities;


class PlaylistSearch extends OWMPElements
{
    // Attributes της κλάσης
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
    public $lastOperator; // Το τελευταίο operator που εμφανίστηκε, για να το σβήσουμε
    public $currentBrowsePageNo = 0;  // The current browse page number
    public $numberOfPages;  // Sum of pages in playlist
    public $pagesArray = []; // Array of pages to display
    public $sort_by = 'date_added';
    public $order = 'DESC';

    protected $searchFunction;
    protected $previousFunction;
    protected $nextFunction;

    /**
     * Get the array of pages to display in pagination
     */
    public function getBrowsePagesArray()
    {

        for ($page=0; $page<=$this->numberOfPages; $page++) {
            $this->pagesArray[] = $page;
        }

        // If pages are to many to display, break into two arrays
        if($this->numberOfPages>15) {
            $firstArraySlice = array_slice($this->pagesArray, 0, 4);
            $lastArraySlice = array_slice($this->pagesArray, $this->numberOfPages - 4, 4);

            // If current page does axist in my arrays
            if (in_array($this->currentBrowsePageNo, $firstArraySlice) == false
                && in_array($this->currentBrowsePageNo, $lastArraySlice) == false) {

                // Change values of firstArraySlive to be next to current page
                $currentPageDifference = $this->currentBrowsePageNo - end($firstArraySlice);
                foreach ($firstArraySlice as $key => $page) {
                    $firstArraySlice[$key] = $page + $currentPageDifference;
                }

            }

            // Merge the arrays and set a space between them
            $this->pagesArray = array_merge($firstArraySlice, [' '], $lastArraySlice);
        }
    }

    /**
     * Display the pagination elements
     */
    public function displayPaginationElements()
    {
        if($this->numberOfPages>0) { // If there is more than one page, display the pagination
            ?>

            <nav class="h-100" aria-label="Page navigation">
                <ul class="playlistPagination pagination justify-content-center pagination-sm">

                    <?php
                    if ($this->currentBrowsePageNo > 0) {
                        ?>
                        <li class="page-item">
                            <a class="page-link" href="#" aria-label="Previous"
                               onclick="makePageActive(0, 'prev'); <?php echo $this->previousFunction; ?>">
                                <span aria-hidden="true">&laquo;</span>
                                <span class="sr-only">Previous</span>
                            </a>
                        </li>
                        <?php
                    }

                    foreach ($this->pagesArray as $page) {
                        // Get the current page offset
                        if ($page !== ' ') {
                            $pageFunction = $this->searchFunction . '(' . ($page * $this->step) . ',' . $this->step . ', false, ' . htmlentities(json_encode($this->fieldsArray)) . ');';
                            ?>
                            <li class="browsePageNoID<?php echo $page; ?> browsePageNumber page-item <?php echo ($page == $this->currentBrowsePageNo) ? 'active' : ''; ?>">
                                <a class="page-link" href="#"
                                   onclick="makePageActive(<?php echo $page; ?>, false); <?php echo $pageFunction; ?> "><?php echo $page; ?></a>
                            </li>
                            <?php
                        } else {
                            ?>
                            <li class="page-item disabled">
                                <a class="page-link" href="#">...</a>
                            </li>
                            <?php
                        }
                    }
                    ?>
                    <?php

                    if ($this->currentBrowsePageNo < $this->numberOfPages) {
                        ?>
                        <li class="page-item">
                            <a class="page-link" href="#" aria-label="Next"
                               onclick="makePageActive(0, 'next'); <?php echo $this->nextFunction; ?>">
                                <span aria-hidden="true">&raquo;</span>
                                <span class="sr-only">Next</span>
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </nav>

            <?php
        }
    }

    /**
     * Get pagination values
     */
    public function getBrowseButtons()
    {
        // Έλεγχος για το τι είδους λίστα εμφανίζει
        if (!$this->duplicates && !$this->votePlaylist && !$this->loadPlaylist) {
            $_SESSION['operation'] = 'search';
        } else {
            if($this->duplicates) {
                $_SESSION['operation'] = 'duplicates';
            }

            if($this->votePlaylist) {
                $_SESSION['operation'] = 'votePlaylist';
            }

            if($this->loadPlaylist) {
                $_SESSION['operation'] = 'manualPlaylist';
            }
        }

        // Find the javascript function we gonna use for search
        switch ($_SESSION['operation']) {
            case 'search' : $this->searchFunction = 'searchPlaylist'; break;
            case 'duplicates' : $this->searchFunction = 'findDuplicates'; break;
            case 'votePlaylist' : $this->searchFunction = 'getVotePlaylist'; break;
            case 'manualPlaylist' : $this->searchFunction = 'playMyPlaylist'; break;
        }

//        var_dump($this->fieldsArray);

        // Get the number of pages
        $this->numberOfPages = (int)($_SESSION['countThePlaylist'] / $this->step);

        // Get the javascript functions with parameters
        $this->previousFunction = $this->searchFunction . '(' . (($this->offset > 0) ? ($this->offset - $this->step) : '0') . ',' . $this->step . ', false, ' . htmlentities(json_encode($this->fieldsArray)) . ');';
        $this->nextFunction = $this->searchFunction . '(' . ((($this->offset + $this->step) < $_SESSION['countThePlaylist']) ? ($this->offset + $this->step) : $this->offset) . ',' . $this->step . ', false, ' . htmlentities(json_encode($this->fieldsArray)) . ');';

        $this->getBrowsePagesArray(); // Get the array of pages to display in pagination

    }

    // Εμφανίζει την πρώτη γραμμή με τις επικεφαλίδες στην playlist
    public function displayPlaylistTitle()
    {
        ?>

        <thead class="thead-dark">
            <tr>

                <th scope="col" class="cell-wrap mcw-1"></th>

                <th scope="col" class="cell-fit">
                    <input type="checkbox" id="checkAll" name="checkAll" class="d-none d-lg-inline-block"
                           onchange="changeCheckAll('checkAll', 'check_item[]');">
                </th>


                <th scope="col" class="cell-wrap mcw-6" title="<?php echo __('tag_title'); ?>">
                    <?php echo __('tag_title'); ?>
                </th>
                <th scope="col" class="cell-wrap mcw-3" title="<?php echo __('tag_artist'); ?>">
                    <?php echo __('tag_artist'); ?>
                </th>
                <th scope="col" class="cell-wrap mcw-2 d-none d-lg-table-cell" title="<?php echo __('tag_album'); ?>">
                    <?php echo __('tag_album'); ?>
                </th>
                <th scope="col" class="cell-wrap mcw-2 d-none d-lg-table-cell" title="<?php echo __('tag_genre'); ?>">
                    <?php echo __('tag_genre'); ?>
                </th>
                <th scope="col" class="cell-wrap mcw-2 d-none d-lg-table-cell" title="<?php echo __('tag_year'); ?>">
                    <?php echo __('tag_year'); ?>
                </th>
                <th scope="col" class="cell-wrap mcw-1 d-none d-lg-table-cell" title="<?php echo __('tag_play_count'); ?>">
                    <?php echo __('tag_play_count'); ?>
                </th>
                <th scope="col" class="cell-wrap mcw-1 d-none d-lg-table-cell" title="<?php echo __('tag_rating'); ?>">
                    <?php echo __('tag_rating'); ?>
                </th>
                <th scope="col" class="cell-wrap mcw-3  d-none d-lg-table-cell" title="<?php echo __('tag_date_added'); ?>">
                    <?php echo __('tag_date_added'); ?>
                </th>

            </tr>
        </thead>

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
        $images = new Images();

        if(!$this->votePlaylist) {
            $conn = new MyDB();
            $user = new User();

            $UserGroupID = $user->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης
        }

        ?>

            <tr class="track" id="fileID<?php echo $track['id']; ?>"
                 onmouseover="displayCoverImage('fileID<?php echo $track['id']; ?>');"
                 onmouseout="hideCoverImage();">

                <td class="cell-fit">
                    <span class="<?php echo ($track['kind'] == 'Music') ? 'mdi mdi-music-box-outline mdi-18px' : 'mdi mdi-video mdi-18px'; ?>"
                          title="<?php echo ($track['kind'] == 'Music') ? 'Music' : 'Music Video'; ?>">
                    </span>
                </td>


                <td class="cell-fit">

                    <?php

                    if ($track['kind'] == 'Music') {
                        if($coverImagePath = $images->getAlbumImagePath($track['album_artwork_id'], 'small')) {

                            ?>
                            <img class="coverImage" src="<?php echo AJAX_PATH . 'app/serveImage?imagePath=' . $coverImagePath; ?>">
                            <?php
                        }
                    }
                    ?>

                    <input type="checkbox" id="check_item[]" name="check_item[]" class="d-none d-lg-inline-block"
                           value="<?php echo $track['id']; ?>">

                    <span class="mdi mdi-play mdi-18px hasCursorPointer" title="<?php echo __('play_file'); ?>"
                          onclick="loadNextVideo(<?php echo $track['id']; ?>); myVideo.play();">
                    </span>

					<span class="mdi mdi-playlist-music mdi-18px  hasCursorPointer d-lg-inline-block"
						  title="<?php echo __('queue_song'); ?>"
						  onclick="queueSong(<?php echo $track['id']; ?>);">
                    </span>

                    <?php
                    if (!$this->loadPlaylist) { ?>
                        <span class="mdi mdi-playlist-plus mdi-18px hasCursorPointer d-none d-lg-inline-block"
                               title="<?php echo __('add_to_playlist'); ?>"
                               onclick="addToPlaylist(<?php echo $track['id']; ?>);">
                        </span>
                        <?php
                    } else { ?>
                        <span class="mdi mdi-playlist-minus mdi-18px hasCursorPointer d-none d-lg-inline-block"
                               title="<?php echo __('remove_from_playlist'); ?>"
                               onclick="removeFromPlaylist(<?php echo $track['id']; ?>);">
                        </span>
                        <?php
                    }
                    ?>

                    <?php
                    if ($UserGroupID == 1) {
                        ?>
                        <span class="mdi mdi-delete mdi-18px hasCursorPointer d-none d-lg-inline-block"
                               title="<?php echo __('delete_file'); ?>"
                               onclick="deleteFile(<?php echo $track['id']; ?>);">
                        </span>
                        <?php
                    }
                    ?>
                </td>


                <td class="cell-wrap mcw-6 song_name" title="<?php echo $track['song_name']; ?>">
					<span class="<?php echo ($track['live'] == 1) ? 'mdi mdi-access-point mdi-18px' : ''; ?>"></span>
                    <span class="searchableItem" onclick="searchPlaylist(0, <?php echo PLAYLIST_LIMIT; ?>, true,
                        <?php echo htmlentities(json_encode(self::getSearchArray('song_name', $track['song_name']))); ?>);">
                            <?php echo $track['song_name']; ?>
                    </span>
                </td>
                <td class="cell-wrap mcw-3 artist" title="<?php echo $track['artist']; ?>">
                    <span class="searchableItem"  onclick="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>,true,
                        <?php echo htmlentities(json_encode(self::getSearchArray('artist', $track['artist']))); ?>);">
                            <?php echo $track['artist']; ?>
                    </span>
                </td>

                <td class="cell-wrap mcw-2 d-none d-lg-table-cell album" title="<?php echo $track['album']; ?>">
                    <span class="searchableItem"  onclick="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>,true,
                        <?php echo htmlentities(json_encode(self::getSearchArray('album', $track['album']))); ?>);">
                            <?php echo $track['album']; ?>
                    </span>
                </td>
                <td class="cell-wrap mcw-2 d-none d-lg-table-cell genre" title="<?php echo $track['genre']; ?>">
                    <span class="searchableItem"  onclick="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>,true,
                        <?php echo htmlentities(json_encode(self::getSearchArray('genre', $track['genre']))); ?>);">
                            <?php echo $track['genre']; ?>
                    </span>
                </td>
                <td class="cell-wrap mcw-2 text-center d-none d-lg-table-cell song_year" title="<?php if ($track['song_year'] == '0') echo ''; else echo $track['song_year']; ?>">
                    <span class="searchableItem"  onclick="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>,true,
                        <?php echo htmlentities(json_encode(self::getSearchArray('song_year', $track['song_year']))); ?>);">
                            <?php if ($track['song_year'] == '0') echo ''; else echo $track['song_year']; ?>
                    </span>
                </td>
                <td class="cell-wrap mcw-1 text-center d-none d-lg-table-cell play_count" title="<?php echo $track['play_count']; ?>">
                    <?php echo $track['play_count']; ?>
                </td>
                <td class="cell-wrap mcw-1 text-center d-none d-lg-table-cell rating" title="<?php echo(($track['rating'] / 20)); ?>">
                    <?php echo(($track['rating'] / 20)); ?>
                </td>
                <td class="cell-wrap mcw-3 text-center d-none d-lg-table-cell date_added" title="<?php echo $track['date_added']; ?>">
                    <?php echo date(DATE_FORMAT, strtotime($track['date_added'])); ?>
                </td>

            </tr>

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
                    case 'not':
                        $equality_sign = ' IS NOT ';
                        break;
                }

                // Τελικό sql query
                $this->condition = $this->condition . $field['search_field'] . $equality_sign . '? ' . $field['search_operator'] . ' ';
                $this->arrayParams[] = $searchText;
            } else {   // αν είναι string
                $searchText = ClearString($field['search_text']);

                $this->condition = $this->condition . $field['search_field']
					. (($field['search_equality'] == 'not') ? ' NOT' : null)
					. ' LIKE ? ' . $field['search_operator'] . ' ';
                $this->arrayParams[] = '%' . $searchText . '%';
            }

            $this->lastOperator = ' '.$field['search_operator'].' ';
        }
    }

    // Θέτει τις τιμές του query σε sessions για μελλοντική χρήση
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

    // Αν υπάρχει τιμή στα query sessions παίρνει τις τιμές από αυτά
    //      @return: boolean True/False
    public function getQueryFromSessions()
    {
        if( isset($_SESSION['condition']) || isset($_SESSION['arrayParams']) ) {
            $this->condition = $_SESSION['condition'];
            $this->arrayParams = $_SESSION['arrayParams'];

            return true;
        } else {
            return false;
        }

    }

    // Προσθέτει στο query το join με τα files  με βάση το $this->mediaKind
    //      @param: string $this->mediaKind  Το media kind που έχει επιλέξει ο χρήστης
    //      @return: string $this->condition = To search query
    //      @return: array $this->arrayParams = To array με τις παραμέτρους για το search
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

    // Διαβάζει το json array $this->fieldsArray και επιστρέφει το search query μαζί με τους παραμέτρους
    //      @param: array $this->fieldsArray = Το json array που θα διαβάσει
    //      @return: string $this->condition = To search query
    //      @return: array $this->arrayParams = To array με τις παραμέτρους για το search
    public function getSearchElements()
    {

        // Αν υπάρχει προηγούμενο query παίρνει τις τιμές από αυτό. Αν όχι κάνει τους νέους υπολογισμούς
        if ($this->fieldsArray) { // Αν έχει δοθεί json array με τα πεδία

            $this->condition = '('; // Η αρχική παρένθεση

            foreach ($this->fieldsArray as $key=>$field) {
                // Μετατρέπει το $field array σε sql query string
                $this->getFieldString($field);

                // Αν υπάρχει group_operator τότε σπάει το query string με αυτό το operator
                if(isset($field['group_operator'])) { // Αν είναι groupRow προσθέτουμε το ανάλογο operator
                    $this->condition = Utilities::cutLastString($this->condition, $this->lastOperator);
                    $this->condition = $this->condition.') '.$field['group_operator'].' (';
                }
            }

            // Καθαρισμός το τελικού string και προσθήκη της τελικής παρένθεσης
            $this->condition = Utilities::cutLastString($this->condition, $this->lastOperator);
            $this->condition = $this->condition.')';

            // Αν είναι κενό
            if($this->condition=='' || $this->condition==')') {
                $this->condition = null;
            }

            // Θέτει τις τιμές του query σε sessions για να υπάρχουν για επόμενη χρήση
            $this->setQuerySessions();

        } else { // αλλιώς τα αρχικοποιεί
            // Αν υπάρχει προηγούμενο query παίρνει τις τιμές από αυτό. Αλλιώς αρχικοποιεί
            if(!$this->getQueryFromSessions()){
                $this->condition = null;
                $this->arrayParams = array();
            }
        }

        // Προσθέτει στο query το join με τα files  με βάση το $this->mediaKind
        $this->insertMediaKindJoin();

    }

    // Επιστρέφει τις διπλοεγγραφές
    //      @return: array $this->playlist  Τα περιεχόμενα της λίστας που θα εμφανίσει
    public function getDuplicateRecords()
    {
        $this->joinFieldsArray = array('firstField'=>'id', 'secondField'=>'file_id');

        // Την πρώτη φορά αντιγράφει την λίστα των διπλοεγγραφών στην $tempUserPlaylist
        if ($_SESSION['PlaylistCounter'] == 0) {
            $myQuery = 'SELECT files.id as file_id
                            FROM files 
                            WHERE hash IN (SELECT hash FROM files GROUP BY hash HAVING count(*) > 1) ORDER BY hash ASC';

            // αντιγραφή του playlist σε αντίστοιχο $tempUserPlaylist table ώστε ο player να παίζει από εκεί
            MyDB::copyFieldsToOtherTable('file_id', $this->tempUserPlaylist, $myQuery, null);

            $_SESSION['countThePlaylist'] = MyDB::countTable($this->tempUserPlaylist);
        }

        // Κάνει join την $tempUserPlaylist με τα music_tags και files για εμφάνιση της playlist
        $this->playlist = MyDB::getTableArray(
                $this->mainTables,
                'music_tags.*, files.path, files.filename, files.hash, files.kind',
                null,
                null,
                'files.hash DESC LIMIT ' . $this->offset . ',' . $this->step,
                $this->tempUserPlaylist,
                $this->joinFieldsArray);

    }

    // Δημιουργεί τα αρχικά temporary tables με την αρχική λίστα
    //      return: void
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
        $_SESSION['countThePlaylist'] = MyDB::countTable($this->tempUserPlaylist);

    }

    // Παίρνει τα περιεχόμενα της playlist που ψάχνουμε
    public function getPlaylistResults()
    {
        // Τα arrays για να γίνει το join των πινάκων
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

        // Αρχίζει το search
        if($this->duplicates==null) {   // κανονική λίστα
            // Όταν φορτώσει για πρώτη φορά η εφαρμογή
            if ($_SESSION['PlaylistCounter'] == 0) {
                // Δημιουργεί τα αρχικά temporary tables με την αρχική λίστα
                $this->getStartupPlaylist();
            }

            //  Η λίστα προς εμφάνιση όταν γίνεται search
            if(!$this->loadPlaylist) {  // Αν το $this->loadPlaylist είναι false. Δηλαδή δεν είναι manual playlist
                // το βασικό search
                $this->playlist = MyDB::getTableArray('music_tags', null, $this->condition, $this->arrayParams,
                    $this->sort_by . ' ' . $this->order . ' LIMIT ' . $this->offset . ',' . $this->step, 'files', $this->joinFieldsArray);
            }
            else { // αλλιώς κάνει join με τον $this->tempUserPlaylist. Όταν είναι manual playlist δηλαδή
                $this->playlist = MyDB::getTableArray($this->mainTables, 'music_tags.*, files.path, files.filename, files.hash, files.kind',
                    null, null, $this->sort_by . ' ' . $this->order . ' LIMIT ' . $this->offset . ',' . $this->step, $this->tempUserPlaylist, $this->joinFieldsArray);
            }

        } else {  // εμφάνιση διπλών εγγραφών
            $this->getDuplicateRecords();
        }

    }

    // Εμφανίζει τα περιεχόμενα της playlist
    public function displayPlaylistContent()
    {
//        $counter = 0;

        ?>

        <div id="playlist_content" class="table-responsive">

            <?php

            // Display pagination
            $this->getBrowseButtons(); // Get Pagination values
            $this->displayPaginationElements(); // Display pagination elements

            ?>

            <table id="playlistTable" class="table table-hover table-sm table-nowrap">
                <?php

                    // Display list labels
                    $this->displayPlaylistTitle();

                ?>

                <tbody>

                <?php

                foreach ($this->playlist as $track) {

                    // Εμφανίζει την λίστα με τα πλήρη στοιχεία
                    $this->displayFullPlaylist($track);

//                    $counter++;
                }

                ?>

                </tbody>

                <?php

                $this->offset = intval($this->offset);
                $this->step = intval($this->step);
                ?>


            </table>

            <?php

            $this->displayPaginationElements(); // Display pagination elements


            ?>


        </div>

        <?php
    }

    // ΒΑΣΙΚΗ ΜΕΘΟΔΟΣ ΤΗΣ ΚΛΑΣΗΣ
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

            // Στέλνει στην javascript το σύνολο των εγγραφών που βρέθηκαν
            ?>
            <script type="text/javascript">
                var playlistCount = <?php echo $_SESSION['countThePlaylist']; ?>;
            </script>
            <?php

        }

        // Όταν φορτώνεται για πρώτη φορά playlist
        if ($_SESSION['PlaylistCounter'] == 0 && !$this->votePlaylist) {

            // Αρχικοποιεί το video element στην javascript
            ?>
            <script type="text/javascript">
                init();
            </script>
            <?php

        }

        // Αυξάνει την τιμή που σημαίνει ότι έχει φορτώσει για πρώτη φορά η σελίδα (δεν είναι 0 δηλαδή)
        $_SESSION['PlaylistCounter']++;

    }

}
