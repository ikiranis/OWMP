/**
 *
 * File: scripts.js
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 30/04/17
 * Time: 01:16
 *
 * Application scripts
 *
 */


// Ενημερώνει την υπάρχουσα εγγραφή στην βάση στο table paths, ή εισάγει νέα εγγραφή
function updatePath(id) {

    // Παίρνουμε όλα τα form id's που έχουν class paths_form
    var allForms = document.querySelectorAll('.paths_form');
    var FormIDs = [];

    for(var i = 0; i < allForms.length;  i++)
    {
        FormIDs.push(allForms[i].id);
    }


    var curID = id;  // Παίρνει μόνο το id

    var file_path=$("#PathID"+curID).find('input[name="file_path"]').val();
    var kind=$("#PathID"+curID).find('select[name="kind"]').val();

    callFile=AJAX_path+"app/updatePath.php?id="+curID+"&file_path="+file_path+"&kind="+kind;

    if ($('#' + FormIDs[curID]).valid()) {
        $.get(callFile, function (data) {
            updatedID=data.id;

            if (data.success == true) {
                if (updatedID == '0') {   // αν έχει γίνει εισαγωγή νέας εγγρσφής, αλλάζει τα ονόματα των elements σχετικά
                    PathKeyPressed = false;
                    LastInserted = data.lastInserted;
                    var PathID = $("#PathID" + LastInserted);

                    $("#PathID0").prop('id', 'PathID' + LastInserted);
                    PathID.find('form').prop('id','paths_formID'+ LastInserted);
                    PathID.find('input[name="file_path"]').attr("onclick", "displayBrowsePath(" + LastInserted + ")");
                    PathID.find('input[name="update_path"]').attr("onclick", "updatePath(" + LastInserted + ")");
                    PathID.find('input[name="delete_path"]').attr("onclick", "deletePath(" + LastInserted + ")");
                    PathID.find('input[id^="messagePathID"]').prop('id', 'messagePathID' + LastInserted);
                    $("#messagePathID" + LastInserted).addClassDelay("success", 3000);

                }
                else {
                    $("#messagePathID" + updatedID).addClassDelay("success", 3000);
                }
            }
            else $("#messagePathID" + updatedID).addClassDelay("failure", 3000);
        }, "json");
    }

}

// Σβήνει την εγγραφή στο paths
function deletePath(id) {
    callFile=AJAX_path+"app/deletePath.php?id="+id;


    $.get( callFile, function( data ) {
        if(data.success==true) {

            $("#messagePathID"+id).addClassDelay("success",3000);


            myClasses= $("#PathID"+id).find('input[name=delete_path]').classes();   // Παίρνει τις κλάσεις του delete_path

            if(!myClasses[2]) {   // Αν δεν έχει κλάση dontdelete σβήνει το div
                $("#PathID" + id).remove();
            }
            else {   // αλλιώς καθαρίζει μόνο τα πεδία
                var PathID = $("#PathID0");
                var PathWithID = $("#PathID"+id);

                PathWithID.find('input').val('');   // clear field values
                PathWithID.prop('id','PathID0');
                PathID.find('form').prop('id','paths_formID0');
                PathID.find('input[id^="messagePathID"]').text('').prop('id','messagePathID0');
                // αλλάζει την function στο button
                PathID.find('input[name="file_path"]').attr("onclick", "displayBrowsePath(paths_formID0)");
                PathID.find('input[name="update_path"]').attr("onclick", "updatePath(0)");
                PathID.find('input[name="delete_Path"]').attr("onclick", "deletePath(0)");

                $('#paths_formID0').validate({ // initialize the plugin
                    errorElement: 'div'
                });

            }
        }
        else $("#messagePathID"+id).addClassDelay("failure",3000);
    }, "json" );

}

// Εισάγει νέα div γραμμή αντιγράφοντας την τελευταία και μηδενίζοντας τις τιμές που είχε η τελευταία
function insertPath() {

    if(!PathKeyPressed) {
        var PathID = $("#PathID0");
        // clone last div row
        $('div[id^="PathID"]:last').clone().insertAfter('div[id^="PathID"]:last').prop('id','PathID0');
        PathID.find('input').val('');   // clear field values
        PathID.find('form').prop('id','paths_formID0');
        PathID.find('input[id^="messagePathID"]').text('').removeClass('success').prop('id','messagePathID0');
        // αλλάζει την function στο button
        PathID.find('select[name="main"]').attr("onchange", "checkMainSelected(0, false)");
        PathID.find('input[name="file_path"]').attr("onclick", "displayBrowsePath('paths_formID0')");
        PathID.find('select[name="main"]').val(0);
        PathID.find('input[name="update_path"]').attr("onclick", "updatePath(0)");
        PathID.find('input[name="delete_path"]').attr("onclick", "deletePath(0)");
        PathKeyPressed=true;

        $('#paths_formID0').validate({ // initialize the plugin
            errorElement: 'div'
        });


    }
}

// Εμφανίζει rating αστεράκια στο elem
function ratingToStars(rating,elem) {
    rating=parseInt(rating);

    $(elem).html('');

    for(var i=1;i<=rating;i++){
        var img = document.createElement("img");
        img.src = "img/star.png";
        var src = document.querySelector(elem);
        src.appendChild(img);
    }


}

// Αλλάζει όλα τα checkItems checkboxes με την τιμή του checkAll
function changeCheckAll(checkAll, checkItems) {
    // Η τιμή του checkAll checkbox
    var currentCheckAllValue = document.querySelector('input[name="'+checkAll+'"]').checked;

    // Όλα τα elements που θέλουμε να αλλάξουμε
    var all_checkboxes = document.querySelectorAll('input[name="'+checkItems+'"]');

    for(var i = 0; i < all_checkboxes.length; i++) {
        // Αλλαγή της τιμής όλων των checkbox elements με την currentCheckAllValue
        all_checkboxes[i].checked = currentCheckAllValue;
    }

}

