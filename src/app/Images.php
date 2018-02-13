<?php
/**
 *
 * File: Images.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 12/02/2018
 * Time: 23:21
 *
 * Images manipulation class
 *
 */

namespace apps4net\parrot\app;

use apps4net\framework\MyDB;
use apps4net\framework\FilesIO;
use apps4net\framework\ScanDir;
use apps4net\framework\Utilities;

class Images
{
    protected $oldImageWidth;
    protected $oldImageHeight;
    protected $newWidth;
    protected $newHeight;

    /**
     * Upload ενός image κι εισαγωγή στην βάση
     *
     * @param $image {string} Η εικόνα σε string
     * @param $mime {string} Ο τύπος της εικόνας
     * @return bool|mixed Επιστρέφει το id του cover ή false
     */
    public function uploadAlbumImage($image, $mime)
    {
        $conn = new MyDB();

        $hash = SyncFiles::hashString($image); // Δημιουργούμε hash της εικόνας

        if (!$coverArtID = SyncFiles::searchForImageHash($hash)) {  // Ψάχνουμε αν το hash της εικόνας υπάρχει ήδη

            // εγγραφή του image σαν αρχείο σε υποκατάλογο έτους και μήνα
            switch ($mime) {  // το  extension του αρχείου αναλόγως το mime
                case 'image/png':
                    $imageExtension = '.png';
                    break;
                case 'image/jpeg':
                    $imageExtension = '.jpeg';
                    break;
                case 'image/jpg':
                    $imageExtension = '.jpg';
                    break;
                case 'image/gif':
                    $imageExtension = '.gif';
                    break;
            }

            $myYear = date('Y');
            $myMonth = date('m');
            $imageDir = $myYear . '/' . $myMonth . '/';  // O φάκελος που θα γραφτεί το αρχείο
            $timestampFilename = date('YmdHis'); // Το όνομα του αρχείου

            // TODO Check path permissions before starting the files upload
            $checkAlbumCoversDir = FilesIO::createDirectory(ALBUM_COVERS_DIR . $imageDir); // Αν δεν υπάρχει ο φάκελος τον δημιουργούμε
            if (!$checkAlbumCoversDir['result']) {  // Αν είναι false τερματίζουμε την εκτέλεση
                trigger_error($checkAlbumCoversDir['message']);
                exit($checkAlbumCoversDir['message']);
            }

            $file = ALBUM_COVERS_DIR . $imageDir . $timestampFilename . $imageExtension;  // Το πλήρες path που θα γραφτεί το αρχείο

            $success = file_put_contents($file, $image);  // Κάνει την τελική εγγραφή του image σε αρχείο

            // TODO Don't write to database (music_tags) if we already do that
            if ($success) {  // Αν το αρχείο δημιουργηθεί κανονικά κάνουμε εγγραφή στην βάση

                $sql = 'INSERT INTO album_arts (path, filename, hash) VALUES(?,?,?)';   // Εισάγει στον πίνακα album_arts

                $artsArray = array($imageDir, $timestampFilename . $imageExtension, $hash);

                $coverID = $conn->insertInto($sql, $artsArray); // Παίρνουμε το id της εγγραφής που έγινε

                // GD install http://php.net/manual/en/image.installation.php

                // Αν είναι εγκατεστημένη η GD library στην PHP και αν το image είναι valid
                if (function_exists('gd_info') && $this->checkValidImage($file)) {
                    // Δημιουργεί thumbnail, small image και ico
                    $this->createSmallerImage($file, 'thumb');
                    $this->createSmallerImage($file, 'small');
                } else {
                    trigger_error('error');
                    exit('error');
                }
            }

        } else {
            $coverID = $coverArtID;
        }

        return $coverID;
    }

    /**
     * Ελέγχει αν ένα image είναι valid
     *
     * @param $myImage {string} To path της εικόνας
     * @return bool True or false
     */
    public function checkValidImage($myImage)
    {
        $html = VALID_IMAGE_SCRIPT_ADDRESS . '?imagePath=' . $myImage;
        $response = @file_get_contents($html, FILE_USE_INCLUDE_PATH);
//        $response = self::get_content($html);

        $decoded = json_decode($response, true);

        if ($decoded) {
            foreach ($decoded as $items) {
                $result = $items;
                return $result;
            }
        }

        return false;
    }

