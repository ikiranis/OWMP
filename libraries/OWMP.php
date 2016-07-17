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
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '255',
                'pattern' => '',
                'title' => '',
                'disabled' => $disabled,
                'value' => null),
            array('name' => 'artist',
                'fieldtext' => __('tag_artist'),
                'type' => 'text',
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '255',
                'pattern' => '',
                'title' => '',
                'disabled' => $disabled,
                'value' => null),
            array('name' => 'genre',
                'fieldtext' => __('tag_genre'),
                'type' => 'text',
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '20',
                'pattern' => '',
                'title' => '',
                'disabled' => $disabled,
                'value' => null),
            array('name' => 'year',
                'fieldtext' => __('tag_year'),
                'type' => 'number',
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '',
                'pattern' => '',
                'title' => '',
                'disabled' => $disabled,
                'value' => null),
            array('name' => 'rating',
                'fieldtext' => __('tag_rating'),
                'type' => 'text',
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '5',
                'pattern' => '',
                'title' => '',
                'disabled' => $disabled,
                'value' => null),
            array('name' => 'live',
                'fieldtext' => __('tag_live'),
                'type' => 'text',
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '1',
                'pattern' => '',
                'title' => '',
                'disabled' => $disabled,
                'value' => null),
            array('name' => 'album',
                'fieldtext' => __('tag_album'),
                'type' => 'text',
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '255',
                'pattern' => '',
                'title' => '',
                'disabled' => $disabled,
                'value' => null),

            array('name' => 'play_count',
                'fieldtext' => __('tag_play_count'),
                'type' => 'number',
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '',
                'pattern' => '',
                'title' => '',
                'disabled' => 'yes',
                'value' => null),
            array('name' => 'track_time',
                'fieldtext' => __('tag_track_time'),
                'type' => 'text',
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '10',
                'pattern' => '',
                'title' => '',
                'disabled' => 'yes',
                'value' => null),
            array('name' => 'date_added',
                'fieldtext' => __('tag_date_added'),
                'type' => 'text',
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '20',
                'pattern' => '',
                'title' => '',
                'disabled' => 'yes',
                'value' => null),
            array('name' => 'date_played',
                'fieldtext' => __('tag_date_played'),
                'type' => 'text',
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '20',
                'pattern' => '',
                'title' => '',
                'disabled' => 'yes',
                'value' => null),

            array('name' => 'path_filename',
                'fieldtext' => __('tag_path_filename'),
                'type' => 'text',
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '255',
                'pattern' => '',
                'title' => '',
                'disabled' => 'yes',
                'value' => null)

