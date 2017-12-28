/**
 *
 * File: _video.js
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 26/09/17
 * Time: 22:46
 *
 * Video element management
 *
 */

/**
 * βάζει/βγάζει το video σε fullscreen
 */
function toggleFullscreen() {
    var elem = myVideo;
    if (!checkFullscreen()) { // μπαίνει σε full screen
        $(elem).addClass('full_screen_video');
        FullscreenON = true;
        showFullScreenVideoTags();
    } else {  // βγαίνει από full screen
        $(elem).removeClass('full_screen_video');
        FullscreenON = false;
        $('#overlay_media_controls').hide();
        showFullScreenVideoTags();
    }
}

// βάζει/βγάζει το video σε fullscreen
// Ο safari δεν υποστηρίζει keyboard shortcuts όταν είναι σε fullscreen για λόγους ασφαλείας
// Ο firefox δεν εμφανίζει το overlay σε fullscreen
function OldtoggleFullscreen() {
    elem = myVideo;
    if (!document.fullscreenElement && !document.mozFullScreenElement &&
        !document.webkitFullscreenElement && !document.msFullscreenElement) {
        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.msRequestFullscreen) {
            elem.msRequestFullscreen();
        } else if (elem.mozRequestFullScreen) {
            elem.mozRequestFullScreen();
        } else if (elem.webkitRequestFullscreen) {
            // elem.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);

            $(elem).addClass('fullscreenvideo');

            window.addEventListener("load", function() { window. scrollTo(0, 0); });
            // getShortcuts(elem);
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        }
    }
}

/**
 * Ελέγχει αν βρίσκεται σε fullscreen
 *
 * @returns {boolean}
 */
function checkFullscreen () {
    if (FullscreenON) {
        return true;
    } else {
        return false;
    }
}

// Ελέγχει αν βρίσκεται σε fullscreen
// Δεν χρησιμοποιείται
function oldcheckFullscreen () {
    if (document.fullscreenElement || document.mozFullScreenElement ||
        document.webkitFullscreenElement || document.msFullscreenElement) {
        return true;
    }
    else return false;
}

/**
 * Convert an audio file to lower bitrate
 *
 * @param id
 */
function convertAudioToLowerBitrate(id)
{
    console.log('Start converting...');

    $.ajax({
        url: AJAX_path + "app/convertAudioToLowerBitRate",
        type: 'GET',
        async: true,
        data: {
            id: id,
            tabID: tabID
        },
        dataType: "json",
        before: function() {
            audioConvertionRunning = true;
        },
        success: function (data) {
            if(data.success === true) {
                // console.log('Converted... ' + data.fullPath);
                // console.log('Elapsed time... ' + data.time);
                // console.log(data.result);

                pathToTempAudioFile = data.tempFile;
                audioConvertionRunning = false;
            } else {
                console.log('Error on converting... ' + data.errorCode);
            }
        }
    });
}

/**
 * Παίρνει το επόμενο τραγούδι και αρχίζει την αναπαραγωγή
 *
 * @param id
 * @param operation
 * @param preload
 */
function getNextVideoID(id, operation, preload)
{
    var theCurrentPlaylistID;

    // TODO possible problem with this when you change manual playlists
    if(operation === 'next') {
        theCurrentPlaylistID = currentPlaylistID;
    }
    if(operation === 'prev') {
        theCurrentPlaylistID = currentQueuePlaylistID;
    }

    $.ajax({  // χρησιμοποιούμε το extension του jquery (αντί του $.ajax) για να εκτελεί το επόμενο AJAX μόλις τελειώσει το προηγούμενο
        url: AJAX_path + "app/getNextVideo",
        type: 'GET',
        async: true,
        data: {
            playMode: localStorage.PlayMode,
            currentPlaylistID: theCurrentPlaylistID,
            tabID: tabID,
            operation: operation
        },
        dataType: "json",
        success: function (data) {
            if (data.success === true) {
                if(preload === false) { // Get current song ids and play the song
                    currentID = data.file_id;

                    if(data.operation === 'next') {
                        currentPlaylistID = data.playlist_id;
                        currentQueuePlaylistID = 0;
                    }
                    if(data.operation === 'prev') {
                        currentQueuePlaylistID = data.playlist_id;
                    }

                    loadNextVideo(id);

                } else { // Get next song ids without playing the song
                    nextPreloadedID = data.file_id;

                    // If song is audio then convert to lower bitrate
                    if(data.songKind === 'Music') {
                        convertAudioToLowerBitrate(nextPreloadedID);
                    }
                }

            }
        }
    });
}

/**
 * TODO όταν παίζει τραγούδια σε continue, αν παίξει κάποιο loved, τότε δεν συνεχίζει μετά από το τραγούδι που σταμάτησε
 *
 * Set the src of the video to the next URL in the playlist
 * If at the end we start again from beginning (the modulo
 * source.length does that)
 *
 * @param id
 */