    /**
     * Αναλόγως το extension επιστρέφει την εικόνα στο $image
     *
     * @param $myImage {string} Το path της εικόνας
     * @return bool|resource Επιστρέφει την εικόνα σαν string ή false
     */
    public function openImage($myImage)
    {
        $extension = pathinfo($myImage, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = @imagecreatefromjpeg($myImage);
                break;
            case 'gif':
                $image = @imagecreatefromgif($myImage);
                break;
            case 'png':
                $image = @imagecreatefrompng($myImage);
                break;
            default:
                return false;
                break;
        }

        if (!$image) {
            trigger_error('ERROR!');
            return false;
        }

        return $image;
    }

    /**
     * Calculate new image dimmensions
     *
     * @param $maxDimmension
     */
    public function getNewImagesDimmensions($maxDimmension)
    {
        if($this->oldImageWidth==$this->oldImageHeight) { // When dimmensions are equeal
            $this->newWidth = $maxDimmension;
            $this->newHeight = $maxDimmension;
        } else {
            if ($this->oldImageWidth > $this->oldImageHeight) {  // When width is bigger
                $this->newWidth = $maxDimmension;
                $this->newHeight = ($this->oldImageHeight * $maxDimmension) / $this->oldImageWidth;
            } else {  // When height is bigger
                $this->newHeight = $maxDimmension;
                $this->newWidth = ($this->oldImageWidth * $maxDimmension) / $this->oldImageHeight;
            }
        }
    }

    /**
     * Δημιουργεί μικρότερες εκδόσεις μίας εικόνας. Thumb, small, large.
     *
     * @param $fullpath {string} Το path της εικόνας
     * @param $imageSize {string} "thumb" or "small"
     * @return bool True or False για την επιτυχία
     */
    public function createSmallerImage($fullpath, $imageSize)
    {
        $imageFilename = pathinfo($fullpath, PATHINFO_BASENAME);  // Το όνομα του αρχείου
        $imagePath = pathinfo($fullpath, PATHINFO_DIRNAME);   // Το path του αρχείου μέσα στο ALBUM_COVERS_DIR
//        $extension = pathinfo($fullpath, PATHINFO_EXTENSION);

        // Aνοίγει το image (αν υπάρχει) και το βάζει στο $image
        if (FilesIO::fileExists($fullpath)) {
            if (!$image = $this->openImage($fullpath)) {
                return false;
            }
        } else {
            return false;
        }

        // Οι διαστάσεις του αρχικού image
        $this->oldImageWidth = imagesx($image);
        $this->oldImageHeight = imagesy($image);

        // Οι νέες διαστάσεις αναλόγως τι έχουμε επιλέξει να κάνει
        switch ($imageSize) {
            case 'thumb':
                $this->getNewImagesDimmensions(50);
                $newFilename = 'thumb_' . $imageFilename;
                break;
            case 'small':
                $this->getNewImagesDimmensions(250);
                $newFilename = 'small_' . $imageFilename;
                break;
        }

        // Δημιουργεί το image με νέες διαστάσεις
        $newImage = ImageCreateTrueColor($this->newWidth, $this->newHeight);
        imagecopyResampled($newImage, $image, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $this->oldImageWidth, $this->oldImageHeight);

        // Σώζει το image
//        if($imageSize!=='ico') {
        if (imagejpeg($newImage, $imagePath . '/' . $newFilename)) {
            $result = true;
        } else {
            $result = false;
        }

        imagedestroy($image); //  clean up image storage
        imagedestroy($newImage);

        return $result;

    }