//            array('name' => 'submit',
//                'fieldtext' => '',
//                'type' => 'button',
//                'onclick' => 'update_tags();',
//                'required' => 'no',
//                'maxlength' => '',
//                'pattern' => '',
//                'title' => '',
//                'disabled' => $disabled,
//                'value' => __('tag_form_submit'))
        );


        ?>

        <video id="myVideo" width="100%"  controls autoplay onerror="failed(event)"></video>

        <!--        Fullscreen overlay elements-->
        <div id="overlay">
            <div id="bottom_overlay">
                <span id="overlay_song_name"></span>
                <span id="overlay_artist"></span>
                <span id="overlay_song_year"></span>
                <span id="overlay_album"></span>
            </div>
            <div id="overlay_rating"></div>
            <div id="overlay_play_count"></div>
        </div>


        <div id="tags">

            <?php $tags->MakeForm('FormTags', $FormElementsArray, true); ?>

            <input type="button" name="submit" id="submit" <?php if($disabled=='yes') echo ' disabled '; ?>
                value="<?php echo __('tag_form_submit'); ?>" onclick="update_tags();">

        </div>

        <input type="button" class="message" id="message">





        <?php

    }


    static function showPlaylistWindow ($offset, $step) {

        $fields=RoceanDB::getTableFields('music_tags',array('id','album_artwork_id'));

        ?>

        <details>
            <summary>
                Search
            </summary>
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
                                        <?php echo $field; ?>
                                    </option>

                                    <?php
                                }
                                ?>
                            </select>
                        </label>

                        <select class="search_equality" name="search_equality<?php echo $counter; ?>" id="search_equality<?php echo $counter; ?>">

                            <option value="equal">
                                Equal
                            </option>

                            <option value="greater">
                                Greater
                            </option>

                            <option value="less">
                                Less
                            </option>


                        </select>

                        <label for="search_text<?php echo $counter; ?>">
                            <input type="text" name="search_text<?php echo $counter; ?>" id="search_text<?php echo $counter; ?>">
                        </label>

                        <select class="search_operator" name="search_operator<?php echo $counter; ?>" id="search_operator<?php echo $counter; ?>">

                                <option value="OR">
                                    OR
                                </option>

                                <option value="AND">
                                    AND
                                </option>

                        </select>
                    </div>

                    <?php
                    }
                    ?>

                    <input type="button" name="searching" id="searching" value="Search" onclick="searchPlaylist(0,<?php echo PLAYLIST_LIMIT; ?>, true, 5);">


                </form>
            </div>


        </details>

        <div id="playlist_container">
            <?php
                if($_SESSION['PlaylistCounter']==0) {
                    $_SESSION['condition']=null;   // Μηδενίζει το τρέχον search query
                    $_SESSION['arrayParams']=null;
                    self::getPlaylist(null,$offset,$step);
                }
                else {
                    ?>
                        <div id="playlistTable"></div>
                    <?php
                }
            ?>

        </div>





        <?php



    }

    // Εμφάνιση των εγγραφών των options σε μορφή form fields για editing
    static function getOptionsInFormFields () {
        $conn = new RoceanDB();

        $options=$conn->getTableArray('options', null, 'setting=?', array(1), null);  // Παίρνει τα δεδομένα του πίνακα options σε array


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

            <input type="button" class="insert_row" name="insert_user" onclick="insertUser();" value="<?php echo __('insert_row'); ?>">
            <?php
        }
        ?>

        <?php
        $stmt->closeCursor();
        $stmt = null;

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

        <input type="button" id="startSync" name="startSync" onclick="startSync();" value="<?php echo __('Synchronize'); ?>">

        <div id="SyncDetails">
            <div id="progress"></div>
        </div>


        <?php
    }

    // Εμφανίζει την playlist με βάση διάφορα keys αναζήτησης
    static function getPlaylist($fieldsArray=null, $offset, $step) {

        $condition='';
        $arrayParams=array();

        if($fieldsArray)
            foreach ($fieldsArray as $field) {

                if($field['search_text']==='0')  // Βάζει ένα κενό όταν μηδέν, αλλιώς το νομίζει null
                    $searchText = ' '.$field['search_text'];
                else
                    $searchText = $field['search_text'];

                if( (!$field==null) && (!$searchText==null) ) {  // αν ο πίνακας δεν είναι κενός και αν το search text δεν είναι κενό

                    $fieldType=RoceanDB::getTableFieldType('music_tags',$field['search_field']);  // παίρνει το type του field
//                    trigger_error($fieldType);
                    if ( $fieldType=='int(11)' || $fieldType=='tinyint(4)' || $fieldType=='datetime' ) {   // αν το type είναι νούμερο
                        if($fieldType=='datetime')
                            $searchText = $field['search_text'];
                        else $searchText = intval($field['search_text']);  // μετατροπή του κειμένου σε νούμερο

                        $equality=$field['search_equality'];
                        switch ($equality) {
                            case 'equal': $equality_sign='='; break;
                            case 'greater': $equality_sign='>'; break;
                            case 'less': $equality_sign='<'; break;
                        }

                        $condition = $condition . $field['search_field'] . $equality_sign.'? ' . $field['search_operator'] . ' ';
                        $arrayParams[]=$searchText;
                    }
                    else {   // αν είναι string
                        $searchText=ClearString($field['search_text']);
                        $condition = $condition . $field['search_field'] . ' LIKE ? ' . $field['search_operator'] . ' ';
                        $arrayParams[]='%'.$searchText.'%';
                    }
                    
                    


                }
            }

        if (!$condition=='') {
            $condition = page::cutLastString($condition, 'OR ');
//            $condition = page::cutLastString($condition, 'AND ');

            $_SESSION['condition']=$condition;  // Το κρατάει σε session για μελοντική χρήση
            $_SESSION['arrayParams']=$arrayParams;

        }
        else $condition=null;

        if(isset($_SESSION['condition']))
            $condition=$_SESSION['condition'];
        
        if(isset($_SESSION['arrayParams']))
            $arrayParams=$_SESSION['arrayParams'];
        

        

//        trigger_error($condition);

        if(!isset($_SESSION['PlaylistCounter'])){
            $_SESSION['PlaylistCounter']=0;
            $_SESSION['condition']=null;
            $_SESSION['arrayParams']=null;
        }
            

        if($_SESSION['PlaylistCounter']==0) {
            $playlistToPlay = RoceanDB::getTableArray('music_tags', 'id', $condition, $arrayParams, 'date_added DESC'); // Ολόκληρη η λίστα
            $_SESSION['$countThePlaylist'] = count($playlistToPlay);
        }

        $playlist=RoceanDB::getTableArray('music_tags', null, $condition, $arrayParams, 'date_added DESC LIMIT '.$offset.','.$step);  // Η λίστα προς εμφάνιση


        $counter=0;
        ?>

        <div id="playlistTable">
            <?php

            foreach ($playlist as $track) {
                ?>
                <div id="fileID<?php echo $track['id']; ?>" class="track" onclick="loadNextVideo(<?php echo $track['id']; ?>);">
                    <div class="tag song_name">
                        <?php echo $track['song_name']; ?>
                    </div>
                    <div class="tag artist">
                        <?php echo $track['artist']; ?>
                    </div>
                    <div class="tag genre">
                        <?php echo $track['genre']; ?>
                    </div>
                    <div class="tag song_year">
                        <?php echo $track['song_year']; ?>
                    </div>
                    <div class="tag play_count">
                        <?php echo $track['play_count']; ?>
                    </div>
                    <div class="tag rating">
                        <?php echo ( ($track['rating']/10)/2 ); ?>
                    </div>
                    <div class="tag date_added">
                        <?php echo $track['date_added']; ?>
                    </div>
                </div>

                <?php
                $counter++;
            }

            $offset=intval($offset);
            $step=intval($step);
            ?>

            <input id="previous" type="button" value="previous" onclick="searchPlaylist(<?php if($offset>0) echo $offset-$step; ?>,<?php echo $step; ?>);">
            <input id="next" type="button" value="next" onclick="searchPlaylist(<?php if( ($offset+$step)<$_SESSION['$countThePlaylist']) echo $offset+$step; ?>,<?php echo $step; ?>);">

        </div>

        
        <?php
        if($_SESSION['PlaylistCounter']==0) {
            ?>

            <script type="text/javascript">

                var files = <?php echo json_encode($playlistToPlay); ?>;

                

                init();

            </script>

            <?php
        }

        $_SESSION['PlaylistCounter']++;

    }
}
