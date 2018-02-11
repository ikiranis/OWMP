/**
 *
 * File: _youtube.js
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 26/09/17
 * Time: 23:00
 *
 * Youtube downloading
 *
 */

/**
 * Returns the html text for video downloading
 *
 * @param id
 * @returns {string|*}
 */
function getHTMLVideoDownloading(id)
{
    var htmlText = '<p> :: '+phrases['youtube_downloading']+
        ' <a href=https://www.youtube.com/watch?v=' + id + '>' +
        'https://www.youtube.com/watch?v=' + id + '</a></p>';

    return htmlText;
}

/**
 * Returns the html text for downloaded video
 *
 * @param imageThumbnail
 * @param result
 * @returns {string|*}
 */
function getHTMMLVideoDownloaded(imageThumbnail, result)
{
    var htmlText = '<img src="' + imageThumbnail + '" style="float:left;">' +
        '<p class="is_youTube-success">' + phrases['youtube_downloaded_to_path'] + ': ' + result + '</p>';

    return htmlText;
}

function getHTMLVideoFail(theUrl)
{
    var htmlText = '<p class="is_youTube-fail">' + phrases['youtube_problem'] + ': ' + theUrl + '</p>';

    return htmlText;
}

function getHTMLVideoError(error)
{
    var htmlText = '<p class="is_youTube-fail">' + phrases['youtube_problem'] + ': ' + ' Error: ' + error + '</p>';

    return htmlText;
}

/**
 * Καλεί AJAX request για να κατεβάσει το βίντεο από το youtube
 *
 * @param id
 * @param counter
 * @param total
 * @param mediaKind
 */
function callGetYouTube(id,counter,total, mediaKind) {
    var resultsContainerTextID = $(".o-resultsContainer_text");

    $.ajaxQueue({  // χρησιμοποιούμε το extension του jquery (αντί του $.ajax) για να εκτελεί το επόμενο AJAX μόλις τελειώσει το προηγούμενο
        url: AJAX_path + "framework/getYouTube",
        type: 'GET',
        async: true,
        data: {
            id: id,
            mediaKind: mediaKind
        },
        dataType: "json",
        beforeSend: function (xhr) {
            if(runningYoutubeDownload) {
                $('.o-resultsContainer_text').append(getHTMLVideoDownloading(id));

                progressPercent = parseInt(((counter + 1) / total) * 100);

                ProgressAnimation.setProgressPercent(progressPercent);

                // Έλεγχος αν είναι hidden. Τότε αρχίζει το blinking και πάλι. Αλλιώς όχι
                var resultsContainer = document.querySelector('#o-resultsContainer');

                if(resultsContainer.classList.contains('isHidden')) {
                    BlinkElement.start('.o-resultsContainer_iconContainer');
                }

            } else {
                xhr.abort();
            }

        },
        success: function (data) {
            if (data.success === true) {
                // TODO να το φτιάξω εμφανισιακά και με σωστό css
                resultsContainerTextID.append(getHTMMLVideoDownloaded(data.imageThumbnail, data.result));

                resultsContainerTextID.append(data.filesToDelete);

            } else {
                resultsContainerTextID.append(getHTMLVideoFail(data.theUrl));
            }
        },
        error: function (xhr, status, error) {
            resultsContainerTextID.append(getHTMLVideoError(error));
        }
    });
}

/**
 * Ελέγχει αν είναι video ή playlist και επιστρέφει τα id σε σχετικό πίνακα videoItems[]
 *
 * @param url
 * @param counter
 * @param total
 */
function checkVideoUrl(url,counter,total) {
    $.ajaxQueue({  // χρησιμοποιούμε το extension του jquery (αντί του $.ajax) για να εκτελεί το επόμενο AJAX μόλις τελειώσει το προηγούμενο
        url: AJAX_path + "framework/checkVideoURL",
        type: 'GET',
        async: true,
        data: {
            url: url
        },
        dataType: "json",
        success: function (data) {
            if (data.success === true) {
                if(data.videoKind === 'video') {
                    videoItems.push(data.videoID);
                } else {
                    var videoIDs = data.playlistItems;
                    for (var i = 0; i < videoIDs.length; i++) {
                        videoItems.push(videoIDs[i]);
                    }
                }

            } else {
                $(".o-resultsContainer_text").append('<p class="youtube_fail">'+phrases['youtube_problem']+': ' + data.theUrl + '</p>');
            }
        }
    });
}

/**
 * Κατεβάζει ένα ή περισσότερα βίντεο από το YouTube
 */
function downloadTheYouTube() {
    var urls = document.querySelector('#o-youTube__textArea').value;
    var mediaKind = document.querySelector('#jsMediaKind').value;

    var OKGo=false;

    if(mediaKind === 'Music Video') {
        var MusicVideoPathOK = document.querySelector('#jsMusicVideoPathOK').value;

        if(MusicVideoPathOK) {
            OKGo = true;
        } else {
            DisplayMessage('.alert_error', phrases['cant_write_to_path'], 'danger');
        }
    } else {
        var MusicPathOK = document.querySelector('#jsMusicPathOK').value;

        if(MusicPathOK) {
            OKGo = true;
        } else {
            DisplayMessage('.alert_error', phrases['cant_write_to_path'], 'danger');
        }
    }

    if(OKGo) {
        urls = urls.split(',');  // Παίρνουμε το string σε array

        clearResultsContainer();
        displayResultsIcon();
        ProgressAnimation.init(true);
        ProgressAnimation.setProgressPercent(0);
        displayKillCommandIcon();

        syncRunning = true;
        runningYoutubeDownload = true;

        videoItems = []; // καθαρίζει το array

        // έλεγχος των url και προσθήκη σε πίνακα με τα video ID
        for (var i = 0; i < urls.length; i++) {
            checkVideoUrl(urls[i], i, urls.length);
        }

        // αφου τελειώσουν οι έλεγχοι
        $(document).one("ajaxStop", function () {

            // κατέβασμα των video
            for (var i = 0; i < videoItems.length; i++) {
                // console.log(videoItems[i]);
                callGetYouTube(videoItems[i], i, videoItems.length, mediaKind);

            }

            // Μόλις εκτελεστούν όλα τα ajax κάνει το παρακάτω
            $(document).one("ajaxStop", function () {
                var syncInterval = setInterval(function () {
                    clearInterval(syncInterval);
                    ProgressAnimation.kill();
                    syncRunning = false;
                    hideKillCommandIcon();
                    runningYoutubeDownlod = false;
                    // startTheSync('sync');
                }, 6000);
                // return;
            });

        });
    }


}