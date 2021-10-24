<?php

/**
 * File: VideoDownload.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 26/01/17
 * Time: 22:16
 *
 * Κλάση για κατέβασμα βίντεο από youtube κ.α.
 *
 */

namespace apps4net\framework;

class VideoDownload
{

    public $videoURL;
    public $videoID;
    public $mediaKind;
    public $imageThumbnail='';
    public $title;
    public $maxVideoHeight;

    // TODO έχει πρόβλημα όταν το λινκ του youtube έχει τον χρονικό σημείο που πρέπει να παίξει
    // Επιστρέφει το id ενός youtube video από το url του
    // Source from http://code.runnable.com/VUpjz28i-V4jETgo/get-youtube-video-id-from-url-for-php
    public function getYoutubeID(){
        $video_id = false;
        $url = parse_url($this->videoURL);
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

    // Επιστρέφει το playlist ID από ένα youtube url
    public function getYoutubePlaylistID(){
        $url = parse_url($this->videoURL);
        parse_str($url['query'],$q);
        $playlistID = $q['list'];

        if($playlistID) {
            return $playlistID;
        } else {
            return false;
        }
    }

    // Ελέγχει αν είναι video ή playlist
    public function checkURLkind() {
        if($this->getYoutubeID()) {
            $result = 'video';
        } else {
            if($this->getYoutubePlaylistID()) {
                $result = 'playlist';
            } else {
                return false;
            }
        }

        return $result;
    }


    // Επιστρέφει την λίστα με τα items μιας playlist, σε array
    public function getYoutubePlaylistItems(){
        $playlistID=$this->getYoutubePlaylistID();

        $html = 'https://www.googleapis.com/youtube/v3/playlistItems?playlistId='.$playlistID.'&key='.YOUTUBE_API.'&part=snippet&maxResults=50';
        $response = file_get_contents($html);
        $decoded = json_decode($response, true);
        $playlistItems=array();
        foreach ($decoded['items'] as $items) {
            $videoID= $items['snippet']['resourceId']['videoId'];
            $playlistItems[] = $videoID;
        }

        return $playlistItems;
    }


    // Επιστρέφει τον τίτλο του βίντεο μέσω του Youtube API
    // Details @ https://developers.google.com/youtube/v3/getting-started
    public function getYoutubeTitle(){
        $html = 'https://www.googleapis.com/youtube/v3/videos?id='.$this->videoID.'&key='.YOUTUBE_API.'&part=snippet';
        $response = file_get_contents($html);
        $decoded = json_decode($response, true);
        foreach ($decoded['items'] as $items) {
            $this->imageThumbnail = $items['snippet']['thumbnails']['default']['url'];
            $uploadDate = substr($items['snippet']['publishedAt'],0,10);
            $title= $items['snippet']['title'].' ('.$uploadDate.')';
            return $title;
        }
    }

    // Κατεβάζει ένα βίντεο από το youtube. Σε audio ή video
    public function downloadYoutube($videoFullPath) {

        $youtubedlDefaultOptions = '--restrict-filenames --cache-dir '. OUTPUT_FOLDER;

        if($this->mediaKind=='Music Video') {
            // Κατέβασμα βίντεο
            $downloadString = '"bestvideo[ext=mp4][height<='.$this->maxVideoHeight.']+bestaudio[ext=m4a]/best[ext=mp4]/best" -o "'.$videoFullPath.'.%(ext)s" -- '.$this->videoID;
        } else {
            // Κατέβασμα audio
            $downloadString = '"bestaudio[ext=m4a]/best[ext=mp3]/best" -o "'.$videoFullPath.'.%(ext)s" -- '.$this->videoID;
        }

        // το όνομα του αρχείου που θα κατεβάσει με το full path
        $outputfilename = shell_exec('youtube-dl ' . $youtubedlDefaultOptions . ' --get-filename -f '.$downloadString);

        $ariaDownloadString = ' --external-downloader aria2c --external-downloader-args "-j 8 -s 8 -x 8 -k 5M"';

        error_log('youtube-dl ' . $youtubedlDefaultOptions . ' -f '.$downloadString . $ariaDownloadString);

        // κατεβάζει το βίντεο
        $result=shell_exec('youtube-dl ' . $youtubedlDefaultOptions . ' -f '.$downloadString . $ariaDownloadString);



        return $outputfilename;
    }

    /**
     * Κατεβάζει ένα βίντεο
     *
     * @return bool|mixed|string
     */
    public function downloadVideo() {
        Progress::setLastMomentAlive(false);

        $fileDir = Utilities::getPathFromYearAndMonth();

        if($this->mediaKind=='Music Video') {
            $uploadDir=VIDEO_FILE_UPLOAD . $fileDir;
        } else {
            $uploadDir=MUSIC_FILE_UPLOAD . $fileDir;
        }

        $checkUploadDir = FilesIO::createDirectory($uploadDir); // Αν δεν υπάρχει ο φάκελος τον δημιουργούμε
        if(!$checkUploadDir['result']) {  // Αν είναι false τερματίζουμε την εκτέλεση
            exit($checkUploadDir['message']);
        }

        // Παίρνει τον τίτλο του βίντεο και τον μετατρέπει σε greeklish αν χρειάζεται
        $this->title=$this->getYoutubeTitle();

        // καθαρίζει τον τίτλο και τον μετατρέπει σε greeklish
        $this->title=str_replace("/",'',$this->title);
        $this->title=Utilities::GrCyr2Latin(ClearString($this->title));

        // Μετατροπή του τίτλου σε μικρά και μετά το πρώτο γράμμα κάθε λέξης σε κεφαλαίο
        $this->title = ucwords(strtolower($this->title));

        $videoFullPath = $uploadDir.$this->title;

        // κατεβάζει το βίντεο
        $outputfilename = $this->downloadYoutube($videoFullPath);

//        trigger_error($result);

        // καθαρίζει το επιστρεφόμενο path
        $outputfilename=str_replace("\n",'',$outputfilename);

        Progress::setLastMomentAlive(true);  // To timestamp της συγκεκριμένης στιγμής

        // έλεγχος αν έχει κατέβει το βίντεο
        if(FilesIO::fileExists($outputfilename))
            return $outputfilename;
        else return false;
    }


}
