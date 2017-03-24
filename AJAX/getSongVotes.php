<?php
/**
 * File: getSongVotes.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 30/12/16
 * Time: 01:59
 *
 * Εμφανίζει τα votes
 *
 */

use apps4net\framework\Page;
use apps4net\parrot\app\OWMP;

require_once('../src/boot.php');

session_start();

Page::checkValidAjaxRequest(false);

// Ο δισδιάστατος πίνακας με τις ψήφους. Στην 1η στήλη είναι το fileID, στην 2η ο αριθμός των ψήφων
$votesArray = OWMP::getVotes();

?>

    <ul>
        
        <?php
        foreach ($votesArray as $vote) {
            $songInfo = OWMP::getSongInfo($vote['file_id']);
        
            ?>
            
            <li>
                <input type="button" class="vote_button playlist_button_img"
                       title="<?php echo __('vote_song'); ?>"
                       onclick="voteSong(<?php echo $vote['file_id']; ?>);">
                <span id="currentSongName"><?php echo $songInfo[0]['song_name']; ?></span>
                <span id="currentSongArtist"><?php echo $songInfo[0]['artist']; ?></span> ::
                <span id="numberOfVotes"><?php echo $vote['numberOfVotes']; ?></span>
            </li>
        
            <?php
        }

        ?>
        
    </ul>

