<?php

/**
 * File: Language.php
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 13/05/16
 * Time: 22:10
 * Class for multilanguage support
 * Info and source http://www.phpbuilder.com/columns/MultilingualPHPSite/index.php3
 * Flags https://github.com/googlei18n/region-flags Διαστάσεις 128x64
 */

namespace apps4net\framework;

class Language
{
    
    public static $expiration_date=60*30; // μισή ώρα
    public static $phrases = array();
    
    public function load_phrases($lang_id) {
        $xml = new \DomDocument('1.0');
        
        
        //path to language directory
        $lang_path=(LANG_PATH.$lang_id.'.xml');
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

        self::$phrases = $phrases;
    }
    
    // Επιστρέφει τις φράσεις σε table
    public function getPhrasesTable() {
        return self::$phrases;
    }

    public function lang_id() {
        //determine page language
        $lang_id= isset($_COOKIE['lang']) ? $_COOKIE['lang'] : DEFAULT_LANG;

        //set the language cookie and update cookie expiration date
        if(!isset($_COOKIE['lang'])) {
            setcookie('lang',$lang_id,time()+self::$expiration_date, PROJECT_PATH);
        }

        return $lang_id;
    }

    public function change_lang($lang_id) {
        setcookie('lang',$lang_id,time()+self::$expiration_date, PROJECT_PATH);
        $this->load_phrases($lang_id);
    }

    public function __construct() {
        $this->load_phrases($this->lang_id());
    }

    // Επιστρέφει τις υπάρχουσες γλώσσες, με κλειδί το $name (language ή lang_id) ναι ενδιάμεσα βάζει το $string.
    // Αν είναι true το $flag εμφανίζει και σημαία. Αν είναι true το $show_texts εμφανίζει και το κείμενο
    public function print_languages($name, $string, $show_flag, $show_texts) {

        global $languages;  // παίρνει τα data από το array $languages που βρίσκεται στο boot.php
        
        $result='';

        foreach ($languages as $language) {
            $flag_icon = HTTP_TEXT . LANG_PATH_HTTP . 'flags/' . $language['lang_id'] . '.png';

            if($show_flag) { // προσθέτει img της σημαίας ή όχι
                $flag_img=' <img src='.$flag_icon.' class="flags">';
            } else {
                $flag_img='';
            }

            if($show_texts) { // προσθέτει το κείμενο ή όχι
                $insert_text=$language[$name];
            } else {
                $insert_text='';
            }

            // Τελική εκτύπωση του $result προσθέτοντας και href για αλλαγή της γλώσσας
            $result = $result . '<a href=?ChangeLang=' . $language["lang_id"]. '>' . $insert_text . $flag_img .'</a>'. $string;
        }

        $result=Utilities::cutLastString($result,$string); // κόβει το τελευταίο $string

        return $result;
    }
}