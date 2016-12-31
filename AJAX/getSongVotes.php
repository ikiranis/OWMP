<?php
/**
 * File: getSongVotes.php
 * Created by rocean
 * Date: 30/12/16
 * Time: 01:59
 *
 * Εμφανίζει τα votes
 *
 */


require_once ('../libraries/common.inc.php');

Page::checkValidAjaxRequest();

// Ο δισδιάστατος πίνακας με τις ψήφους. Στην 1η στήλη είναι το fileID, στην 2η ο αριθμός των ψήφων
$votesArray = OWMP::getVotes();
?>

    <ul>
        
        <?php
        foreach ($votesArray as $vote) {
            $songInfo = OWMP::getSongInfo($vote['file_id']);
        
            ?>
            
            <li>
                <span id="currentSongName"><?php echo $songInfo[0]['song_name']; ?></span>
                <span id="currentSongArtist"><?php echo $songInfo[0]['artist']; ?></span>
                <span id="numberOfVotes"><?php echo $vote['numberOfVotes']; ?></span>
            </li>
        
            <?php
        }

        ?>
        
    </ul>

