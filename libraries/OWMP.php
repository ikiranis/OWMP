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


        $FormElementsArray = array(
            array('name' => 'title',
                'fieldtext' => __('tag_title'),
                'type' => 'text',
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '255',
                'pattern' => '',
                'title' => '',
                'disabled' => 'no',
                'value' => null),
            array('name' => 'artist',
                'fieldtext' => __('tag_artist'),
                'type' => 'text',
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '100',
                'pattern' => '',
                'title' => '',
                'disabled' => 'no',
                'value' => null),
            array('name' => 'genre',
                'fieldtext' => __('tag_genre'),
                'type' => 'text',
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '20',
                'pattern' => '',
                'title' => '',
                'disabled' => 'no',
                'value' => null),
            array('name' => 'year',
                'fieldtext' => __('tag_year'),
                'type' => 'number',
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '',
                'pattern' => '',
                'title' => '',
                'disabled' => 'no',
                'value' => null),
            array('name' => 'rating',
                'fieldtext' => __('tag_rating'),
                'type' => 'text',
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '5',
                'pattern' => '',
                'title' => '',
                'disabled' => 'no',
                'value' => null),
            array('name' => 'live',
                'fieldtext' => __('tag_live'),
                'type' => 'text',
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '1',
                'pattern' => '',
                'title' => '',
                'disabled' => 'no',
                'value' => null),
            array('name' => 'album',
                'fieldtext' => __('tag_album'),
                'type' => 'text',
                'onclick' => '',
                'required' => 'no',
                'maxlength' => '255',
                'pattern' => '',
                'title' => '',
                'disabled' => 'no',
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

            array('name' => 'submit',
                'fieldtext' => '',
                'type' => 'button',
                'onclick' => 'update_tags();',
                'required' => 'no',
                'maxlength' => '',
                'pattern' => '',
                'title' => '',
                'disabled' => 'no',
                'value' => __('tag_form_submit'))
        );


        ?>

        <video id="myVideo" width="100%"  controls autoplay onerror="failed(event)"></video>
        <div id="overlay"></div>

        <input type="button" onclick="loadAndplayNextVideo();" value="Επόμενο">

        <div id="tags">

            <?php $tags->MakeForm('FormTags', $FormElementsArray); ?>

        </div>

        <input type="button" class="message" id="message">





        <?php

    }


    static function showPlaylistWindow ($offset,$step) {
        ?>
        <h2><?php echo __('nav_item_1'); ?></h2>

        <div id="playlist_containter">
            <?php self::getPlaylist(null,null,null,$offset,$step); ?>

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

    // Εμφανίζει την playlist με βάση διάφορα keys αναζήτησης
    static function getPlaylist($title, $artist, $genre, $offset, $step) {

        if(!isset($_SESSION['PlaylistCounter']))
            $_SESSION['PlaylistCounter']=0;

        if($_SESSION['PlaylistCounter']==0) {
            $playlistToPlay = RoceanDB::getTableArray('music_tags', '*', null, null, 'date_added DESC'); // Ολόκληρη η λίστα
            $_SESSION['$countThePlaylist'] = count($playlistToPlay);
        }

        $playlist=RoceanDB::getTableArray('music_tags', '*', null,null,'date_added DESC LIMIT '.$offset.','.$step);  // Η λίστα προς εμφάνιση


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
        </div>

        <input id="previous" type="button" value="previous" onclick="DisplayWindow(1, <?php if($offset>0) echo $offset-$step; ?>,<?php echo $step; ?>);">
        <input id="next" type="button" value="next" onclick="DisplayWindow(1, <?php if( ($offset+$step)<$_SESSION['$countThePlaylist']) echo $offset+$step; ?>,<?php echo $step; ?>);">

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
