<?php
/**
 *
 * File: FileUpload.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 08/09/2017
 * Time: 00:36
 *
 * Uploading αρχείων
 *
 */

namespace apps4net\framework;

class FileUpload
{
    public $fileData, $fileType, $uploadedFilename, $uploadDir;

    /**
     * FileUpload constructor.
     *
     * @param $file_data
     * @param $fileType
     * @param $uploadedFilename
     */
    public function __construct($file_data, $fileType, $uploadedFilename)
    {
        $this->fileData     = $file_data;
        $this->fileType = $fileType;
        $this->uploadedFilename = $uploadedFilename;

        // Παράγει το file path από το έτος και τον μήνα και ελέγχει το είδος του αρχείου
        if (strpos(strtolower($this->fileType), 'video')!==false) {
//            $syncFile->mediaKind = 'Music Video';
            $this->uploadDir = VIDEO_FILE_UPLOAD . Utilities::getPathFromYearAndMonth(). $this->uploadedFilename;
        } else {
//            $syncFile->mediaKind = 'Music';
            $this->uploadDir = MUSIC_FILE_UPLOAD . Utilities::getPathFromYearAndMonth() . $this->uploadedFilename;
        }
    }

    /**
     *
     */
    public function ajaxUploadFile()
    {

        $this->fileData = $this->decodeChunk( $this->fileData );

        // Παράγει το file path από το έτος και τον μήνα και ελέγχει το είδος του αρχείου
        if (strpos(strtolower($this->fileType), 'video')!==false) {
//            $syncFile->mediaKind = 'Music Video';
            $uploadDir = VIDEO_FILE_UPLOAD . Utilities::getPathFromYearAndMonth();
        } else {
//            $syncFile->mediaKind = 'Music';
            $uploadDir = MUSIC_FILE_UPLOAD . Utilities::getPathFromYearAndMonth();
        }

        if ( false === $this->fileData ) {
            $jsonArray=array( 'success'=> false);
            echo json_encode($jsonArray);
        }

        file_put_contents( $uploadDir.$this->uploadedFilename, $this->fileData, FILE_APPEND );

        $jsonArray = array('success' => true, 'fileName' => $this->uploadedFilename,
            'fullPathFilename' => $uploadDir.$this->uploadedFilename, 'fileType' => $this->fileType);
        echo json_encode($jsonArray);
    }

    /**
     * @param $data
     * @return array|bool|string
     */
    public function decodeChunk( $data )
    {
        $data = explode( ';base64,', $data );

        if ( ! is_array( $data ) || ! isset( $data[1] ) ) {
            return false;
        }

        $data = base64_decode( $data[1] );
        if ( ! $data ) {
            return false;
        }

        return $data;
    }

}