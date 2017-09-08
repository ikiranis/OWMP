/**
 *
 * File: _uploadFiles.js
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 08/09/17
 * Time: 16:50
 *
 * Uploading Files
 *
 * @reference https://deliciousbrains.com/using-javascript-file-api-to-avoid-file-upload-limits/
 *
 */

// Uploading Files
var UploadFiles =
{
    finishedUploads: 0,             // Πόσα uploads αρχείων έχουν ολοκληρωθεί
    filesUploadedCount:  0,         // Σύνολο των αρχείων που ανέβηκαν
    percent_done: [],               // Το ποσοστό που έχει ανέβει από κάθε αρχείο
    reader: [],                     // To fileReader object για κάθε αρχείο
    theFile: [],                    // Το κάθε αρχείο
    slice_size: 1000 * 1024,        // Το μέγεθος του slice

    /**
     * Εκκίνηση του uploading
     *
     */
    startUpload: function () {
        // To imput element που περιέχει τα επιλεγμένα αρχεία
        var files = document.querySelector('#jsMediaFiles').files;

        clearResultsContainer();
        displayResultsIcon();
        ProgressAnimation.init(true);
        ProgressAnimation.setProgressPercent(0);

        this.finishedUploads = 0;
        this.filesUploadedCount = files.length;

        this.percent_done = [];
        this.reader = [];
        this.theFile = [];

        for(var i=0; i<this.filesUploadedCount; i++) {
            this.reader.push(new FileReader());
            this.theFile.push(files[i]);

            this.uploadTheFile( 0, i );
        }

    },

    /**
     * Αρχίζει το ανέβασμα του αρχείου
     *
     * @param start {int} Το σημείο που βρίσκεται το slice
     * @param i {int} Counter
     */
    uploadTheFile: function (start, i)
    {
        var next_slice = start + this.slice_size + 1;
        var blob = this.theFile[i].slice( start, next_slice );

        this.reader[i].onloadend = function( event ) {
            if ( event.target.readyState !== FileReader.DONE ) {
                return;
            }

            $.ajax({
                url: AJAX_path + 'app/uploadMediaFile.php',
                type: 'POST',
                cache: false,
                data: JSON.stringify({'file': this.theFile[i].name,
                    'file_type': this.theFile[i].type,
                    'uploadKind': 'slice',
                    'file_data': event.target.result}),
                dataType: "json",
                error: function( jqXHR, textStatus, errorThrown ) {
                    console.log( jqXHR, textStatus, errorThrown );
                },
                success: function( data ) {
                    var size_done = start + this.slice_size;
                    this.percent_done[i] = Math.floor( ( size_done / this.theFile[i].size ) * 100 );

                    if ( next_slice < this.theFile[i].size ) {
                        // Update upload progress
                        this.showFileUploadProgress();

                        // More to upload, call function recursively
                        this.uploadTheFile( next_slice, i );
                    } else {
                        $.ajax({
                            url: AJAX_path + 'app/uploadMediaFile.php',
                            type: 'POST',
                            cache: false,
                            data: JSON.stringify({'fullPathFilename': data.fullPathFilename,
                                'fileName': data.fileName,
                                'file_type': data.fileType,
                                'uploadKind': 'finalizedFile'}),
                            dataType: 'json',
                            success: function( data ) {
                                this.finishedUploads++;

                                if(this.finishedUploads===this.filesUploadedCount) {
                                    ProgressAnimation.kill();
                                }

                                if (data.success === true) {
                                    $(".o-resultsContainer_text").append('<p class="is_youTube-success">'+
                                        phrases['youtube_downloaded_to_path']+': ' + data.result + '</p>');

                                    $(".o-resultsContainer_text").append(data.filesToDelete);

                                    // Έλεγχος αν είναι hidden. Τότε αρχίζει το blinking και πάλι. Αλλιώς όχι
                                    var resultsContainer = document.querySelector('.o-resultsContainer');

                                    if(resultsContainer.classList.contains('isHidden')) {
                                        BlinkElement.start('.o-resultsContainer_iconContainer');
                                    }

                                } else {
                                    console.log('upload problem');
                                }
                            }.bind(this)
                        });
                    }
                }.bind(this)
            });
        }.bind(this) // Περνάει το this για να μπορεί να το δει μέσα στο callback

        this.reader[i].readAsDataURL( blob );

    },

    /**
     * Εμφανίζει το ποσοστό uploading του τρέχοντος αρχείου σε σχέση και με το συνολικό ποσοστό όλων των αρχείων
     *
     * @param evt {object} Το progress event του uploading
     */
    showFileUploadProgress: function () {
        var percentSummary = 0;
        for(var i=0; i<this.percent_done.length; i++) {
            percentSummary = percentSummary+this.percent_done[i];
        }
        // Το συνολικό ποσοστό όλων των αρχείων
        var totalPercent = parseInt(this.filesUploadedCount * 100);
        // Προστίθεται το τρέχον ποσοστό, στο συνολικό
        var theTotal = ( (percentSummary / totalPercent) * 100).toFixed(0);

        ProgressAnimation.setProgressPercent(theTotal);
    }

}