function loadNextVideo(id)
{
    if(id !== 0) {
        currentID = id;
    }

    TimeUpdated = false;

    var onlyGiphy = 'false';
    if(localStorage.AllwaysGiphy === 'true') { // Αν θέλουμε μόνο από Giphy
        onlyGiphy = 'true';
    }

    // τραβάει τα metadata του αρχείου
    $.ajax({
        url: AJAX_path+"app/getVideoMetadata",
        type: 'GET',
        data: {
            id: currentID,
            tabID: tabID,
            onlyGiphy: onlyGiphy
        },
        dataType: "json",
        success: function (data) {
            var filename = data.file.filename; // σκέτο το filename

            var thePath = data.file.path;
            thePath = thePath.replace(WebFolderPath,'');
            var file_path = DIR_PREFIX + thePath + encodeURIComponent(data.file.filename);    // Το filename μαζί με όλο το path

            // console.log('Current ID: ' + currentID);

            // myVideo.src = file_path;
            if(pathToTempAudioFile === null) {
                myVideo.src = AJAX_path + "app/serveFile?id=" + currentID;
            } else {
                myVideo.src = AJAX_path + "app/serveFile?path=" + pathToTempAudioFile;
                pathToTempAudioFile = null;
                nextPreloadedID = 0;
            }
            // myVideo.controls=false;
            // console.log('Playing now... ' + myVideo.src);

            myVideo.load();
            myVideo.currentTime = 0;

            // Αν δεν είναι το πρώτο τραγούδι που παίζει τότε αρχίζει την αναπαραγωγή του τραγουδιού
            if (PlayTime > 0) {
                myVideo.play();
                displayPauseButton();
            } else { // αλλιώς κάνει pause
                myVideo.pause();
                displayPlayButton();
            }

            if (data.tags.success === true) { // τυπώνει τα data που τραβάει


                if(data.file.kind === 'Music') {  // Αν είναι Music τότε παίρνει το album cover και το εμφανίζει

                    var albumCoverPath = data.tags.albumCoverPath;
                    // var iconImagePath = data.tags.iconImagePath;

                    // Εμφάνιση του source στο fullscreen overlay
                    document.querySelector('#overlay_poster_source').innerHTML = data.tags.apiSource;

                    // Αν υπάρχει icon το εμφανίζει σαν favicon
                    // if(iconImagePath) {
                    //     document.querySelector("#theFavIcon").href = AJAX_path+'app/serveImage.php?imagePath=' + albumCoverPath;
                    // }

                    document.querySelector("#theFavIcon").href = AJAX_path+'app/serveImage?imagePath=' + albumCoverPath;

                    // Εμφάνιση του cover
                    if(localStorage.AllwaysGiphy === 'true'){  // Αν θέλουμε μόνο από Giphy
                        if(data.tags.fromAPI) { // αν έχει βρει κάτι στο API
                            myVideo.poster = data.tags.fromAPI;
                        } else { // Αν όχι εμφανίζει το album cover
                            myVideo.poster = AJAX_path + 'app/serveImage?imagePath=' + albumCoverPath;
                        }
                    } else {   // όταν δεν θέλουμε μόνο από giphy
                        // Αν δεν υπάρχει album cover το παίρνουμε από itunes ή giphy API
                        if (albumCoverPath === Album_covers_path + 'default.gif' ||
                            albumCoverPath === Album_covers_path + 'small_default.gif') {
                            if (data.tags.fromAPI) { // αν έχει βρει κάτι στο API
                                myVideo.poster = data.tags.fromAPI;
                            } else {
                                myVideo.poster = AJAX_path + 'app/serveImage?imagePath=' + albumCoverPath;
                            }
                        }
                        else myVideo.poster = AJAX_path + 'app/serveImage?imagePath=' + albumCoverPath;
                    }

                    // Τρικ για να εμφανίζει το poster σε fullscreen όταν πηγαίνει από βίντεο σε mp3
                    // TODO να βρω καλύτερο τρόπο
                    for(var i=0; i<4; i++) {
                        toggleFullscreen();
                    }

                } else { // Αν είναι video
                    document.querySelector('#overlay_poster_source').innerHTML = '';
                    myVideo.poster = '';
                }

                currentPlaylistID = data.tags.playlist_id;

                // Αλλαγή του τίτλου του site με το τρέχον τραγούδι
                document.title = data.tags.title + ' : ' + data.tags.artist;

                //Μετατροπή του track time σε λεπτά και δευτερόλεπτα
                timeInMinutesAndSeconds = seconds2MinutesAndSeconds(data.tags.track_time)['minutes']+' : '+seconds2MinutesAndSeconds(data.tags.track_time)['seconds'];

                // εμφανίζει τα metadata στα input fields
                $('#songID').val(data.tags.songID);
                $('#title').val(data.tags.title);
                $('#artist').val(data.tags.artist);
                $('#genre').val(data.tags.genre);
                $('#year').val(data.tags.year);
                $('#album').val(data.tags.album);
                $('#play_count').val(data.tags.play_count);
                $('#date_played').val(data.tags.date_played);
                $('#date_added').val(data.tags.date_added);
                $('#rating').val(data.tags.rating);
                $('#rating_output').val(data.tags.rating);
                $('#jsTrackTime').val(timeInMinutesAndSeconds);
                $('#live').val(data.tags.live);
                $('#path_filename').val(decodeURIComponent(file_path));

                // Βάζει τα metadata για εμφάνιση όταν είναι σε fullscreen
                $('#overlay_artist').html(data.tags.artist);
                $('#overlay_song_name').html(data.tags.title);
                $('#overlay_song_year').html(data.tags.year);
                $('#overlay_album').html(data.tags.album);
                // $('#overlay_rating').html(stars);
                ratingToStars(data.tags.rating,'#overlay_rating');
                $('#overlay_play_count').html(data.tags.play_count);
                // Ο συνολικός χρόνος του τραγουδιού
                $('#jsOverlayTotalTrackTime').html(timeInMinutesAndSeconds); // σε full screen
                $('#jsTotalTrackTime').html(timeInMinutesAndSeconds); //  εκτός  full screen
                $('#overlay_live').html(liveOptions[data.tags.live]);
                showFullScreenVideoTags();

                makePlaylistItemActive(currentID);  // Κάνει active την συγκεκριμένη γραμμή στην playlist


            } else {   // Αν δεν βρει metadata τα κάνει όλα κενα
                $('#FormTags').find('input').not('[type="button"]').val('');
                $('#title').val(filename);
            }

            PlayTime++;
        }
    });

}

