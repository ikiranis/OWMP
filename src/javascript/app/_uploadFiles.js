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
    finishedUploads: 0,                     // Πόσα uploads αρχείων έχουν ολοκληρωθεί
    filesUploadedCount:  0,                 // Σύνολο των αρχείων που έχουν επιλεχθεί για ανέβασμα
    percent_done: [],                       // Το ποσοστό που έχει ανέβει από κάθε αρχείο
    reader: [],                             // To fileReader object για κάθε αρχείο
    theFile: [],                            // Το κάθε αρχείο
    slice_size: 1000 * 1024,                // Το μέγεθος του slice
    filesInputElement: '#jsMediaFiles',     // Το input element που παίρνει τα αρχεία

    /**
     * Εκκίνηση του uploading
     *
     */
    startUpload: function (problematicPaths)
    {
        // To imput element που περιέχει τα επιλεγμένα αρχεία
        var files = document.querySelector(this.filesInputElement).files;

        // If there is no error with needed folders
        if(problematicPaths.coverAlbumsFolder === 0 && problematicPaths.musicDownloadPath === 0 && problematicPaths.musicVideoDownloadPath === 0) {
            clearResultsContainer();
            displayResultsIcon();
            ProgressAnimation.init(true);
            ProgressAnimation.setProgressPercent(0);

            this.finishedUploads = 0;
            this.filesUploadedCount = files.length;

            this.percent_done = [];
            this.reader = [];
            this.theFile = [];

            for (var i = 0; i < this.filesUploadedCount; i++) {
                this.reader.push(new FileReader());
                this.theFile.push(files[i]);

                this.uploadSliceOfFile(0, i);
            }
        } else {
            let errorString = '';

            // TODO dynamic texts
            if(problematicPaths.coverAlbumsFolder !== 0) {
                errorString+= 'Problem with Cover Albums Folder. ';
            }
            if(problematicPaths.musicDownloadPath !== 0) {
                errorString+= 'Problem with Music Download Folder. ';
            }
            if(problematicPaths.musicVideoDownloadPath !== 0) {
                errorString+= 'Problem with Music Video Download Folder. ';
            }

            DisplayMessage('.alert_error', errorString, 'danger');
        }

    },

    /**
     * Αρχίζει το ανέβασμα κομματιού του αρχείου
     *
     * @param start {int} Το σημείο που βρίσκεται το slice
     * @param i {int} Counter
     */
    uploadSliceOfFile: function (start, i)
    {
        var next_slice = start + this.slice_size + 1;
        var blob = this.theFile[i].slice(start, next_slice);

        this.reader[i].onloadend = function( event ) {
            if ( event.target.readyState !== FileReader.DONE ) {
                return;
            }

            $.ajaxQueue({
                url: AJAX_path + 'app/uploadMediaFile',
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
                    this.percent_done[i] =  parseInt(((size_done / this.theFile[i].size) * 100).toFixed(0)) ;

                    // Fix για τα mp3 που για κάποιο λόγο ανεβάζουν πάνω από το 100%
                    if(this.percent_done[i] > 100) {
                        this.percent_done[i] = 100;
                    }

                    if ( next_slice < this.theFile[i].size ) {
                        // Update upload progress
                        this.showFileUploadProgress();

                        // More to upload, call function recursively
                        this.uploadSliceOfFile(next_slice, i);
                    } else {
                        this.insertFileToDatabase(data);
                    }
                }.bind(this)
            });
        }.bind(this); // Περνάει το this για να μπορεί να το δει μέσα στο callback

        this.reader[i].readAsDataURL(blob);

    },

    /**
     * Εισαγωγή του τελικού αρχείου στην βάση
     *
     * @param data {object} Τα data που επέστρεψε το ajax call
     */
    insertFileToDatabase: function(data)
    {
        $.ajaxQueue({
            url: AJAX_path + 'app/uploadMediaFile',
            type: 'POST',
            cache: false,
            data: JSON.stringify({'fullPathFilename': data.fullPathFilename,
                'fileName': data.fileName,
                'file_type': data.fileType,
                'uploadKind': 'finalizedFile'}),
            dataType: 'json',
            success: function( data ) {
                this.finishedUploads++;
                var resultsContainerTextElem = $(".o-resultsContainer_text");

                if (data.success === true) {
                    resultsContainerTextElem.append('<div class="row text-success my-2 px-2">' +
                        phrases['youtube_downloaded_to_path'] + ': ' + '<span class="font-weight-bold">' +
                        data.result + '</span></div>');

                    resultsContainerTextElem.append(data.filesToDelete);

                    // Έλεγχος αν είναι hidden. Τότε αρχίζει το blinking και πάλι. Αλλιώς όχι
                    var resultsContainer = document.querySelector('#o-resultsContainer');

                    if(resultsContainer.classList.contains('isHidden')) {
                        BlinkElement.start('.o-resultsContainer_iconContainer');
                    }

                } else {
                    resultsContainerTextElem.append('<div class="row text-danger my-2 px-2">' + phrases['problem_with_file'] + ': ' +
                        '<span class="font-weight-bold">' + data.fileName + '</span></div>');
                }

                this.checkUploadTermination(); // Έλεγχος και τερματισμός της διαδικασίας του uploading

            }.bind(this),
            error: function( jqXHR, textStatus, errorThrown ) {
                console.log( jqXHR, textStatus, errorThrown );
            }
        });
    },

    /**
     * Εμφανίζει το ποσοστό uploading του τρέχοντος αρχείου σε σχέση και με το συνολικό ποσοστό όλων των αρχείων
     */
    showFileUploadProgress: function ()
    {
        var percentSummary = 0;
        for(var i=0; i<this.percent_done.length; i++) {
            percentSummary = percentSummary + this.percent_done[i];
        }
        // Το συνολικό ποσοστό όλων των αρχείων
        var totalPercent = parseInt(this.filesUploadedCount * 100);
        // Προστίθεται το τρέχον ποσοστό, στο συνολικό
        var theTotal = ((percentSummary / totalPercent) * 100).toFixed(0);

        ProgressAnimation.setProgressPercent(theTotal);
    },

    /**
     * Έλεγχος και τερματισμός της διαδικασίας του uploading
     */
    checkUploadTermination: function ()
    {
        if(this.finishedUploads === this.filesUploadedCount) {
            ProgressAnimation.kill();
            $(".o-resultsContainer_text").append('<div class="row text-info font-weight-bold my-2 px-2">' + phrases['files_added'] + ' ' + this.filesUploadedCount
                + ' ' + phrases['added_files'] + '</div>');
        }
    }

};


