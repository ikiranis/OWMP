<?php
/**
 *
 * File: ExternalAPI.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 29/04/17
 * Time: 03:00
 *
 * Χρήση διάφορων εξωτερικών API
 *
 */

namespace apps4net\framework;


class ExternalAPI
{
    // Επιστρέφει το λινκ με το artwork cover από το itunes API
    static function getItunesCover($searchText, $getSmall)
    {

        $html = 'https://itunes.apple.com/search?term='.urlencode($searchText);
//        trigger_error($html);
        $response = file_get_contents($html);
        $decoded = json_decode($response, true);

        if($decoded) {
            // Αν είναι mobile παίρνει την μικρή έκδοση.
            if(!$getSmall) {
                $coverSize = '1400x1400';
            } else {
                $coverSize = '250x250';
            }

            foreach ($decoded['results'] as $items) {
                $artwork = $items['artworkUrl100'];
                $artwork = str_replace('100x100', $coverSize, $artwork);
                return $artwork;
            }
        } else {
            return false;
        }
    }

    // Επιστρέφει το λινκ με το gif από το giphy API
    static function getGiphy($searchText)
    {

        $html = 'https://api.giphy.com/v1/gifs/search?q='.urlencode($searchText).'&api_key='.GIPHY_API;
//        trigger_error($html);
        $response = file_get_contents($html);
        $decoded = json_decode($response, true);

        if($decoded) {
            foreach ($decoded['data'] as $items) {
                $giphy = $items['images']['downsized_large']['url'];
                return $giphy;
            }
        } else {
            return false;
        }
    }

}