// βάζει/βγάζει το video σε fullscreen
function toggleFullscreen() {
    elem = myVideo;
    if (!checkFullscreen()) { // μπαίνει σε full screen
        $(elem).addClass('full_screen_video');
        FullscreenON=true;
        showFullScreenVideoTags();
    } else {  // βγαίνει από full screen
        $(elem).removeClass('full_screen_video');
        FullscreenON=false;
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

// Ελέγχει αν βρίσκεται σε fullscreen
function checkFullscreen () {
    if (FullscreenON) {
        return true;
    }
    else return false;
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

// Εμφανίζει το div με τα metadata όταν είναι σε fullscreen
function showFullScreenVideoTags(toggle) {
    if(localStorage.OverlayAllwaysOn==null) localStorage.OverlayAllwaysOn='false';

    if (checkFullscreen ()) {  // Αν είναι σε fullscreen
        if(toggle!=null) {
            if (toggle == 'on') {
                $('#overlay').show();
                localStorage.OverlayAllwaysOn = 'true';
            }
            else {
                $('#overlay').hide();
                localStorage.OverlayAllwaysOn = 'false';
            }
        }
        else {
            if (localStorage.OverlayAllwaysOn=='false') {  // αν δεν εχει πατηθεί να πρέπει να είναι allways on
                if (!OverlayON) {  // αν δεν είναι on ήδη
                    OverlayON = true;
                    $('#overlay').show().delay(5000).hide(0);
                    OverlayON = false;
                }

            }
            else $('#overlay').show();
        }

    }
    else {
        $('#overlay').hide();
        $('#overlay_volume').hide();
    }

}

// Παίρνει το επόμενο τραγούδι και αρχίζει την αναπαραγωγή
function getNextVideoID(id, operation) {
    if(operation=='next') {
        var theCurrentPlaylistID=currentPlaylistID;
    }
    if(operation=='prev') {
        var theCurrentPlaylistID=currentQueuePlaylistID;
    }

    $.ajaxQueue({  // χρησιμοποιούμε το extension του jquery (αντί του $.ajax) για να εκτελεί το επόμενο AJAX μόλις τελειώσει το προηγούμενο
        url: AJAX_path + "app/getNextVideo.php",
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
            if (data.success == true) {
                currentID=data.file_id;

                if(data.operation=='next') {
                    currentPlaylistID = data.playlist_id;
                    currentQueuePlaylistID = 0;
                }
                if(data.operation=='prev') {
                    currentQueuePlaylistID = data.playlist_id;
                }
                loadNextVideo(id);
            }
        }
    });
}

// TODO όταν παίζει τραγούδια σε continues, αν παίξει κάποιο loved, τότε δεν συνεχίζει μετά από το τραγούδι που σταμάτησε
// Set the src of the video to the next URL in the playlist
// If at the end we start again from beginning (the modulo
// source.length does that)
function loadNextVideo(id)
{

    if(id==0) {
        callFile = AJAX_path+"app/getVideoMetadata.php?id="+currentID+'&tabID='+tabID;
    }

    else {
        currentID=id;

        callFile = AJAX_path+"app/getVideoMetadata.php?id="+currentID+'&tabID='+tabID;
    }

    TimeUpdated=false;


    if(localStorage.AllwaysGiphy=='true') // Αν θέλουμε μόνο από Giphy
        callFile=callFile+'&onlyGiphy=true';

    $.get(callFile, function (data) {  // τραβάει τα metadata του αρχείου

        var filename=data.file.filename; // σκέτο το filename

        var thePath=data.file.path;
        thePath=thePath.replace(WebFolderPath,'');
        file_path=DIR_PREFIX+thePath+encodeURIComponent(data.file.filename);    // Το filename μαζί με όλο το path

        // myVideo.src = file_path;
        myVideo.src = AJAX_path+"app/serveFile.php?id="+currentID;
        // myVideo.controls=false;
        // console.log(myVideo.src);

        myVideo.load();

        // Αν δεν είναι το πρώτο τραγούδι που παίζει τότε αρχίζει την αναπαραγωγή του τραγουδιού
        if (PlayTime > 0) {
            myVideo.play();
            displayPauseButton();
        } else { // αλλιώς κάνει pause
            myVideo.pause();
            displayPlayButton();
        }

        if (data.tags.success == true) { // τυπώνει τα data που τραβάει


            if(data.file.kind=='Music') {  // Αν είναι Music τότε παίρνει το album cover και το εμφανίζει

                var albumCoverPath = data.tags.albumCoverPath;
                // var iconImagePath = data.tags.iconImagePath;

                // Εμφάνιση του source στο fullscreen overlay
                document.querySelector('#overlay_poster_source').innerHTML = data.tags.apiSource;

                // Αν υπάρχει icon το εμφανίζει σαν favicon
                // if(iconImagePath) {
                //     document.querySelector("#theFavIcon").href = AJAX_path+'app/serveImage.php?imagePath=' + albumCoverPath;
                // }

                document.querySelector("#theFavIcon").href = AJAX_path+'app/serveImage.php?imagePath=' + albumCoverPath;

                // Εμφάνιση του cover
                if(localStorage.AllwaysGiphy==='true'){  // Αν θέλουμε μόνο από Giphy
                    if(data.tags.fromAPI) { // αν έχει βρει κάτι στο API
                        myVideo.poster = data.tags.fromAPI;
                    } else { // Αν όχι εμφανίζει το album cover
                        myVideo.poster = AJAX_path+'app/serveImage.php?imagePath=' + albumCoverPath;
                    }
                } else {   // όταν δεν θέλουμε μόνο από giphy
                    // Αν δεν υπάρχει album cover το παίρνουμε από itunes ή giphy API
                    if (albumCoverPath === Album_covers_path + 'default.gif' ||
                        albumCoverPath === Album_covers_path + 'small_default.gif') {
                        if (data.tags.fromAPI) { // αν έχει βρει κάτι στο API
                            myVideo.poster = data.tags.fromAPI;
                        } else {
                            myVideo.poster = AJAX_path+'app/serveImage.php?imagePath=' + albumCoverPath;
                        }
                    }
                    else myVideo.poster = AJAX_path+'app/serveImage.php?imagePath=' + albumCoverPath;
                }

                // Τρικ για να εμφανίζει το poster σε fullscreen όταν πηγαίνει από βίντεο σε mp3
                // TODO να βρω καλύτερο τρόπο
                for(var i=0; i<4; i++) {
                    toggleFullscreen();
                }

            }
            else { // Αν είναι video
                document.querySelector('#overlay_poster_source').innerHTML='';
                myVideo.poster='';
            }

            currentPlaylistID=data.tags.playlist_id;

            // Αλλαγή του τίτλου του site με το τρέχον τραγούδι
            document.title=data.tags.title+' : '+data.tags.artist;

            //Μετατροπή του track time σε λεπτά και δευτερόλεπτα
            timeInMinutesAndSeconds=seconds2MinutesAndSeconds(data.tags.track_time)['minutes']+' : '+seconds2MinutesAndSeconds(data.tags.track_time)['seconds'];

            // εμφανίζει τα metadata στα input fields
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

    }, "json");

}

// callback that loads and plays the next video
function loadAndplayNextVideo(operation) {

    myVideo.pause();
    // myVideo.poster='';


    if(operation=='next') {
        currentPlaylistID++;
        getNextVideoID(0, 'next');
    }

    if(operation=='prev') {
        getNextVideoID(0, 'prev');
    }

    // myVideo.play();

}

// Called when the page is loaded
function init(){

    if(!initEventListenerHadler) {  // Αν δεν έχει ξανατρέξει
        // get the video element using the DOM api
        myVideo = document.querySelector("#myVideo");
        // Define a callback function called each time a video ends
        myVideo.addEventListener('ended', function () {
            loadAndplayNextVideo('next');
        }, false);

        if(!localStorage.volume)  // Αν δεν υπάρχει το localStorage.volume θέτει αρχική τιμή
            localStorage.volume='1';

        myVideo.volume=parseFloat(localStorage.volume);   // Θέτει το volume με βάση την τιμή του localStorage.volume

        // Έλεγχος και αρχικοποίηση της κατάστασης του shuffle button
        checkShuffleButton();

        initEventListenerHadler = true;

        // Load the first video when the page is loaded.
        getNextVideoID(0, 'next');

        // if (Playtime > 0) {
        //     $("#overlay_media_controls .pause_play_button").removeClass('play_button_white').addClass('pause_button_white');
        //     $("#mediaControls .pause_play_button").removeClass('play_button').addClass('pause_button_black');
        // }
    }

    if($("#TotalNumberInPlaylist").length>0) {
        document.querySelector("#TotalNumberInPlaylist").innerHTML = playlistCount;  // εμφανίζει το σύνολο των κομματιών στην playlist
    }

}

// Όταν δεν βρει ένα video να παίξει
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

// Κάνει active το τρέχον row στην playlist
function makePlaylistItemActive(id) {
    $('.track').removeClass('is-active');  // Κάνει unactive όλα τα rows

    if($("#fileID"+id).length) { // Αν υπάρχει στην λίστα το συγκεκριμένο row το κάνει active
        $("#fileID" + id).addClass('is-active');

        // if (!checkFullscreen ()) // Αν δεν είναι σε fullscreen, αλλιώς λειτουργεί περιέργως
        //     document.querySelector("#fileID"+id).scrollIntoView();  // κάνει scrolling στο συγκεκριμένο row
    }

}

// Ενημερώνει τα tags του κομματιού
function update_tags(key_rating) {
    song_name=$('#FormTags #title').val();
    artist=$('#FormTags #artist').val();
    genre=$('#FormTags #genre').val();
    song_year=$('#FormTags #year').val();
    album=$('#FormTags #album').val();
    if(!key_rating)
        rating=$('#FormTags #rating').val();
    else rating=key_rating;  // Αν έχει πατηθεί νούμερο για βαθμολογία
    live=$('#FormTags #live').val();

    callFile=AJAX_path+"app/updateTags.php";

    $.ajax({
        url: callFile,
        type: 'POST',
        data: {
            id: currentID,
            song_name: song_name,
            artist: artist,
            genre: genre,
            song_year: song_year,
            album: album,
            rating: rating,
            live: live
        },
        dataType: "json",
        success: function(data) {
            if (data.success === true) {

                $("#message").addClassDelay("success", 3000);

                var fileCurrentID = $("#fileID"+currentID);

                if(fileCurrentID.length) {   // Ενημερώνει τα σχετικά πεδία στην λίστα
                    fileCurrentID.find('.song_name').text(song_name);
                    fileCurrentID.find('.artist').text(artist);
                    fileCurrentID.find('.genre').text(genre);
                    fileCurrentID.find('.album').text(album);
                    fileCurrentID.find('.song_year').text(song_year);
                    fileCurrentID.find('.rating').text(rating);
                }


                if(key_rating) {   // Αν έχει πατηθεί νούμερο για βαθμολογία
                    $('#rating').val(rating);
                    $('#rating_output').val(rating);
                }

                FocusOnForm=false;


                // Βάζει τα metadata για εμφάνιση όταν είναι σε fullscreen
                $('#overlay_artist').html(artist);
                $('#overlay_song_name').html(song_name);
                $('#overlay_song_year').html(song_year);
                $('#overlay_album').html(album);
                $('#overlay_live').html(liveOptions[live]);

                // $('#overlay_rating').html(stars);
                ratingToStars(rating,'#overlay_rating');

                showFullScreenVideoTags();


            }
            else $("#message").addClassDelay("failure", 3000);
        }

    })
}

// Ενημερώνει τα play count και date last played
function updateVideoPlayed() {
    callFile=AJAX_path+"app/updateTimePlayed.php?id="+currentID;

    $.get(callFile, function (data) {
        if (data.success === true) {


            $('#play_count').val(data.play_count);     // Ενημερώνει τα σχετικά input πεδία
            $('#date_played').val(data.date_last_played);

            var fileCurrentID = $("#fileID" + currentID);

            if(fileCurrentID.length) {    // Ενημερώνει τα σχετικά πεδία στην λίστα
                fileCurrentID.find('.play_count').text(data.play_count);
            }

            $('#overlay_play_count').html(data.play_count);

        }
    }, "json");
}

/**
 * Αναζήτηση για διπλές εγγραφές και εμφάνιση τους
 *
 * @param offset {int} Το τρέχον σημείο στην λίστα
 * @param step {int} Ο αριθμός των εγγραφών που θα εμφανίσει
 * @param firstTime {bool} True όταν τρέχει για πρώτη φορά η αναζήτηση
 */
function findDuplicates(offset, step, firstTime) {
    callFile=AJAX_path+"app/searchPlaylist.php?duplicates=true"+"&firstTime="+firstTime+"&offset="+offset+"&step="+step+'&tabID='+tabID;
    ProgressAnimation.init(false);

    $.get(callFile, function(data) {
        if (data) {
            $('#playlist_container').html(data);
            if(!syncRunning) {
                ProgressAnimation.kill();
            }
            $('#search').hide();
        }
        else {
            $('#playlist_container').html('Δεν βρέθηκαν εγγραφές');
            if(!syncRunning) {
                ProgressAnimation.kill();
            }
            $('#search').hide();
        }

    });
}

// Το σύνολο των γραμμών div μέσα στην φόρμα #SearchForm
function getNumberOfSearchRows()
{
    var searchFormID = $('#SearchForm');

    // Το σύνολο των γραμμών div μέσα στην φόρμα #SearchForm
    searchRows = searchFormID.children('div').length;
    // Το σύνολο των γραμμών .groupRow στην φόρμα #SearchForm
    var groupRows = searchFormID.children('.groupRow').length;
    searchRows = (searchRows-groupRows)-2;

    return searchRows;
}

// Διαβάζει την φόρμα και επιστρέφει τα πεδία αναζήτησησης σε μορφή array
function getSearchArray()
{
    var searchArray = [];

    // Το σύνολο των γραμμών div μέσα στην φόρμα #SearchForm
    var searchRows = getNumberOfSearchRows();

    for (var i = 1; i <= searchRows; i++) {
        searchArray[i] = {
            'search_field': $('#search_field' + i).val(),
            'search_text': $('#search_text' + i).val(),
            'search_operator': $('#search_operator' + i).val(),
            'search_equality': $('#search_equality' + i).val(),
            'group_operator': $('#group_operator' + i).val()
        }
    }

    return searchArray;
}

// αναζήτηση στην playlist
function searchPlaylist(offset, step, firstTime, search) {
    ProgressAnimation.init(false);

    if(!search) { // Αν δεν υπάρχει ήδη json search array, διαβάζουμε την φόρμα
        var searchArray = getSearchArray();
        jsonArray=JSON.stringify(searchArray);
    } else {
        jsonArray=JSON.stringify(search);
    }

    var mediaKind=document.querySelector('#ChooseMediaKind select[name=mediakind]').value;

    currentPlaylistID='1';

    callFile=AJAX_path+"app/searchPlaylist.php?jsonArray="+encodeURIComponent(jsonArray)+"&offset="+offset+"&step="+step
        +"&firstTime="+firstTime+"&mediaKind="+encodeURI(mediaKind)+'&tabID='+tabID;

    $.get(callFile, function(data) {
        if (data) {
            $('#playlist_container').html(data);
            if(!syncRunning) {
                ProgressAnimation.kill();
            }
            $('#search').hide();
        }
        else {
            $('#playlist_container').html('Δεν βρέθηκαν εγγραφές');
            if(!syncRunning) {
                ProgressAnimation.kill();
            }
            $('#search').hide();
        }
    });

}

// Φορτώνει μια manual playlist
function playPlaylist(offset, step) {
    var playlistID=document.querySelector('#playlist').value;

    if(playlistID==='') {  // Αν δεν έχει επιλεχτεί μια playlist
        DisplayMessage('.alert_error', phrases['you_have_to_choose_playlist']);
        return;
    }

    ProgressAnimation.init(false);

    // Αντιγραφή της manual playlist στην current playlist
    callFile=AJAX_path+"app/loadPlaylist.php?playlistID="+playlistID+'&tabID='+tabID;

    $.get(callFile, function (data) {
        // var playlistName=document.querySelector('#playlist option:checked').text; // Το όνομα της playlist

        if (data.success === true) {

            // Κάνει search και φορτώνει τα περιεχόμενα της manual playlist
            callFile=AJAX_path+'app/searchPlaylist.php?tabID='+tabID+'&firstTime=true&loadPlaylist=true'
                +"&offset="+offset+"&step="+step;

            $.get(callFile, function(data) {
                if (data) {
                    $('#playlist_container').html(data);
                    if(!syncRunning) {
                        ProgressAnimation.kill();
                    }
                }
                else {
                    $('#playlist_container').html(phrases['records_not_founded']);
                    if(!syncRunning) {
                        ProgressAnimation.kill();
                    }
                }

            });
        }
        else {
            DisplayMessage('.alert_error', phrases['playlist_loading_problem']);
        }
    }, "json");

}

// Προσθέτει μια γραμμή searchRow
function addSearchRow()
{
    var lastElementID = $('div[id^="searchRow"]:last').prop('id'); // To id του τελευταίου searchRow
    var newID = parseInt(lastElementID.match(/[0-9]+/))+1;
    var newElementID = 'searchRow' + newID; // To id του νέου searchRow
    var theNewElementID = $('#'+newElementID);

    // Προσθέτει το νέο searchRow αντιγράφοντας το searchRow0 και το κάνει visible
    $('#searchRow0').clone().insertAfter('div[id^="searchRow"]:last').prop('id',newElementID);
    theNewElementID.toggleClass('isHidden', 'isVisible');

    // Αλλάζει τα id όλων των child elements
    $('.search_field', '#'+newElementID).prop('id', 'search_field' + newID ).prop('name', 'search_field' + newID );
    $('.search_equality', '#'+newElementID).prop('id', 'search_equality' + newID ).prop('name', 'search_equality' + newID );
    $('.search_text', '#'+newElementID).prop('id', 'search_text' + newID ).prop('name', 'search_text' + newID );
    $('.search_operator', '#'+newElementID).prop('id', 'search_operator' + newID ).prop('name', 'search_operator' + newID );
    theNewElementID.find('label[for^="search_field"]').prop('for', 'search_field' + newID );
    theNewElementID.find('label[for^="search_text"]').prop('for', 'search_text' + newID );

    // Αλλάζει τις τιμές στις onclick functions
    theNewElementID.find('input[id="jsAddGroup"]').attr("onclick", "addOrAndToGroup("+newID+")");
    theNewElementID.find('input[id="jsRemoveSearchRow"]').attr("onclick", "removeSearchRow("+newID+")");

    checkSearchFieldChanges();  // επανεκίννηση του έλεγχου αλλαγών στα search fields

}

// Αφαιρεί μία γραμμή searchRow
function removeSearchRow(elementID)
{
    if(elementID!==1) { // αν δεν είναι η πρώτη γραμμή
        $("#searchRow"+elementID).remove();
    }
}

// Αφαιρεί το group Row
function removeGroupRow(elementID) {
    $('#searchRow-' + elementID).remove();
}

// Προσθέτει OR/AND στο group πεδίων
function addOrAndToGroup(elementID)
{
    var currentElement = document.querySelector('#searchRow' + elementID); // To element μετά το οποίο θα προστεθεί το select

    // Το div element μέσα στο οποίο θα μπει το select
    var divElement = document.createElement('div');
    divElement.setAttribute('id', 'searchRow-' + elementID);
    divElement.setAttribute('class', 'groupRow' );

    // Δημιουργεί το select
    var selectElement = document.createElement('select');
    selectElement.setAttribute('type', 'text');
    selectElement.setAttribute('class', 'search_operator');
    selectElement.setAttribute('id', 'group_operator' + elementID);
    selectElement.setAttribute('name', 'group_operator' + elementID);

    // Δημιουργεί το κουμπί για αφαίρεση της γραμμής
    var removeRowButton = document.createElement('input');
    removeRowButton.setAttribute('type', 'button');
    removeRowButton.setAttribute('class', 'o-imageButton ο-imageButton_removeGroupRow');
    removeRowButton.setAttribute('id', 'jsRemoveGroup' + elementID);
    removeRowButton.setAttribute('title', phrases['remove_group_row']);
    removeRowButton.setAttribute('onclick', 'removeGroupRow(' + elementID + ')');

    // Τα Options του select
    var option=[];

    option[0] = document.createElement('option');
    option[0].value = 'OR';
    option[0].innerHTML = phrases['search_or'];

    option[1] = document.createElement('option');
    option[1].value = 'AND';
    option[1].innerHTML = phrases['search_and'];

    $(divElement).insertAfter(currentElement); // προσθέτει το divElement μετά το currentElement

    for (var i = 0; i < 2; i++)
        selectElement.appendChild(option[i]); // προσθέτει τα options

    divElement.appendChild(selectElement); // Προσθέτει το select μέσα στο div
    divElement.appendChild(removeRowButton);  // Προσθέτει το button μέσα στο div
}

// Καθαρίζει την φόρμα search
function clearSearch()
{
    // Σβήνει όλα τα searchRow εκτός του searchRow0
    $('div[id^="searchRow"]').not('#searchRow0').remove();

    // Προσθέτει ένα
    addSearchRow();
}

// Φορτώνει την λίστα του ιστορικού
function loadPlayedQueuePlaylist() {
    ProgressAnimation.init(false);
    $('#search').hide();

    callFile=AJAX_path+'app/loadPlayedQueue.php?tabID='+tabID;

    $.get(callFile, function (data) {

        if (data.success === true) {

            callFile=AJAX_path+'app/searchPlaylist.php?tabID='+tabID+'&firstTime=true&loadPlaylist=true';

            $.get(callFile, function(data) {
                if (data) {
                    $('#playlist_container').html(data);
                    if(!syncRunning) {
                        ProgressAnimation.kill();
                    }
                }
                else {
                    $('#playlist_container').html(phrases['records_not_founded']);
                    if(!syncRunning) {
                        ProgressAnimation.kill();
                    }
                }

            });
        }
        else {
            DisplayMessage('.alert_error', phrases['playlist_loading_problem']);
        }

    }, "json");

}

// Ελέγχει και εμφανίζει το progress
function checkProgress()
{
    var progressCallFile = AJAX_path + "framework/getProgress.php";

    $.ajax({
        url: progressCallFile,
        type: 'GET',
        dataType: "json",
        success: function(progressData) {
            if (progressData.success === true) {
                if(progressData.progressInPercent>97 && localStorage.syncPressed==='true') {
                    DisplayWindow(3, null, null);
                }

                // TODO να δω αν χρειάζεται όντως αυτός ο έλεγχος
                // if($('.o-resultsContainer').length!==0 && localStorage.syncPressed=='true') {
                //     ProgressAnimation.init(false);
                // } else {
                //     ProgressAnimation.kill();
                // }
                ProgressAnimation.setProgressPercent(progressData.progressInPercent);

                // $("#theProgressNumber" ).html(progressData.progressInPercent+'%');
                // document.querySelector('#theProgressBar').value=progressData.progressInPercent;
            }
        }
    });
}

// Καθαρισμός του results container
function clearResultsContainer()
{
    document.querySelector('.o-resultsContainer_text').innerHTML = '';
}

// Εμφανίζει το εικονίδιο για τα results
function displayResultsIcon()
{
    $('.o-resultsContainer_iconContainer').toggleClass('isHidden', 'isVisible');
    BlinkElement.start('.o-resultsContainer_iconContainer');
}

// Εξαφανίζει το εικονίδιο για τα results
function hideResultsIcon()
{
    $('.o-resultsContainer_iconContainer').toggleClass('isVisible', 'isHidden');
}

// Εμφανίζει το icon του kill command
function displayKillCommandIcon()
{
    $('.o-resultsContainer_killCommandContainer').toggleClass('isHidden isVisible');
}

// Εξαφανίζει το icon του kill command
function hideKillCommandIcon()
{
    $('.o-resultsContainer_killCommandContainer').toggleClass('isVisible isHidden');
}

// TODO όταν κάνεις κάτι συγχρονισμό κτλ και τρέχει το animation, αν κάνεις την ίδια στιγμή κάτι search και στο τέλος του
// σταματήσει το animation, τότε σκοτώνει και το animation του συγχρονισμού
// Κάνει τον συγχρονισμό των αρχείων
function startTheSync(operation) {
    var mediaKind = document.querySelector('#mediakind').value;
    var GDOK =  document.querySelector('#jsGDOK').value;
    var callFile = AJAX_path+"app/syncTheFiles.php";

    // Έλεγχος αν είναι εγκατεστημένη η GD library
    if ( (operation==='sync' && GDOK==='false' && mediaKind==='Music') || (operation==='coverConvert' && GDOK==='false') ) {
        var confirmAnswer=confirm(phrases['GD_not_installed']);

        if(!confirmAnswer) {
            return;
        }
    }

    if(localStorage.syncPressed==='false'){  // Έλεγχος αν δεν έχει πατηθεί ήδη
        localStorage.syncPressed = 'true';

        clearResultsContainer();
        displayResultsIcon();
        ProgressAnimation.init(true);
        ProgressAnimation.setProgressPercent(0);
        displayKillCommandIcon();

        syncRunning = true;

        $('.syncButton').prop('disabled', true);

        // Κοιτάει για το progress κάθε ένα λεπτό και το τυπώνει
        var syncInterval=setInterval(function(){
            checkProgress();
        }, 1000);

        // Τρέχει τον συγχρονισμό και περιμένει το αποτέλεσμα να το τυπώσει
        $.ajax({
            url: callFile,
            type: 'GET',
            data: {
                operation: operation,
                mediakind: mediaKind
            },
            success: function(data) {
                $('.o-resultsContainer_text').append(data);
                displayResultsIcon();
                ProgressAnimation.kill();
                hideKillCommandIcon();
                localStorage.syncPressed='false';
                $('.syncButton').prop('disabled', false);
                clearInterval(syncInterval);
                syncRunning = false;
            }
        });

    } else {
        alert (phrases['running_process']);
    }


}

// Έλεγχος αν η process τρέχει
function checkProcessAlive() {
    // TODO να τεστάρω τι γίνεται την στιγμή που διαβάζει αρχεία και δεν στέλνει σημείο ζωής
    CallFile = AJAX_path + "framework/checkLastMomentAlive.php";

    if (localStorage.syncPressed === 'true') { // αν η process τρέχει
        $('.syncButton').prop('disabled', true);
    }
    else {
        $('.syncButton').prop('disabled', false);
    }

    TheSyncInterval=setInterval(function(){
        $.get(CallFile, function (data) {
            var syncButtonID = $('.syncButton');

            if (data.success === true) { // αν η process τρέχει
                localStorage.syncPressed='true';
                $('.syncButton').prop('disabled', true);
            }
            else {
                localStorage.syncPressed='false';
                syncButtonID.prop('disabled', false);
            }

            if(syncButtonID.length===0)
                clearInterval(TheSyncInterval);

        }, "json");

    }, 1000);
}

// Σβήνει το αρχείο που μόλις περάσαμε, επειδή υπάρχει ήδη
function deleteExistedFile(id)
{
    var confirmAnswer=confirm(phrases['sure_to_delete_file']);

    if (confirmAnswer===true) {
        $.ajax({
            url: AJAX_path + "app/deleteFile.php",
            type: 'GET',
            data: {
                id: id
            },
            dataType: "json",
            success: function(data) {
                if (data.success === true) {
                    $('#jsFileAlreadyExist' + id).remove();
                }
            }
        });
    }
}

// Καλεί AJAX request για να κατεβάσει το βίντεο από το youtube
function callGetYouTube(id,counter,total, mediaKind) {
    $.ajaxQueue({  // χρησιμοποιούμε το extension του jquery (αντί του $.ajax) για να εκτελεί το επόμενο AJAX μόλις τελειώσει το προηγούμενο
        url: AJAX_path + "framework/getYouTube.php",
        type: 'GET',
        async: true,
        data: {
            id: id,
            mediaKind: mediaKind
        },
        dataType: "json",
        beforeSend: function (xhr) {
            if(runningYoutubeDownload) {
                $('.o-resultsContainer_text').append('<p> :: '+phrases['youtube_downloading']+
                    ' <a href=https://www.youtube.com/watch?v=' + id + '>' +
                    'https://www.youtube.com/watch?v=' + id + '</a></p>');

                progressPercent = parseInt(((counter + 1) / total) * 100);

                ProgressAnimation.setProgressPercent(progressPercent);

                // Έλεγχος αν είναι hidden. Τότε αρχίζει το blinking και πάλι. Αλλιώς όχι
                var resultsContainer = document.querySelector('.o-resultsContainer');

                if(resultsContainer.classList.contains('isHidden')) {
                    BlinkElement.start('.o-resultsContainer_iconContainer');
                }

            }
            else xhr.abort();

        },
        success: function (data) {
            var resultsContainerTextID = $(".o-resultsContainer_text");

            if (data.success === true) {
                // TODO να το φτιάξω εμφανισιακά και με σωστό css
                resultsContainerTextID.append('<img src="' + data.imageThumbnail+'" style="float:left;">' +
                    '<p class="is_youTube-success">'+phrases['youtube_downloaded_to_path']+': ' + data.result + '</p>');

                resultsContainerTextID.append(data.filesToDelete);

            } else {
                resultsContainerTextID.append('<p class="is_youTube-fail">'+phrases['youtube_problem']+': ' + data.theUrl + '</p>');
            }
        }
    });
}

// Ελέγχει αν είναι video ή playlist και επιστρέφει τα id σε σχετικό πίνακα videoItems[]
function checkVideoUrl(url,counter,total) {
    $.ajaxQueue({  // χρησιμοποιούμε το extension του jquery (αντί του $.ajax) για να εκτελεί το επόμενο AJAX μόλις τελειώσει το προηγούμενο
        url: AJAX_path + "framework/checkVideoURL.php",
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

// Καλεί το ajax σε queue για να κάνει το μαζικό update αρχείων
function callUpdateTheFile(path, filename, id, counter, total) {
    $.ajaxQueue({  // χρησιμοποιούμε το extension του jquery (αντί του $.ajax) για να εκτελεί το επόμενο AJAX μόλις τελειώσει το προηγούμενο
        url: AJAX_path + "app/updateFile.php",
        type: 'GET',
        async: true,
        data: {
            path: path,
            filename: filename,
            id: id
        },
        dataType: "json",
        beforeSend: function (xhr) {
            if(runningUpdateFiles) {
                progressPercent = parseInt(((counter + 1) / total) * 100);

                ProgressAnimation.setProgressPercent(progressPercent);

                // $("#theProgressNumber").html(progressPercent + '%');
                // document.querySelector('#theProgressBar').value = progressPercent;
            }
            else xhr.abort();

        },
        success: function (data) {
            if (data.success) {
                $("#updateRow" + data.id).remove();
            }
        }
    });

}

// Καλεί το ajax σε queue για να κάνει το μαζικό delete αρχείων
function callDeleteTheFile(fullpath, filename, id, counter, total) {
    $.ajaxQueue({  // χρησιμοποιούμε το extension του jquery (αντί του $.ajax) για να εκτελεί το επόμενο AJAX μόλις τελειώσει το προηγούμενο
        url: AJAX_path + "app/deleteFile.php",
        type: 'GET',
        async: true,
        data: {
            // fullpath: fullpath,
            // filename: filename,
            id: id
        },
        dataType: "json",
        beforeSend: function (xhr) {
            if(runningUpdateFiles) {
                progressPercent = parseInt(((counter + 1) / total) * 100);

                ProgressAnimation.setProgressPercent(progressPercent);
            }
            else xhr.abort();

        },
        success: function (data) {
            if (data.success) {
                $("#deleteRow" + data.id).remove();
            }
        }
    });

}

// Κατεβάζει ένα ή περισσότερα βίντεο από το YouTube
function downloadTheYouTube() {
    var urls=document.querySelector('.o-youTube__textArea').value;
    var mediaKind=document.querySelector('.jsMediaKind').value;

    var OKGo=false;

    if(mediaKind === 'Music Video') {
        var MusicVideoPathOK=document.querySelector('#jsMusicVideoPathOK').value;

        if(MusicVideoPathOK) {
            OKGo=true;
        } else {
            DisplayMessage('.alert_error', phrases['cant_write_to_path']);
        }
    } else {
        var MusicPathOK=document.querySelector('#jsMusicPathOK').value;

        if(MusicPathOK) {
            OKGo=true;
        } else {
            DisplayMessage('.alert_error', phrases['cant_write_to_path']);
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

/**
 * Αλλάζει ένα text input σε select. Elem είναι το input field που θα αλλάξουμε. ID το id του row
 *
 * @param elem {element object} Το element που θα αλλάξει
 * @param elementID {int} Το id του element
 * @param optionsArray {array} To array με τα options
 */
function changeToSelect(elem, elementID, optionsArray) {

    elem.outerHTML = ""; // Σβήσιμο του υπάρχοντος
    // delete elem;

    var afterElement = document.querySelector('#search_operator' + elementID); // To element πριν το οποίο θα προστεθεί το select

    // Δημιουργεί το select
    var element = document.createElement('select');
    element.setAttribute('type', 'text');
    element.setAttribute('id', 'search_text' + elementID);
    element.setAttribute('name', 'search_text' + elementID);

    var option=[];

    // Δημιουργεί τα options του select
    for (var i = 0; i < optionsArray.length; i++) {
        option[i] = document.createElement('option');
        option[i].value = i;
        option[i].innerHTML = optionsArray[i];
    }

    // προσθέτει το element πριν το afterElement
    var newSelect=document.querySelector('#searchRow'+elementID).insertBefore(element, afterElement);

    for (i = 0; i < optionsArray.length; i++)
        newSelect.appendChild(option[i]); // προσθέτει τα options

}

// Αλλάζει ένα select σε input
function changeSelectToInput(elem, elementID) {
    elem.outerHTML = ""; // Σβήσιμο του υπάρχοντος
    // delete elem;

    var afterElement = document.querySelector('#search_operator' + elementID); // To element πριν το οποίο θα προστεθεί το select

    // Δημιουργεί το select
    var element = document.createElement('input');
    element.setAttribute('type', 'text');
    element.setAttribute('id', 'search_text' + elementID);
    element.setAttribute('name', 'search_text' + elementID);

    var newSelect = document.querySelector('#searchRow'+elementID).insertBefore(element, afterElement); // προσθέτει το element πριν το afterElement
}

// εμφανίζει το sliderId value στο outputId
function printValue(sliderId, outputId) {
    outputId.value = sliderId.value;
}

// Σβήνει ένα αρχείο μαζί με την αντίστοιχη εγγραφή στην βάση
function deleteFile(id) {
    if(id === 0) {  // Αν το id 0 παίρνει τα ids όλων των checkbox items σε πίνακα
        var all_checkboxes = document.querySelectorAll('input[name="check_item[]"]:checked');

        var checkIDs = [];

        for(var i = 0; i < all_checkboxes.length;  i++)
        {
            checkIDs.push(all_checkboxes[i].value);
        }
    }

    var confirmAnswer=confirm(phrases['sure_to_delete_files']);

    if (confirmAnswer === true) {
        if(id!==0) { // Αν δεν είναι 0 τότε σβήνει μοναδική εγγραφή
            callFile = AJAX_path + "app/deleteFile.php?id=" + id;

            $.get(callFile, function (data) {
                if (data.success === true) {
                    $("#fileID" + id).remove();
                }


            }, "json");
        } else {  // σβήνει μαζικά όσα αρχεία έχουν τσεκαριστεί
            for(var i = 0; i < checkIDs.length;  i++) {
                callFile = AJAX_path + "app/deleteFile.php?id=" + checkIDs[i];

                $.get(callFile, function (data) {
                    if (data.success === true) {
                        $("#fileID" + data.id).remove();
                    }
                }, "json");
            }
        }
    }
}

// Σβήνει μια λίστα (array) αρχείων
function deleteFiles(filesArray) {
    var confirmAnswer=confirm(phrases['sure_to_delete_files']);

    if (confirmAnswer === true) {
        ProgressAnimation.init(true);
        ProgressAnimation.setProgressPercent(0);

        $("#AgreeToDeleteFiles").remove();
        displayKillCommandIcon();

        runningUpdateFiles = true;

        for (var i = 0; i < filesArray.length; i++) {
            callDeleteTheFile(filesArray[i]['fullpath'], filesArray[i]['filename'], filesArray[i]['id'], i, filesArray.length);
        }

        $( document ).one("ajaxStop", function() {  // Μόλις εκτελεστούν όλα τα ajax κάνει το παρακάτω
            ProgressAnimation.kill();
            hideKillCommandIcon();
            runningUpdateFiles = false;
        });
    }
}

// Ανοίγει το παράθυρο για edit των tags
function openMassiveTagsWindow() {
    $('#editTag').show();
}

// Κλείνει το παράθυρο για edit των tags
function cancelTheEdit() {
    $('#editTag').hide();
}

// Κλείνει το παράθυρο για search
function cancelTheSearch() {
    $('#search').hide();
}

function readImage(files) {
    var selectedFile = document.getElementById('uploadFile').files[0];

    myMime = selectedFile.type;

    var f = files[0];

    var reader = new FileReader();

    // Called when the file content is loaded, e.target.result is
    // The content
    reader.onload = function (e) {
        // create a span with CSS class="thumb", for nicer layout
        var thumbImage = document.querySelector('#myImage');

        thumbImage.innerHTML = "<img class='thumb' src='" +
            e.target.result + "' alt='a picture'/>";

        myImage = e.target.result;

    };

    // Start reading asynchronously the file
    reader.readAsDataURL(f);
}

// Κάνει μαζικό edit των στοιχείων μιας λίστας (array) αρχείων
function editFiles() {

    var confirmAnswer=confirm(phrases['sure_to_update_files']);

    if (confirmAnswer === true) {
        var all_checkboxes = document.querySelectorAll('input[name="check_item[]"]:checked');

        var checkIDs = [];

        for(var i = 0; i < all_checkboxes.length;  i++)
        {
            checkIDs.push(all_checkboxes[i].value);
        }

        artist=$('#FormMassiveTags #artist').val();
        genre=$('#FormMassiveTags #genre').val();
        song_year=$('#FormMassiveTags #year').val();
        album=$('#FormMassiveTags #album').val();
        rating=$('#FormMassiveTags #rating').val();
        live=$('#FormMassiveTags #live').val();


        if(myImage!=='') {
            coverImage = myImage;
            coverMime = myMime;
        }
        else {
            coverImage = '';
            coverMime = '';
        }



        for (i = 0; i < checkIDs.length; i++) {


            callFile=AJAX_path+"app/updateTags.php";

            $.ajax({
                url: callFile,
                type: 'POST',
                data: {
                    id: checkIDs[i],
                    artist: artist,
                    genre: genre,
                    song_year: song_year,
                    album: album,
                    rating: rating,
                    live: live,
                    coverMime: coverMime,
                    coverImage: coverImage
                },
                dataType: "json",
                success: function(data) {
                    if (data.success === true) {
                        var fileDataID = $("#fileID"+data.id);

                        if(fileDataID.length) {   // Ενημερώνει τα σχετικά πεδία στην λίστα
                            if(artist!=='')
                                fileDataID.find('.artist').text(artist);
                            if(genre!=='')
                                fileDataID.find('.genre').text(genre);
                            if(album!=='')
                                fileDataID.find('.album').text(album);
                            if(song_year!=='')
                                fileDataID.find('.song_year').text(song_year);
                            if(rating!==0)
                                fileDataID.find('.rating').text(rating);
                        }

                    }
                }
            })

        }

        $('#editTag').hide();
    }

}

// Ενημερώνει μια λίστα (array) αρχείων που έχουν αλλάξει filepath και filename
function updateFiles(filesArray) {
    var confirmAnswer = confirm(phrases['sure_to_update_files']);

    if (confirmAnswer === true) {
        ProgressAnimation.init(true);
        ProgressAnimation.setProgressPercent(0);
        $("#AgreeToUpdateFiles").remove();
        displayKillCommandIcon();

        console.log ('Files to update: '+filesArray.length);

        runningUpdateFiles = true;

        for (var i = 0; i < filesArray.length; i++) {
            callUpdateTheFile(filesArray[i]['path'], filesArray[i]['filename'], filesArray[i]['id'], i, filesArray.length);
        }

        $(document).one("ajaxStop", function() {  // Μόλις εκτελεστούν όλα τα ajax κάνει το παρακάτω
            ProgressAnimation.kill();
            hideKillCommandIcon();
            // $(".o-resultsContainer_text").append('<p>'+phrases['starting_sync']+'</p>');
            runningUpdateFiles = false;
        });

    }
}

// Προσθέτει ένα αρχείο σε playlist
function addToPlaylist(fileID) {
    var playlistID=document.querySelector('#playlist').value;

    if(playlistID === '') {  // Αν δεν έχει επιλεχτεί μια playlist
        if(!checkFullscreen()) { // αν δεν είναι σε full screen
            DisplayMessage('.alert_error', phrases['you_have_to_choose_playlist']);
        }
        else { // αν είναι σε full screen
            DisplayMessage('#error_overlay', phrases['you_have_to_choose_playlist']);
        }

        return;

    }

    callFile=AJAX_path+"app/addToPlaylist.php?playlistID="+playlistID+'&fileID='+fileID;


    $.get(callFile, function (data) {
        var playlistName=document.querySelector('#playlist option:checked').text; // Το όνομα της playlist

        if (data.success === true) {
            if(!checkFullscreen()) { // αν δεν είναι σε full screen
                DisplayMessage('.alert_error', phrases['song_added_to'] + ' ' + data.song_name
                    + ' ' + phrases['_to_playlist'] + ' ' + playlistName);
            }
            else { // αν είναι σε full screen
                DisplayMessage('#error_overlay', phrases['song_added_to'] + ' ' + data.song_name
                    + ' ' + phrases['_to_playlist'] + ' ' + playlistName);
            }
        }
        else {
            if(data.errorID === 2) {
                if(!checkFullscreen()) { // αν δεν είναι σε full screen
                    DisplayMessage('.alert_error', phrases['song_exist_to'] + ' ' + data.song_name
                        + ' ' + phrases['_to_playlist'] + ' ' + playlistName);
                }
                else { // αν είναι σε full screen
                    DisplayMessage('#error_overlay', phrases['song_exist_to'] + ' ' + data.song_name
                        + ' ' + phrases['_to_playlist'] + ' ' + playlistName);
                }
            }
        }
    }, "json");
}

// Αφαίρεση κομματιού από την playlist
function removeFromPlaylist(fileID) {
    var playlistID=document.querySelector('#playlist').value;

    if(playlistID === '') {  // Αν δεν έχει επιλεχτεί μια playlist
        if(!checkFullscreen()) { // αν δεν είναι σε full screen
            DisplayMessage('.alert_error', phrases['you_have_to_choose_playlist']);
        }
        else { // αν είναι σε full screen
            DisplayMessage('#error_overlay', phrases['you_have_to_choose_playlist']);
        }

        return;

    }

    callFile=AJAX_path+"app/removeFromPlaylist.php?playlistID="+playlistID+'&fileID='+fileID;


    $.get(callFile, function (data) {
        var playlistName=document.querySelector('#playlist option:checked').text; // Το όνομα της playlist

        if (data.success === true) {

            if(!checkFullscreen()) { // αν δεν είναι σε full screen
                DisplayMessage('.alert_error', phrases['song_deleted_from'] + ' ' + data.song_name
                    + ' ' + phrases['_from_playlist'] + ' ' + playlistName);
            }
            else { // αν είναι σε full screen
                DisplayMessage('#error_overlay', phrases['song_deleted_from'] + ' ' + data.song_name
                    + ' ' + phrases['_from_playlist'] + ' ' + playlistName);
            }

            // Σβήσιμο της σχετικής γραμμής στην λίστα
            document.querySelector('#fileID'+data.fileID).remove();
        }
        else {
            if(!checkFullscreen()) { // αν δεν είναι σε full screen
                DisplayMessage('.alert_error', phrases['song_not_deleted'] + ' ' + data.song_name
                    + ' ' + phrases['_from_playlist'] + ' ' + playlistName);
            } else {
                DisplayMessage('#error_overlay', phrases['song_not_deleted'] + ' ' + data.song_name
                    + ' ' + phrases['_from_playlist'] + ' ' + playlistName);
            }
        }
    }, "json");
}

// Εμφανίζει το volume
function displayVolume(operation) {
    if(checkFullscreen()) {
        var volume = parseInt(localStorage.volume * 100);
        var overlayTextID = $('#overlay_volume_text');

        if(operation!=='giphyON' && operation!=='giphyOFF')
            document.querySelector('#overlay_volume_text').innerText = volume;

        overlayTextID.removeClass();

        switch (operation) {  // Αναλόγως τι είναι το πεδίο αλλάζουμε το search text type
            case 'up':
                overlayTextID.addClass('overlay_volume_up');
                break;
            case 'down':
                overlayTextID.addClass('overlay_volume_down');
                break;
            case 'mute':
                overlayTextID.addClass('overlay_volume_mute');
                break;
            case 'giphyON':
                overlayTextID.addClass('overlay_giphy');
                document.querySelector('#overlay_volume_text').innerText = 'on';
                break;
            case 'giphyOFF':
                overlayTextID.addClass('overlay_giphy');
                document.querySelector('#overlay_volume_text').innerText = 'off';
                break;
        }


        $('#overlay_volume').show().delay(1500).fadeOut();
    }
}

// Αλλάζει τον χρόνο που βρίσκεται το track αναλόγως την θέση στον slider
function controlTrack() {
    var curTime = null;

    if(checkFullscreen()) { // Όταν είναι σε full screen
        curTime = document.querySelector('.o-trackTime--overlay__range').value;  // ο τρέχον track time σε ποσοστό
    } else { // όταν δεν είναι σε full screen
        curTime = document.querySelector('.o-trackTime__range').value;  // ο τρέχον track time σε ποσοστό
    }

    var duration = myVideo.duration;  // ο συνολικός track time

    // μετατροπή του ποσοστού χρόνου σε πραγματικά δευτερόλεπτα
    myVideo.currentTime = parseInt( (curTime/100)*duration );
}

// Εμφανίζει το τρέχον cover image, όπου είναι ο κέρσορας
function displayCoverImage(elem) {
    $('.coverImage').hide();
    $('#'+elem).find('img').show();
}

function hideCoverImage() {
    $('.coverImage').hide();
}

// Εμφανίζει το παράθυρο για αναζήτηση
function displaySearchWindow() {
    $('#search').show();
}

// Εμφανίζει το παράθυρο για εισαγωγή playlist
function displayInsertPlaylistWindow() {
    $('#insertPlaylistWindow').show();
}

// Εμφανίζει το παράθυρο για εισαγωγή smart playlist
function displayInsertSmartPlaylistWindow()
{
    $('#insertSmartPlaylistWindow').show();
}

// Κλείνει το παράθυρο για εισαγωγή playlist
function cancelCreatePlaylist()
{
    $('#insertPlaylistWindow').hide();
}

// Κλείνει το παράθυρο για εισαγωγή smart playlist
function cancelCreateSmartPlaylist()
{
    $('#insertSmartPlaylistWindow').hide();
}

function checkTheChanges(element)
{
    var changedElement = $(element).attr('id');  // το id του αλλαγμένου selected
    var valueOfChangedElement = $(element).val();  // η τιμή του αλλαγμένου selected

    var elementID = parseInt(changedElement.replace('search_field',''));   // παίρνουμε μόνο το id για να το προσθέσουμε στο search_text element
    var searchStringElement = document.querySelector('#search_text'+elementID);

    // αν το πεδίο που θέλουμε να αλλάξουμε δεν είναι κάποιο από αυτά
    if( valueOfChangedElement !== 'rating' &&  valueOfChangedElement !== 'live' )
        if (searchStringElement.type === 'select-one')  // Ελέγχουμε αν το υπάρχον είναι select
            changeSelectToInput(searchStringElement, elementID);  // Αν είναι select το αλλάζουμε σε input

    switch (valueOfChangedElement) {  // Αναλόγως τι είναι το πεδίο αλλάζουμε το search text type
        case 'date_added': searchStringElement.type='date'; break;
        case 'date_last_played': searchStringElement.type='date'; break;
        case 'play_count': searchStringElement.type='number'; break;
        case 'rating': changeToSelect(searchStringElement, elementID, ratingOptions); break;
        case 'video_width': searchStringElement.type='number'; break;
        case 'video_height': searchStringElement.type='number'; break;
        case 'filesize': searchStringElement.type='number'; break;
        case 'track_time': searchStringElement.type='number'; break;
        case 'song_year': searchStringElement.type='number'; break;
        case 'live': changeToSelect(searchStringElement, elementID, liveOptions); break;
        case 'song_name':  searchStringElement.type='text'; break;
        case 'artist': searchStringElement.type='text'; break;
        case 'genre': searchStringElement.type='text'; break;
        case 'album': searchStringElement.type='text'; break;
    }
}

// Έλεγχος για όταν γίνονται αλλαγές στα search fields
function checkSearchFieldChanges()
{
    var searchFieldID = $('.search_field');

    searchFieldID.off('change'); // Αφαίρεση προηγούμενων change events

    // Έλεγχος πιο πεδίο έχουμε διαλέξει για να ψάξουμε, ώστε να αλλάξουμε τον τύπο του search text
    searchFieldID.change(function() {
        checkTheChanges(this);
    });
}

// Functions για τα controls
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
    if(localStorage.OverlayAllwaysOn === 'true')
        showFullScreenVideoTags('off');
    else
        showFullScreenVideoTags('on');
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
    if(localStorage.mute === null) localStorage.mute='false';

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

    if (live === 0) {
        liveID.val('1');
    } else { // Αν είναι 0 το κάνει 1
        liveID.val('0'); // Αλλιώς (αν είναι 1) το κάνει 0
    }

    update_tags();  // ενημερώνει τα tags
}

// Ενεργοποιεί/απενεργοποιεί το shuffle/continue
function toggleShuffle() {
    if(localStorage.PlayMode === 'shuffle') {
        localStorage.PlayMode='continue';
        $('.shuffle_button').removeClass('shuffle_on').addClass('shuffle_off');
    } else {
        localStorage.PlayMode='shuffle';
        $('.shuffle_button').removeClass('shuffle_off').addClass('shuffle_on');
    }
}

// Έλεγχος και αρχικοποίηση της κατάστασης του shuffle button
function checkShuffleButton() {
    if(localStorage.PlayMode === 'shuffle') {
        $('.shuffle_button').addClass('shuffle_on');
    } else {
        $('.shuffle_button').addClass('shuffle_off');
    }
}

// Εμφανίζει τα media controls σε fullscreen
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

// Έλεγχος shorcuts
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
                changeLive()
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

// Κάνει export την τρέχουσα playlist
function exportPlaylist() {
    var confirmAnswer=confirm(phrases['sure_to_export_playlist']);

    if (confirmAnswer === true) {
        callFile=AJAX_path+"app/exportPlaylist.php?tabID="+tabID;

        if(localStorage.syncPressed === 'false'){  // Έλεγχος αν δεν έχει πατηθεί ήδη
            localStorage.syncPressed='true';

            ProgressAnimation.init(true);
            ProgressAnimation.setProgressPercent(0);
            displayKillCommandIcon();

            syncRunning = true;

            progressCallFile = AJAX_path + "framework/getProgress.php";

            var exportInterval=setInterval(function(){

                $.get(progressCallFile, function (progressData) {
                    if (progressData.success === true) {
                        ProgressAnimation.setProgressPercent(progressData.progressInPercent);
                        // $("#theProgressNumber" ).html(progressData.progressInPercent+'%');
                        // document.querySelector('#theProgressBar').value=progressData.progressInPercent;
                    }
                }, "json");

            }, 1000);

            $.get(callFile, function(data) {
                hideKillCommandIcon();
                localStorage.syncPressed='false';
                clearInterval(exportInterval);
                syncRunning = false;
            });


        }
        else {
            alert (phrases['running_process']);
        }
    }
}

// Δημιουργεί μια manual playlist
function createPlaylist() {
    var playlistName = document.querySelector('#playlistName').value;

    var callFile=AJAX_path + "app/createPlaylist.php?playlistName=" + playlistName;

    $.get(callFile, function (data) {
        if (data.success === true) {
            $('#insertPlaylistWindow').hide();

            // Προσθέτει στο select #playlist καινούργιο option με την νέα playlist
            var option = document.createElement('option');
            option.value = data.playlistID;
            option.innerHTML = data.playlistName;

            document.querySelector('#playlist').appendChild(option); // προσθέτει το νέο option

            DisplayMessage('.alert_error', phrases['playlist_created'] + ' ' + data.playlistName);

            document.querySelector('#insertPlaylist').reset();
        }
        else {
            DisplayMessage('.alert_error', phrases['playlist_not_created'] + ' ' + data.playlistName);
        }

    }, "json");
}

// Δημιουργεί μια smart playlist
function createSmartPlaylist() {
    var playlistName = document.querySelector('#smartPlaylistName').value;

    var callFile = AJAX_path + "app/createSmartPlaylist.php?playlistName=" + playlistName;

    $.get(callFile, function (data) {
        if (data.success === true) {
            $('#insertSmartPlaylistWindow').hide();

            // Προσθέτει στο select #playlist καινούργιο option με την νέα playlist
            var option = document.createElement('option');
            option.value = data.playlistID;
            option.innerHTML = data.playlistName;

            document.querySelector('#smartPlaylist').appendChild(option); // προσθέτει το νέο option

            DisplayMessage('.alert_error', phrases['smart_playlist_created'] + ' ' + data.playlistName);

            document.querySelector('#insertSmartPlaylist').reset();
        }
        else {
            DisplayMessage('.alert_error', phrases['smart_playlist_not_created'] + ' ' + data.playlistName);
        }

    }, "json");
}

// Σβήνει μια manual playlist
function deletePlaylist() {
    var playlistID=document.querySelector('#playlist').value;

    if(playlistID === '') {  // Αν δεν έχει επιλεχτεί μια playlist
        DisplayMessage('.alert_error', phrases['you_have_to_choose_playlist']);
        return;
    }

    var confirmAnswer=confirm(phrases['sure_to_delete_playlist']);

    if (confirmAnswer === true) {

        var callFile = AJAX_path + "app/deletePlaylist.php?playlistID=" + playlistID;

        $.get(callFile, function (data) {
            var playlistName = document.querySelector('#playlist option:checked').text; // Το όνομα της playlist

            if (data.success === true) {
                DisplayMessage('.alert_error', phrases['playlist_deleted'] + ' ' + playlistName);

                // Σβήνει το συγκεκριμένο option από το select #playlist
                document.querySelector("#playlist option:checked").remove();

            }
            else {
                DisplayMessage('.alert_error', phrases['playlist_not_deleted'] + ' ' + playlistName);
            }
        }, "json");

    }
}

// Σβήνει μια smart playlist
function deleteSmartPlaylist() {
    var playlistID=document.querySelector('#smartPlaylist').value;

    if(playlistID === '') {  // Αν δεν έχει επιλεχτεί μια playlist
        DisplayMessage('.alert_error', phrases['you_have_to_choose_playlist']);
        return;
    }

    var confirmAnswer = confirm(phrases['sure_to_delete_playlist']);

    if (confirmAnswer === true) {

        var callFile = AJAX_path + "app/deleteSmartPlaylist.php?playlistID=" + playlistID;

        $.get(callFile, function (data) {
            var playlistName = document.querySelector('#smartPlaylist option:checked').text; // Το όνομα της playlist

            if (data.success === true) {
                DisplayMessage('.alert_error', phrases['smart_playlist_deleted'] + ' ' + playlistName);

                // Σβήνει το συγκεκριμένο option από το select #playlist
                document.querySelector("#smartPlaylist option:checked").remove();

            }
            else {
                DisplayMessage('.alert_error', phrases['smart_playlist_not_deleted'] + ' ' + playlistName);
            }
        }, "json");

    }
}

// Σώζει το search query σε smart playlist, σε μορφή json
function saveSmartPlaylist() {
    var playlistID = document.querySelector('#smartPlaylist').value;

    if(playlistID) {
        var searchArray = getSearchArray();
        var searchJsonString = JSON.stringify(searchArray);

        var callFile = AJAX_path + "app/saveSmartPlaylist.php?playlistID=" + playlistID +
            '&searchJsonString=' + encodeURIComponent(searchJsonString);

        $.get(callFile, function (data) {
            var playlistName = document.querySelector('#smartPlaylist option:checked').text; // Το όνομα της playlist

            if (data.success === true) {
                DisplayMessage('.alert_error', phrases['smart_playlist_saved'] + ' ' + playlistName);
            }
            else {
                DisplayMessage('.alert_error', phrases['smart_playlist_not_saved'] + ' ' + playlistName);
            }
        }, "json");
    }
}

// Φορτώνει τις τιμές των search fields
function loadSearchFields(elementID, searchArray)
{
    $('#search_field' + elementID).val(searchArray['search_field']);
    $('#search_text' + elementID).val(searchArray['search_text']);
    $('#search_operator' + elementID).val(searchArray['search_operator']);
    $('#search_equality' + elementID).val(searchArray['search_equality']);
}

// Φορτώνει μία smart playlist και εμφανίζει όλα τα search items
function loadSmartPlaylist()
{
    var playlistID = document.querySelector('#smartPlaylist').value;

    callFile = AJAX_path + "app/loadSmartPlaylist.php?playlistID=" + playlistID;

    $.get(callFile, function (data) {
        var playlistName = document.querySelector('#smartPlaylist option:checked').text; // Το όνομα της playlist

        if (data.success === true) {
            var jsonArray = JSON.parse(data.searchJsonArray);

            // Καθαρίζει τα υπάρχοντα searchRows
            clearSearch();
            $("#searchRow1").remove();

            // Προσθέτει όλες τις γραμμές με τα περιεχόμενα τους
            for(var i=1; i<jsonArray.length; i++) {

                // αν δεν είναι group operator
                if(jsonArray[i]['group_operator'] === null) {
                    addSearchRow();
                    loadSearchFields(i, jsonArray[i]);

                    // Αλλαγή του τύπου των inputs με βάση το search field
                    var theElement = document.querySelector('#searchRow' + i + ' .search_field');
                    checkTheChanges(theElement);

                    // ξαναδιάβασμα των τιμών, γιατί πιθανών μηδενίστηκαν από την αλλαγή των τύπων
                    loadSearchFields(i, jsonArray[i]);

                } else {  // αν είναι group
                    addSearchRow();
                    loadSearchFields(i, jsonArray[i]);
                    addOrAndToGroup(i);

                    // αν είναι AND θέτει την τιμή
                    if(jsonArray[i]['group_operator'] === 'AND') {
                        document.querySelector('#group_operator' + i).selectedIndex='1';
                    }

                }

            }

            // Κάνει click στο searching button για να αρχίσει αμέσως την αναζήτηση
            $('#searching').click();

        }
        else {
            DisplayMessage('.alert_error', phrases['smart_playlist_not_loaded'] + ' ' + playlistName);
        }
    }, "json");
}

// όταν η φόρμα είναι focused
function OnFocusInForm (event) {
    var target = event.target ? event.target : event.srcElement;
    if (target) {
        FocusOnForm=true;
    }
}

// όταν η φόρμα δεν είναι focused
function OnFocusOutForm (event) {
    var target = event.target ? event.target : event.srcElement;
    if (target) {
        FocusOnForm=false;
    }
}

// Ελέγχει αν είναι focus οι φόρμες
function checkFormsFocus() {
    if(VideoLoaded) { // αν έχει φορτωθεί το βίντεο
        checkTheFocus('FormTags');
        checkTheFocus('FormMassiveTags');
        checkTheFocus('SearchForm');
        checkTheFocus('insertPlaylist');
        checkTheFocus('insertSmartPlaylist');
        // checkTheFocus('paths_form');
    }
}

// Καθαρίζει όλες τις τιμές main (τις κάνεις not main) και αφήνει μόνο την μία για το συγκεκριμένο media kind
// Δεν χρησιμοποιείται
function checkMainSelected(formID, checkAll) {
    var currentMediaKind = document.querySelector('#paths_formID'+formID+' #kind').value;

    var founded=0;  // μετράει αν υπάρχει έστω κι ένα main
    var firstFindedMediaKind=null;

    // Παίρνουμε όλα τα form id's που έχουν class paths_form
    var allForms = document.querySelectorAll('.paths_form');

    var FormIDs = [];

    for(var i = 0; i < allForms.length;  i++)
    {
        FormIDs.push(allForms[i].id);
    }

    for(i = 0; i<FormIDs.length; i++) {

        var curID = eval(FormIDs[i].replace('paths_formID',''));  // Παίρνει μόνο το id

        var checkedMediaKind = document.querySelector('#paths_formID' + curID + ' #kind').value;
        var checkedMediaStatus = document.querySelector('#paths_formID' + curID + ' #main').value;

        if(checkedMediaKind === currentMediaKind) {  // Αν είναι στο ίδιο kind με αυτό που αλλάξαμε
            if(!firstFindedMediaKind) {
                firstFindedMediaKind = curID;
            }

            if(checkedMediaStatus === '1') {  // αν είναι main το status
                founded++;
            }

            if(curID !== formID) {  // Αλλάζει όλα σε not main, εκτός από το τρέχον που αλλάξαμε εμείς
                document.querySelector('#paths_formID' + curID + ' #main').selectedIndex='0';
            }
        }

    }

    if(checkAll === false) {
        if(founded === 0) {
            document.querySelector('#paths_formID' + formID + ' #main').selectedIndex = '1';
        }
    }
    else {
        document.querySelector('#paths_formID' + firstFindedMediaKind + ' #main').selectedIndex = '1';
        return firstFindedMediaKind;
    }


}

// Παίρνει τα paths που είναι μέσα σε συγκεκριμένο directory
function getPaths(path) {

    document.querySelector('#displayPaths').innerHTML = '';

    document.querySelector('#chosenPathText').innerText = path;

    var callFile = AJAX_path + "app/getPaths.php?path=" + path;

    $.get(callFile, function (data) {
        for(var i = 1; i<data.length; i ++) {
            // Προσθέτει κάθε directory σαν span
            var newSpan = document.createElement('span');
            newSpan.className = 'thePaths';
            newSpan.innerText = data[i];
            var newPath = null;

            if(data[i] === '..') {  // Αν είναι '..' κόβει το τελευταίο directory από το string
                newPath = path.replace(/\/[^\/]+\/?$/, '')+'/';
            }
            else {
                newPath = path + data[i] + '/';
            }

            newSpan.setAttribute('onclick', 'getPaths("'+newPath+'")' );

            document.querySelector('#displayPaths').append(newSpan);
        }

    }, "json");
}

// Εμφάνιση παράθυρου αναζήτησης διαδρομής
function displayBrowsePath(formID) {
    currentPathFormID=formID;
    getPaths('/');
    $('#browsePathWindow').show();
}

// Κλείσιμο παράθυρου αναζήτησης διαδρομής
function cancelTheBrowse() {
    $('#browsePathWindow').hide();
}

// Εισαγωγή διαδρομής στο σχετικό text input field
function importPath() {
    document.querySelector('#'+currentPathFormID+' #file_path').value = document.querySelector('#chosenPathText').innerText.slice(0, -1);
    $('#browsePathWindow').hide();
}

// Σβήνει όλα τα περιεχόμενα της φόρμας
function resetFormMassiveTags() {
    document.querySelector('#FormMassiveTags').reset();
    document.querySelector('#myImage').innerHTML='';
    document.querySelector('#uploadFile').value='';
}

// Στέλνει την τρέχουσα playlist στην jukebox list
function sendToJukeboxList() {
    $('#progress').show();

    var callFile = AJAX_path + 'app/sendToJukeBox.php?tabID=' + tabID;

    $.get(callFile, function (data) {

        if (data.success === true) {

            DisplayMessage('.alert_error', phrases['playlist_loaded_to_jukebox']);
            $('#progress').hide();

        }
        else {
            DisplayMessage('.alert_error', phrases['problem_to_copy_to_jukebox']);
            $('#progress').hide();
        }

    }, "json");
}

// Προσθέτει μία ψήφο στο τραγούδι
function voteSong(id) {

    var callFile = AJAX_path + 'app/voteSong.php?id=' + id;

    $.get(callFile, function (data) {

        if (data.success === true) {
            DisplayMessage('.alert_error', phrases['vote_accepted']);
        }
        else {
            DisplayMessage('.alert_error', phrases['vote_not_accepted']);
        }

    }, "json");
}

// Ανεβάζει ένα αρχείο
function uploadFile(files) {
    var selectedFile = document.getElementById('uploadSQLFile').files[0];

    var myMime = selectedFile.type;

    var f = files[0];

    var reader = new FileReader();

    // Called when the file content is loaded, e.target.result is
    // The content
    reader.onload = function (e) {
        // console.log(e.target.result);

        var myFile = e.target.result;

        $.ajax({
            // Your server script to process the upload
            url: AJAX_path + 'app/uploadFile.php',
            type: 'POST',

            // Form data
            data: {
                myFile: myFile
            }
        });

    };

    // Start reading asynchronously the file
    reader.readAsText(f);
}

/**
 * Ενημερώνει το download path
 *
 * @param pathName {string} To path name του σχετικού row στο download_paths, που θέλουμε να ενημερώσουμε
 */
function updateDownloadPath(pathName)
{
    var filePath = document.querySelector('#' + pathName + ' #file_path').value;

    console.log(filePath);

    $.ajax({
        url: AJAX_path + 'app/updateDownloadPath.php',
        type: 'GET',

        // Form data
        data: {
            pathName: pathName,
            filePath: filePath
        },
        dataType: "json",
        success: function (data) {
            if (data.success === true) {
                $("#message_" + pathName).addClassDelay("success", 3000);
            } else {
                $("#message_" + pathName).addClassDelay("failure", 3000);
            }
        }

    });
}

// Εμφανίζει το παράθυρο επιλογής του sleep timer
function displayTheSleepTimer()
{
    $('#insertSleepTimerWindow').show();
}

// Εξαφανίζει το παράθυρο επιλογής του sleep timer
function cancelTheSleepTimer()
{
    $('#insertSleepTimerWindow').hide();
}

// Αρχίζει την αντίστροφη μέτρηση για το sleep
function startSleepTimer()
{
    var sleepMinutes = document.querySelector('#sleepMinutes').value;

    var timeInSeconds = sleepMinutes*60;

    clearInterval(theTimer);

    theTimer = setInterval(function () {
        timeInSeconds--;

        var timeInMinutesAndSeconds = seconds2MinutesAndSeconds(timeInSeconds);
        document.querySelector('#theSleepTimer').innerText = timeInMinutesAndSeconds['minutes'] + ':' + timeInMinutesAndSeconds['seconds'];

        if (timeInSeconds === 0) {
            clearInterval(theTimer);
            if (!myVideo.paused) {
                myVideo.pause();
                displayPlayButton();
            }
        }

    }, 1000);

    $('#insertSleepTimerWindow').hide();
}

// Εμφανίζει/εξαφανίζει το resultsContainter
function toggleResultsContainer()
{
    $('.o-resultsContainer').toggleClass('isHidden isVisible');

    BlinkElement.stop(); // Σταματάει το αναβόσβησμα του εικονίδιου
}

// Κάνει ένα element να αναβοσβήνει
var BlinkElement =
{
    elementName: null,
    blinkInterval: null,

    // Αρχίζει το αναβόσβησμα
    start: function(elementName)
    {
        this.elementName = elementName;

        clearInterval(this.blinkInterval);

        this.blink = this.blink.bind(this);

        this.blinkInterval = setInterval(this.blink, 1000);
    },

    // Σταματάει το αναβόσβησμα
    stop: function()
    {
        clearInterval(this.blinkInterval);
        $(this.elementName).fadeTo('fast', 1);
    },

    // Το εφέ του αναβοσβήσματος
    blink: function ()
    {
        $(this.elementName).stop().fadeTo('fast', 0.1).fadeTo('fast', 1);
    }
};



