/**
 *
 * File: _shortcuts.js
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 26/09/17
 * Time: 22:46
 *
 * Shortcut actions
 *
 */


function nextSong() {
    loadAndplayNextVideo('next');
}

function prevSong() {
    loadAndplayNextVideo('prev');
}

function fwSong() {
    myVideo.currentTime+=60;
}

function rwSong() {
    myVideo.currentTime-=60;
}

function displayPauseButton() {
    $("#overlay_media_controls .pause_play_button").removeClass('play_button_white').addClass('pause_button_white');
    $("#mediaControls .pause_play_button").removeClass('play_button').addClass('pause_button_black');
}

function displayPlayButton() {
    $("#overlay_media_controls .pause_play_button").removeClass('pause_button_white').addClass('play_button_white');
    $("#mediaControls .pause_play_button").removeClass('pause_button_black').addClass('play_button');
}

function playSong() {
    if (myVideo.paused) {
        myVideo.play();
        displayPauseButton();
    }
    else {
        myVideo.pause();
        displayPlayButton();
    }
    showFullScreenVideoTags();
}

function interfaceToggle() {
    if(localStorage.OverlayAllwaysOn === 'true') {
        showFullScreenVideoTags('off');
    } else {
        showFullScreenVideoTags('on');
    }

}

function giphyToggle() {
    if(localStorage.AllwaysGiphy === 'true') {
        localStorage.AllwaysGiphy = 'false';
        displayVolume('giphyOFF');
    }
    else {
        localStorage.AllwaysGiphy = 'true';
        displayVolume('giphyON');
    }
}

function volumeUp() {
    if(myVideo.volume<0.99) {
        myVideo.volume += 0.01;
        localStorage.volume = myVideo.volume;
        displayVolume('up');
    }
}

function volumeDown() {
    if(myVideo.volume>0) {
        myVideo.volume -= 0.01;
        localStorage.volume = myVideo.volume;
        displayVolume('down');
    }
}

function volumeMute() {
    if(localStorage.mute === undefined) {
        localStorage.mute = 'false';
    }

    if (localStorage.mute === 'false') {
        localStorage.oldVolume = localStorage.volume;
        localStorage.mute = 'true';
        myVideo.volume = 0;
        localStorage.volume = myVideo.volume;
        displayVolume('mute');
    } else {
        localStorage.mute = 'false';
        myVideo.volume = localStorage.oldVolume;
        localStorage.volume = myVideo.volume;
        displayVolume('up');
    }
}

function increasePlaybackRate() {
    myVideo.playbackRate += 1;
}

function decreasePlaybackRate() {
    myVideo.playbackRate -= 1;
}

function resetPlaybackRate() {
    myVideo.playbackRate = 1;
}

function changeLive() {
    var liveID = $('#live');
    var live = liveID.val(); // Η τρέχουσα τιμή του live

    if (live == 0) {
        liveID.val('1');
    } else { // Αν είναι 0 το κάνει 1
        liveID.val('0'); // Αλλιώς (αν είναι 1) το κάνει 0
    }

    update_tags();  // ενημερώνει τα tags
}

/**
 * Ενεργοποιεί/απενεργοποιεί το shuffle/continue
 */
function toggleShuffle() {
    if(localStorage.PlayMode === 'shuffle') {
        localStorage.PlayMode = 'continue';
        $('.shuffle_button').removeClass('button_on').addClass('button_off');
    } else {
        localStorage.PlayMode = 'shuffle';
        $('.shuffle_button').removeClass('button_off').addClass('button_on');
    }
}

/**
 * Activate/deactivate audio convert to lower bitrate
 */
function toggleLowerBitrate()
{
    if(localStorage.convertToLowerBitrate === 'true') {
        localStorage.convertToLowerBitrate = 'false';
        $('.lower_bitrate').removeClass('button_on').addClass('button_off');
    } else {
        localStorage.convertToLowerBitrate = 'true';
        $('.lower_bitrate').removeClass('button_off').addClass('button_on');
    }
}

/**
 * Έλεγχος και αρχικοποίηση της κατάστασης του shuffle button
 */
function checkShuffleButton() {
    if(localStorage.PlayMode === 'shuffle') {
        $('.shuffle_button').addClass('button_on');
    } else {
        $('.shuffle_button').addClass('button_off');
    }
}

/**
 * Check and start the bitrate button condition
 */
function checkBitrateButton() {
    if(localStorage.convertToLowerBitrate === 'true') {
        $('.lower_bitrate').addClass('button_on');
    } else {
        $('.lower_bitrate').addClass('button_off');
    }
}

/**
 * Εμφανίζει τα media controls σε fullscreen
 */
function displayFullscreenControls() {

    if(checkFullscreen()) { // αν είναι σε full screen
        if (!displayingMediaControls) {   // αν δεν εμφανίζονται ήδη

            $('#overlay_media_controls').show();  // τα εμφανίζει

            displayingMediaControls = true;

            setTimeout(function () {  // Μετά από 5 δευτερόλεπτα τα κρύβει
                $('#overlay_media_controls').hide();
                displayingMediaControls = false;
            }, 5000)
        }
    }
}

/**
 * Έλεγχος shorcuts
 *
 * @param elem
 */
function getShortcuts(elem) {

    elem.addEventListener('keydown', function(event) {
        if (!FocusOnForm && VideoLoaded) {
            if (event.keyCode === 78) {  // N
                nextSong();
            }

            if (event.keyCode === 80) {  // P
                prevSong();
            }

            if (event.keyCode === 39) {  // δεξί βελάκι
                fwSong();
            }

            if (event.keyCode === 37) {  // αριστερό βελάκι
                rwSong();
            }

            if (event.keyCode === 32) {   // space
                playSong();
            }

            if (event.keyCode === 73) {   // I
                interfaceToggle();
            }

            if (event.keyCode === 71) {   // G
                giphyToggle();
            }

            if (event.keyCode === 38) {   // πάνω βελάκι
                volumeUp();
            }

            if (event.keyCode === 40) {   // κάτω βελάκι
                volumeDown();
            }

            if (event.keyCode === 77) {   // M Mute
                volumeMute();
            }

            if (event.keyCode === 190) {   // >
                increasePlaybackRate();
            }

            if (event.keyCode === 188) {   // <
                decreasePlaybackRate();
            }

            // if (event.keyCode === 187) {   // +
            // }
            //
            // if (event.keyCode === 189) {   // -
            // }

            if (event.keyCode === 191) {   // /
                resetPlaybackRate();
            }

            if (event.keyCode === 76) {   // L Αλλαγή live
                changeLive();
            }

            if (event.keyCode === 49) {   // 1
                update_tags(1);
            }

            if (event.keyCode === 50) {   // 2
                update_tags(2);
            }

            if (event.keyCode === 51) {   // 3
                update_tags(3);
            }

            if (event.keyCode === 52) {   // 4
                update_tags(4);
            }

            if (event.keyCode === 53) {   // 5
                update_tags(5);
            }

            if (event.keyCode === 65) {   // A
                addToPlaylist(currentID);  // Προσθήκη κομματιού στην playlist
            }

            if (event.keyCode === 68) {   // D
                removeFromPlaylist(currentID);  // Αφαίρεση κομματιού στην playlist
            }

            if (event.keyCode === 70) {   // F
                toggleFullscreen();  // μπαινοβγαίνει σε fullscreen
                FocusOnForm=false;
            }


        }

        // Έλεγχος του enter στις φόρμες
        if (event.keyCode === 13) {   // Enter
            pressEnterToForm();
        }

        // console.log(event.keyCode);

    }, false);
}