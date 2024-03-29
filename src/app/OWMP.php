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
use apps4net\framework\Utilities;

class OWMP
{


    // Εμφανίζει το βίντεο
    static function showVideo ()
    {
        $conn = new MyDB();
        $user = new User();

        $UserGroup=$user->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης

        if ($UserGroup==1) {  // Αν ο χρήστης είναι admin
            $disabled = false;
        } else {
            $disabled = true;
        }

        ?>

        <video id="myVideo" onerror="failed(event);" ondblclick="displayFullscreenControls();"></video>

        <?php OWMPElements::displayControls('mediaControls', false); ?>

        <div id="the_time_track">
            <span id="jsTrackTime">00:00</span> /
            <span id="jsTotalTrackTime">00:00</span>
        </div>

        <div id="o-trackTime">
            <input type=range class="o-trackTime__range" name="o-trackTime__range" min="0" max="100"
                   list="overlay_track_ticks" value="0" oninput="controlTrack();">
        </div>

        <?php OWMPElements::displayFullscreenOverlayElements(); //Display fullscreen overlay elements ?>

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

            <?php OWMPElements::displayTagsForm($disabled); ?>

            <?php
                if ($UserGroup==1)  {
            ?>
                    <input type="button" class="btn btn-dark w-100" name="submit" id="submit"
                        <?php echo ($disabled) ? ' disabled ' : ''; ?>
                           value="<?php echo __('tag_form_submit'); ?>" onclick="update_tags();">

            <?php
                }
            ?>

        </div>

        <script type="text/javascript">

            // περνάει στην javascript το ότι το video φορτώθηκε
            var VideoLoaded=true;

        </script>


        <?php

    }

    /**
     * Εμφανίζει το βασικό παράθυρο που εμφανίζεται η playlist
     *
     * @param $offset {int} Το τρέχον σημείο της λίστας
     * @param $step {int} Πόσες εγγραφές ανα σελίδα θα εμφανίσει
     */
    static function showPlaylistWindow ($offset, $step)
    {
        $conn = new MyDB();
        $user = new User();
        $OWMPElements = new OWMPElements();

        $UserGroup=$user->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης
        $userID=$user->getUserID($conn->getSession('username'));      // Επιστρέφει το id του user με username στο session


        // Display playlist choices bar
        ?>
        <div class="row w-100 px-3 no-gutters">



            <div class="row col-lg-4 col-sm-5 col-12 my-1 px-4 w-100 text-center">
                    <?php $OWMPElements->displayChooseMediaSelect(); // Εμφάνιση του media select ?>

                    <div class="col-2 my-auto h-100">
                        <span class="mdi mdi-magnify mdi-24px hasCursorPointer"  data-toggle="modal" data-target="#search"
                              id="searchClick" title="<?php echo __('search_text_search'); ?>" >
                        </span>
                    </div>
            </div>

            <div class="col-lg-4 col-sm-6 col-10 my-1 w-100">
                <?php $OWMPElements->displayChoosePlaylistElements($userID); // Εμφάνιση των στοιχείων επιλογής playlist ?>
            </div>

            <?php
            if ($UserGroup==1) {

                ?>
                <div class="col-2 col-lg-2 col-sm-1 my-1">

                    <div class="navbar navbar-light py-0 px-0">
                        <button class="navbar-toggler ml-auto" title="Tools" data-toggle="collapse" data-target="#navbarNavToolbar"
                                aria-controls="navbarNavToolbar" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon" id="tools-toggler"></span>
                        </button>

                    </div>
                </div>
                <?php

            }
            ?>

            <div class="col-lg-2 w-100 my-1 d-none d-lg-block text-right">
                <?php $OWMPElements->displayEditButtons($UserGroup); // Εμφάνιση των edit buttons ?>
            </div>


        </div>

        <?php
        $OWMPElements->displaySomeTools(); // Εμφάνιση διάφορων εργαλείων
        $OWMPElements->displayPlaylistContainer($offset,$step); // Εμφάνιση του playlist container
    }

