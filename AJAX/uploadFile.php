<?php
/**
 *
 * File: uploadFile.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 06/04/17
 * Time: 00:46
 *
 * Σώζει ένα αρχείο που κάναμε upload
 *
 */

use apps4net\framework\Page;

require_once('../src/boot.php');

Page::checkValidAjaxRequest(true);







//$.ajax({
//        // Your server script to process the upload
//        url: AJAX_path + 'uploadFile.php',
//        type: 'POST',
//
//        // Form data
//        data: formData,
//
//        // Tell jQuery not to process data or worry about content-type
//        // You *must* include these options!
//        contentType: false,
//        processData: false,
//
//        // Custom XMLHttpRequest
//        xhr: function() {
//    var myXhr = $.ajaxSettings.xhr();
//    if (myXhr.upload) {
//        // For handling the progress of the upload
//        // myXhr.upload.addEventListener('progress', function(e) {
//        //     if (e.lengthComputable) {
//        //         $('progress').attr({
//        //             value: e.loaded,
//        //             max: e.total,
//        //         });
//        //     }
//        // } , false);
//    }
//    return myXhr;
//}
//    });