/**
 * callback that loads and plays the next video
 *
 * @param operation
 */
function loadAndplayNextVideo(operation) {

    myVideo.pause();
    // myVideo.currentTime = 0;
    // myVideo.poster='';

    if(operation === 'next') {
        currentPlaylistID++;
        // If we haven't proloaded song or audio convertion is running at this moment
        if(nextPreloadedID === 0 || audioConvertionRunning === true) {
            pathToTempAudioFile = null; // We ignore the result of audio convertion
            getNextVideoID(0, 'next', false);
        } else {
            loadNextVideo(nextPreloadedID);
            nextPreloadedID = 0;
        }
    }

    if(operation === 'prev') {
        pathToTempAudioFile = null; // We ignore the result of audio convertion
        getNextVideoID(0, 'prev', false);
    }

    // myVideo.play();

}

/**
 * Called when the page is loaded
 */
function init(){

    if(!initEventListenerHadler) {  // Αν δεν έχει ξανατρέξει
        // get the video element using the DOM api
        myVideo = document.querySelector("#myVideo");
        // Define a callback function called each time a video ends
        myVideo.addEventListener('ended', function () {
            loadAndplayNextVideo('next');
        }, false);

        if(!localStorage.volume)  // Αν δεν υπάρχει το localStorage.volume θέτει αρχική τιμή
            localStorage.volume = '1';

        myVideo.volume = parseFloat(localStorage.volume);   // Θέτει το volume με βάση την τιμή του localStorage.volume

        // Έλεγχος και αρχικοποίηση της κατάστασης του shuffle button
        checkShuffleButton();

        // Check and start the bitrate button condition
        checkBitrateButton();

        initEventListenerHadler = true;

        // Load the first video when the page is loaded.
        getNextVideoID(0, 'next', false);

        // if (Playtime > 0) {
        //     $("#overlay_media_controls .pause_play_button").removeClass('play_button_white').addClass('pause_button_white');
        //     $("#mediaControls .pause_play_button").removeClass('play_button').addClass('pause_button_black');
        // }
    }

    if($("#TotalNumberInPlaylist").length>0) {
        document.querySelector("#TotalNumberInPlaylist").innerHTML = playlistCount;  // εμφανίζει το σύνολο των κομματιών στην playlist
    }

}

/**
 * Όταν δεν βρει ένα video να παίξει
 *
 * @param e
 */
function failed(e) {
    console.log(myVideo.src);

    // video playback failed - show a message saying why
    switch (e.target.error.code) {
        case e.target.error.MEDIA_ERR_ABORTED:
            console.log('You aborted the video playback.');
            break;
        case e.target.error.MEDIA_ERR_NETWORK:
            console.log('A network error caused the video download to fail part-way.');
            break;
        case e.target.error.MEDIA_ERR_DECODE:
            console.log('The video playback was aborted due to a corruption problem or because the video used features your browser did not support.');
            break;
        case e.target.error.MEDIA_ERR_SRC_NOT_SUPPORTED:
            console.log('The video could not be loaded, either because the server or network failed or because the format is not supported.');
            break;
        default:
            console.log('An unknown error occurred.');
            break;
    }

    loadAndplayNextVideo('next');
}

