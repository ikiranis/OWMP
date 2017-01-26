<?php

/**
 * File: videoDownload.php
 * Created by rocean
 * Date: 26/01/17
 * Time: 22:16
 *
 * Κλάση για κατέβασμα βίντεο από youtube κ.α.
 *
 */

class videoDownload
{

    public $videoURL;
    public $mediaKind;

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

    // Επιστρέφει τον τίτλο του βίντεο μέσω του Youtube API
    // Details @ https://developers.google.com/youtube/v3/getting-started
    public function getYoutubeTitle(){
        $youtubeID=$this->getYoutubeID();

//        trigger_error($youtubeID);

        $html = 'https://www.googleapis.com/youtube/v3/videos?id='.$youtubeID.'&key='.YOUTUBE_API.'&part=snippet';
        $response = file_get_contents($html);
        $decoded = json_decode($response, true);
        foreach ($decoded['items'] as $items) {
            $uploadDate = substr($items['snippet']['publishedAt'],0,10);
            $title= $items['snippet']['title'].' ('.$uploadDate.')';
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

    // Κατεβάζει ένα βίντεο από το youtube. Σε audio ή video
    public function downloadYoutube($videoFullPath) {

        if($this->mediaKind=='Music Video') {
            // Κατέβασμα βίντεο
            $downloadString = '"bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best" -o "'.$videoFullPath.'.%(ext)s" '.$this->videoURL;
        } else {
            // Κατέβασμα audio
            $downloadString = '"bestaudio[ext=m4a]/best[ext=mp3]/best" -o "'.$videoFullPath.'.%(ext)s" '.$this->videoURL;
        }

        // το όνομα του αρχείου που θα κατεβάσει με το full path
        $outputfilename = shell_exec('youtube-dl --get-filename -f '.$downloadString);

        // κατεβάζει το βίντεο
        $result=shell_exec('youtube-dl -f '.$downloadString);

        return $outputfilename;
    }


    // TODO να μπορεί να παίρνει το url μιας youtube playlist και να το σπάει
    // Κατεβάζει ένα βίντεο
    public function downloadVideo() {
        Page::setLastMomentAlive(false);

        $myYear = date('Y');
        $myMonth = date('m');
        $fileDir = $myYear . '/' . $myMonth . '/';  // O φάκελος που θα γραφτεί το αρχείο

        if($this->mediaKind=='Music Video') {
            $uploadDir=VIDEO_FILE_UPLOAD . $fileDir;
        } else {
            $uploadDir=MUSIC_FILE_UPLOAD . $fileDir;
        }

        OWMP::createDirectory($uploadDir); // Αν δεν υπάρχει ο φάκελος τον δημιουργούμε

        // Παίρνει τον τίτλο του βίντεο και τον μετατρέπει σε greeklish αν χρειάζεται
        $title=$this->getYoutubeTitle();

        // καθαρίζει τον τίτλο και τον μετατρέπει σε greeklish
        $title=str_replace("/",'',$title);
        $title=self::GrCyr2Latin(ClearString($title));

        $videoFullPath = $uploadDir.$title;

        // κατεβάζει το βίντεο
        $outputfilename = $this->downloadYoutube($videoFullPath);

//        trigger_error($result);

        // καθαρίζει το επιστρεφόμενο path
        $outputfilename=str_replace("\n",'',$outputfilename);

        Page::setLastMomentAlive(true);  // To timestamp της συγκεκριμένης στιγμής

        // έλεγχος αν έχει κατέβει το βίντεο
        if(OWMP::fileExists($outputfilename))
            return $outputfilename;
        else return false;
    }


}