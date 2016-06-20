<?php

/**
 * File: Language.php
 * Created by rocean
 * Date: 13/05/16
 * Time: 22:10
 * Class for multilanguage support
 * Info and source http://www.phpbuilder.com/columns/MultilingualPHPSite/index.php3
 * Flags https://github.com/googlei18n/region-flags Διαστάσεις 128x64
 */

class Language
{
    
    public static $expiration_date=60*30; // μισή ώρα
    public static $phrases = array();
    
    public function load_phrases($lang_id) {
        $xml = new DomDocument('1.0');
        
        
        //path to language directory
        $lang_path=('..'.LANG_PATH.$lang_id.'.xml');
        $xml->load($lang_path);

        //phrases are inside page tags, first we must get these
        $page = $xml->getElementsByTagName('page');
        $page_num=$page->length;


        for($i = 0; $i < $page_num; $i++) {
            $page=$xml->getElementsByTagName('page')->item($i);

            //get phase tags and store them into array
            foreach($page->getElementsByTagName('phrase') as $phrase) {
                $phase_name = $phrase->getAttribute('name');
                $phrases[$phase_name] = $phrase->firstChild->nodeValue;

                $phrases[$phase_name] = str_replace('\n','<br/>',$phrases[$phase_name]);
            }

        }

        $this->phrases=$phrases;
    }

    public function lang_id() {
        //determine page language
        $lang_id= isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'gr';

        //set the language cookie and update cookie expiration date
        if(!isset($_COOKIE['lang'])) {
            setcookie('lang',$lang_id,time()+self::$expiration_date);
        }

        return $lang_id;
    }

    public function change_lang($lang_id) {
        setcookie('lang',$lang_id,time()+self::$expiration_date);
        $this->load_phrases($lang_id);
    }

    public function __construct() {
        $this->load_phrases($this->lang_id());
    }

    // Επιστρέφει τις υπάρχουσες γλώσσες, με κλειδί το $name (language ή lang_id) ναι ενδιάμεσα βάζει το $string.
    // Αν είναι true το $flag εμφανίζει και σημαία. Αν είναι true το $show_texts εμφανίζει και το κείμενο
    public function print_languages($name, $string, $show_flag, $show_texts) {

        global $languages;  // παίρνει τα data από το array $languages που βρίσκεται στο common.inc.php
        
        $result='';

        foreach ($languages as $language) {
            $flag_icon = '..'.LANG_PATH.'flags/'.$language['lang_id'].'.png';
            if($show_flag) $flag_img=' <img src='.$flag_icon.' class="flags">'; else $flag_img=''; // προσθέτει img της σημαίας ή όχι

            if($show_texts) $insert_text=$language[$name]; else $insert_text=''; // προσθέτει το κείμενο ή όχι

            // Τελική εκτύπωση του $result προσθέτοντας και href για αλλαγή της γλώσσας
            $result = $result . '<a href=?ChangeLang=' . $language["lang_id"]. '>' . $insert_text . $flag_img .'</a>'. $string;
        }

//        $result=substr($result,0,-strlen($string)); // κόβει το τελευταίο $string

        $result=Page::cutLastString($result,$string); // κόβει το τελευταίο $string

        return $result;
    }
}

// shortcut του $lang->phrases[$text];    Using like: __('αυτό είναι ένα μήνυμα');
function __($text){
    global $lang;
    
    return $lang->phrases[$text];
    
}