    // Εμφανίζει την οθόνη του configuration
    static function showConfiguration ()
    {

        $conn = new MyDB();
        $user = new User();

        $UserGroup=$user->getUserGroup($conn->getSession('username'));  // Παίρνει το user group στο οποίο ανήκει ο χρήστης

        ?>
        <h2 class="c1"><?php echo __('nav_item_2'); ?></h2>


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

        <script type="text/javascript">

            var error1='<?php echo __('user_error1'); ?>';
            var error2='<?php echo __('user_error2'); ?>';

        </script>

        <?php

    }

    // εμφάνιση της οθόνης συγχρονισμού
    static function showSynchronization () {

        ?>

        <h2 class="c1"><?php echo __('nav_item_3'); ?></h2>

        <?php

        $OWMPElements = new OWMPElements();

        // Check folders
        $problematicPaths = $OWMPElements->checkFoldersPermissions();


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
                    // Αν είναι εγκατεστημένο το youtube-dl
                    if(Utilities::checkIfLinuxProgramInstalled('yt-dlp')) {
                        $OWMPElements->displayYoutubeDownloadElements(); // Εμφανίζει τις επιλογές για το youtube
                    } else {
                        // TODO να μπει δυναμικό κείμενο
                        echo 'yt-dlp not installed';
                    }
                ?>
            </details>

        <?php

            $OWMPElements->displayUploadFilesElement($problematicPaths);

        ?>

            <p>
                <li> <?php echo __('help_samba_sharing_title'); Page::getHelp('help_samba_sharing'); ?> </li>
                <li> <?php echo __('help_itunes_sync_title'); Page::getHelp('help_itunes_sync'); ?> </li>
                <li> <?php echo __('help_alac_title'); Page::getHelp('help_alac'); ?> </li>
            </p>

            <h4 class="c1"><?php echo __('requirements_check'); ?></h4>

            <?php
                // Έλεγχοι εφαρμογών και φακέλων
                $OWMPElements->checkRequirements();  // Εμφανίζει τους ελέγχους για τα requirements
                // Έλεγχος δικαιωμάτων φακέλων
                $OWMPElements->displayFoldersPermissions($problematicPaths);
            ?>



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
        <h2 class="c1"><?php echo __('nav_item_4'); ?></h2>
        <?php

        MyDB::createConnection();

        $sql = 'SELECT * FROM logs ORDER BY log_date DESC LIMIT 0,100';

        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute();

        ?>

        <table class="table table-striped">
          <thead>
            <tr>
                <th scope="col"><?php echo __('logs_id'); ?></th>
                <th scope="col"><?php echo __('logs_message'); ?></th>
                <th scope="col"><?php echo __('logs_ip'); ?></th>
                <th scope="col"><?php echo __('logs_user'); ?></th>
                <th scope="col"><?php echo __('logs_date'); ?></th>
                <th scope="col"><?php echo __('logs_browser'); ?></th>
            </tr>
          </thead>

          <tbody>

          <?php
          // Αν ο χρήστης username βρεθεί. Αν υπάρχει δηλαδή στην βάση μας
          while ($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
              ?>

              <tr>
                  <td scope="row"><?php echo $item['id']; ?></td>
                  <td scope="row"><?php echo $item['message']; ?></td>
                  <td scope="row"><?php echo $item['ip']; ?></td>
                  <td scope="row"><?php echo $item['user_name']; ?></td>
                  <td scope="row"><?php echo date('Y-m-d H:i:s', strtotime($item['log_date'])); ?></td>
                  <td scope="row"><?php echo $item['browser']; ?></td>
              </tr>

            <?php

          }


          ?>

          </tbody>
        </table>

        <?php

        $stmt->closeCursor();
        $stmt = null;

    }

    // Εμφάνιση της οθόνης βοήθειας
    static function showHelp ()
    {
        ?>
        <h2 class="c1"><?php echo __('nav_item_5'); ?></h2>

        <?php

        $lang = new Language();

        include ('../lang/'.$lang->lang_id().'.help.html');

    }


}