    /**
     * Επιστρέφει το fullpath του album cover για το $id
     *
     * @param $id {int} Το id του cover image
     * @param $imageSize {string} "thumb" or "small" or "big"
     * @return bool|string To fullpath ή false στην αποτυχία
     */
    public function getAlbumImagePath($id, $imageSize)
    {
        MyDB::createConnection();

        $sql = 'SELECT path, filename FROM album_arts WHERE id=?';

        $stmt = MyDB::$conn->prepare($sql);

        $stmt->execute(array($id));

        if ($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {

            $bigImage = ALBUM_COVERS_DIR . $item['path'] . $item['filename'];

//            $extension = pathinfo($bigImage, PATHINFO_EXTENSION);

            if (FilesIO::fileExists($bigImage)) {
                $result = $bigImage;
            }

            if (function_exists('gd_info')) {
                $smallImage = ALBUM_COVERS_DIR . $item['path'] . 'small_' . $item['filename'];
                $thumbImage = ALBUM_COVERS_DIR . $item['path'] . 'thumb_' . $item['filename'];

                if (FilesIO::fileExists($smallImage)) {
                    $smallExist = true;
                } else {
                    $smallExist = false;
                }

                if (FilesIO::fileExists($thumbImage)) {
                    $thumbExist = true;
                } else {
                    $thumbExist = false;
                }

                switch ($imageSize) {
                    case 'small':
                        if ($smallExist) {
                            $result = $smallImage;
                        }
                        break;
                    case 'thumb':
                        if ($thumbExist) {
                            $result = $thumbImage;
                        }
                        break;
                }

//                if($imageSize=='big' && $_SESSION['mobile'] && $smallExist) {
//                    $result = $smallImage;
//                }
            } else {
                if ($imageSize == 'ico') {
                    $result = false;
                }
            }

        } else {
            $result = false;
        }

        $stmt->closeCursor();
        $stmt = null;

        return $result;
    }

    /**
     * Delete cover image file with the reference in album_arts table
     *
     * @param $id
     * @return bool
     */
    public function deleteImage($id)
    {
        $conn = new MyDB();

        $file = MyDB::getTableArray('album_arts', '*', 'id=?', array($id), null, null, null);   // Παίρνει το συγκεκριμένο αρχείο

        $filesArray = array('path' => $file[0]['path'],
            'filename' => $file[0]['filename']);

        $fullPath = ALBUM_COVERS_DIR . $filesArray['path'] . $filesArray['filename'];   // Το full path του αρχείου

        if (file_exists($fullPath)) {  // αν υπάρχει το αρχείο, σβήνει το αρχείο μαζί με την εγγραφή στην βάση
            if (unlink($fullPath)) {
                if ($conn->deleteRowFromTable('album_arts', 'id', $id)) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {  // Αν δεν υπάρχει το αρχείο σβήνει μόνο την εγγραφή στην βάση
                if ($conn->deleteRowFromTable('album_arts', 'id', $id)) {
                    return true;
                } else {
                    return false;
                }
        }

        return false;
    }

    /**
     * Delete files from $myArray
     *
     * @param $myArray
     */
    public function deleteFilesOfArray($myArray) {
        if (is_array($myArray)) {
            foreach ($myArray as $item) {
                if (file_exists($item)) {
                    unlink($item);
                }
            }
        }
    }

    /**
     * Delete all extra version of cover images
     */
    public function resetCoverImages()
    {
        $extensions = array('jpg', 'png', 'jpeg', 'gif', 'ico'); // image extensions
        $needles = array('small_', 'thumb_', '.ico');  // Strings to search in the array

        // παίρνει το σύνολο των αρχείων με $extensions από τον φάκελο ALBUM_COVERS_DIR
        $images = ScanDir::scan(ALBUM_COVERS_DIR, $extensions, true);
        $images = array_unique($images);

        // Delete files which contains $needles
        foreach ($needles as $needle) {
            $this->deleteFilesOfArray(Utilities::getFilteredArray($needle, $images));
        }

    }

    /**
     * Clean table album_arts from undefined rows in music_tags
     */
    public function cleanUndefinedALbumArts()
    {
        $conn = new MyDB();

        $condition = ' id NOT IN (SELECT album_artwork_ID FROM music_tags)';

        $rows = MyDB::getTableArray('album_arts', 'id', $condition, null, null, null, null);   // Παίρνει το συγκεκριμένο αρχείο

        // TODO Delete rows with mysql query
        foreach ($rows as $row) {
            $conn->deleteRowFromTable('album_arts', 'id', $row['id']);
        }

    }

}