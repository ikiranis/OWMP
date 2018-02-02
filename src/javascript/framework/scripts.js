/**
 * File: scripts.js
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 20/05/16
 * Time: 19:44
 *
 * Framework javascript controls και functions
 *
 */


/**
 * Εμφάνιση μυνήματος σε συγκεκριμένο element
 *
 * @param element
 * @param error
 */
function DisplayMessage (element, error, color) {
    var defaultClasses = 'alert_error alert ml-auto mr-auto fixed-bottom';
    $(element).removeClass().addClass(defaultClasses + ' alert-' + color);
    $(element).text(error);
    $(element).stop().show(0).delay(5000).hide(0);
}

/**
 * Toggle two classes, delay and then toggle again
 *
 * @param element
 * @param firstClass
 * @param secondClass
 * @param delay
 */
function toggleClassWithDelay(element, firstClass, secondClass, delay)
{
    $(element).toggleClass(firstClass + ' ' + secondClass);

    window.setTimeout(function() {
        $(element).toggleClass(secondClass + ' ' + firstClass);
    }, delay);
}

// Ενημερώνει την υπάρχουσα εγγραφή στην βάση στο table options, ή εισάγει νέα εγγραφή
function updateOption(id) {
    var optionIDElem = $("#OptionID" + id);

    var option_name = optionIDElem.find('input[name="option_name"]').val();
    var option_value = optionIDElem.find('input[name="option_value"]').val();

    if ($('#options_formID'+id).valid()) {

        $.ajax({
            url: AJAX_path+"framework/updateOption",
            type: 'GET',
            data: {
                id: id,
                option_name: option_name,
                option_value: encodeURIComponent(option_value)
            },
            dataType: "json",
            success: function (data) {
                if (data.success === 'true') {
                    $("#messageOptionID" + id).addClassDelay("success", 3000);
                }
                else {
                    $("#messageOptionID" + id).addClassDelay("failure", 3000);
                }
            }
        });

    }

}

/**
 * Διαβάζει τις τιμές των search fields και τις αποθηκεύει στο GlobalSearchArray
 *
 * @param numberOfFields
 */
function readSearchFields(numberOfFields) {
    for(var i=1;i<=numberOfFields;i++){
        GlobalSearchArray[i]= {
            'search_field': $('#search_field' + i).val(),
            'search_text': $('#search_text' + i).val(),
            'search_operator': $('#search_operator' + i).val(),
            'search_equality': $('#search_equality' + i).val()
        }
    }
}

/**
 * Γράφει στην φόρμα τις τιμές των search fields που ήταν αποθηκευμένες στο GlobalSearchArray
 *
 * @param numberOfFields
 */
function writeSearchFields(numberOfFields) {
    for(var i=1;i<=numberOfFields;i++){
        $('#search_field' + i).val(GlobalSearchArray[i]['search_field']);
        $('#search_text' + i).val(GlobalSearchArray[i]['search_text']);
        $('#search_operator' + i).val(GlobalSearchArray[i]['search_operator']);
        $('#search_equality' + i).val(GlobalSearchArray[i]['search_equality']);
    }
}

/**
 * Εμφανίζει τα περιεχόμενα του κεντρικού παραθύρου με ajax
 *
 * @param page
 * @param offset
 * @param step
 */
function DisplayWindow(page, offset, step) {
    // console.log(curNavItem+ ' '+ NavLength);
    var callFile=AJAX_path+"framework/displayWindow?page="+page+"&offset="+offset+"&step="+step+'&tabID='+tabID;

    // Αν target σελίδα δεν είναι η 1
    if(page !== 1) {

        var searchElem = $('#search');

        // Αν το #search δεν είναι κενό, άρα είμασταν πριν στην 1
        if(!searchElem.length == 0) {

            // διαβάζουμε τις τιμές των search fields
            readSearchFields(getNumberOfSearchRows());

            // αντιγράφουμε τον html κώδικα που βρίσκεται μέσα στο #search, στην μεταβλητή SearchHTML
            SearchHTML = searchElem.html();
        }

        // Αν το #ChooseMediaKind δεν είναι κενό, άρα είμασταν πριν στην 1
        if(!$('#ChooseMediaKind').length == 0) {
            MediaKindChosen=document.querySelector('#ChooseMediaKind select[name=mediakind]').value;
        }

        var playlistContentElem = $('#playlist_content');

        // Αν το #playlist_content δεν είναι κενό, άρα είμασταν πριν στην 1
        if(!playlistContentElem.length == 0)
        // αντιγράφουμε τον html κώδικα που βρίσκεται μέσα στο #playlist_content, στην μεταβλητή PlaylistContainerHTML
            PlaylistContainerHTML = playlistContentElem.html();

    }


    if(page !== CurrentPage) {
        // όταν ανοίγει το section article
        $('section article').load(callFile, function () {

            // Αν εμφανίζουμε την σελίδα 1
            if (page === 1) {

                // εμφανίζουμε τις μεταβλητές που έχουμε σώσει στα αντίστοιχα divs
                $('#search').html(SearchHTML);
                writeSearchFields(getNumberOfSearchRows());
                document.querySelector('#ChooseMediaKind select[name=mediakind]').value = MediaKindChosen;
                $('#playlist_content').html(PlaylistContainerHTML);
                checkSearchFieldChanges();  // επανεκίννηση του έλεγχου αλλαγών στα search fields
                checkFormsFocus();
            }

            CurrentPage = page;

            for (var i = 1; i <= NavLength; i++)   // Κάνει όλα τα nav πεδία inactive
                $('#navID' + i).removeClass('active');

            $('#navID' + page).addClass('active');   // κάνει το page active
        });
    }
}

/**
 * Κλείνει το παράθυρο της βοήθειας
 */
function closeHelp()
{
    $('#helpContainer').modal('hide');
}

/**
 * Κλείνει το παράθυρο container
 *
 * @param container
 */
function closeWindow(container)
{
    $(container).toggleClass('isVisible isHidden');
}


/**
 * Eμφανίζει box με text το helpText
 *
 * @param helpText
 */
function getHelp(helpText) {
    document.querySelector('#helpText').innerHTML = phrases[helpText];

    $('#helpContainer').show();
}

/**
 * Κάνει έλεγχο της τρέχουσας έκδοσης της εφαρμογής
 */
function checkCurrentVersion() {
    $.ajax({
        url: ParrotVersionFile,
        type: 'GET',
        dataType: "json",
        success: function (data) {
            // αν η έκδοση της εγκατεστημένης εφαρμογής δεν ταιριάζει με την τρέχουσα, βγάζει μήνυμα
            if(AppVersion !== data.app_version)
                $("#checkCurrentVersion").html(phrases['need_update']+': '+data.app_version+'&nbsp;<a href='+changeLogUrl+'>('+phrases['change_log']+')</a>');
        }
    });
}

/**
 * Στέλνει kill command στην βάση για να σταματήσει το php script που τρέχει
 */
function sendKillCommand() {
    // console.log(runningYoutubeDownload);

    var killKommandImgElem = $("#killCommand_img");

    if(!runningYoutubeDownload) {
        killKommandImgElem.hide();

        $.ajax({
            url: AJAX_path + "framework/sendKillCommand",
            type: 'GET',
            dataType: "json",
            success: function (data) {
                if (data.success) {
                    console.log('Killed');
                }
            }
        });

    } else {
        killKommandImgElem.hide();
        runningYoutubeDownload=false;
    }

}

/**
 * Ψάχνει και καθαρίζει την βάση από προσωρινά tables που δεν χρησιμοποιούνται πλέον
 */
function garbageCollection() {
    $.ajax({
        url: AJAX_path+"framework/garbageCollection",
        type: 'GET',
        data: {
            tabID: tabID
        },
        dataType: "json",
        success: function (data) {
            if (data.success === true) {
                console.log('Garbage collected');
            }
        }
    });
}

/**
 * Κάνει το update της εφαρμογής
 */
function startTheUpdate() {

    $.ajax({
        url: AJAX_path+'framework/updateApp',
        type: 'GET',
        dataType: "json",
        success: function (data) {
            if (data.success === true) {
                DisplayMessage('.alert_error', 'AjaxRouting Updated', 'success');
            } else {
                DisplayMessage('.alert_error', 'AjaxRouting Not Updated', 'danger');
            }
        }
    });
}

/**
 * Δημιουργία a href DOM element και αυτόματο (ή όχι) download
 *
 * @param: string fullPath = το πλήρες path μαζί με το filename του αρχείου
 * @param: string filename = σκέτο το όνομα του αρχείου
 * @param: string hrefText = το κείμενο που θα εμφανιστεί
 * @param: Bool autoDownload = true για να αρχίσει να κατεβάζει αυτόματα το αρχείο
 * @return: DOM object = To a href που θα εμφανίσει
 */
function getDownloadLink(fullPath, filename, hrefText) {

    var downloadText = document.createElement('a');
    downloadText.href = fullPath;
    downloadText.innerHTML = hrefText;
    downloadText.target = '_blank';
    downloadText.download = filename;

    return downloadText;

}

/**
 * Παίρνει backup της βάσης
 */
function startTheBackup() {
    var confirmAnswer=confirm(phrases['sure_to_backup']);

    if (confirmAnswer === true) {

        if(localStorage.syncPressed === 'false') {  // Έλεγχος αν δεν έχει πατηθεί ήδη
            localStorage.syncPressed = 'true';

            clearResultsContainer();
            ProgressAnimation.init(true);
            ProgressAnimation.setProgressPercent(0);
            var resultsContainerElem = $('.o-resultsContainer_text');

            syncRunning = true;

            // Κοιτάει για το progress κάθε 5 λεπτά και το τυπώνει
            var syncInterval = setInterval(function () {
                checkProgress();
            }, 5000);

            $.ajax({
                url: AJAX_path + 'framework/backupDatabase',
                type: 'GET',
                dataType: "json",
                success: function(data) {
                    if (data.success === true) {

                        var path = AJAX_path + "app/serveFile?path=" + data.fullPath;

                        // Δημιουργία a href element και αυτόματο download
                        var downloadText = getDownloadLink(path, data.filename, path);

                        displayResultsIcon();
                        resultsContainerElem.append('<br>');
                        resultsContainerElem.append('<p>' + phrases['backup_success'] + '</p>');
                        resultsContainerElem.append(downloadText);
                        ProgressAnimation.kill();
                        syncRunning = false;
                        localStorage.syncPressed = 'false';
                        $('.syncButton').prop('disabled', false);
                        clearInterval(syncInterval);

                    } else {
                        displayResultsIcon();
                        resultsContainerElem.append('<br>');
                        resultsContainerElem.append('<p>' + phrases['backup_failure'] + '</p>');
                        ProgressAnimation.kill();
                        syncRunning = false;
                        localStorage.syncPressed = 'false';
                        $('.syncButton').prop('disabled', false);
                        clearInterval(syncInterval);
                    }
                }
            });

        }
    }
}

/**
 * Κάνει restore της βάσης από ένα αρχείο backup
 */
function restoreTheBackup() {
    if(myFile!=='') {
        var confirmAnswer = confirm(phrases['sure_to_restore']);

        if (confirmAnswer === true) {
            if(localStorage.syncPressed === 'false') {  // Έλεγχος αν δεν έχει πατηθεί ήδη
                localStorage.syncPressed = 'true';

                clearResultsContainer();
                ProgressAnimation.init(true);
                ProgressAnimation.setProgressPercent(0);
                var resultsContainerElem = $('.o-resultsContainer_text');

                syncRunning = true;

                // Κοιτάει για το progress κάθε 5 λεπτά και το τυπώνει
                var syncInterval = setInterval(function () {
                    checkProgress();
                }, 5000);

                $.ajax({
                    url: AJAX_path + 'framework/restoreDatabase',
                    type: 'GET',
                    dataType: "json",
                    success: function(data) {
                        if (data.success === true) {

                            displayResultsIcon();
                            resultsContainerElem.append('<br>');
                            resultsContainerElem.append(phrases['restore_success']);
                            ProgressAnimation.kill();
                            syncRunning = false;
                            localStorage.syncPressed = 'false';
                            $('.syncButton').prop('disabled', false);
                            clearInterval(syncInterval);

                        }
                        else {
                            displayResultsIcon();
                            resultsContainerElem.append('<br>');
                            resultsContainerElem.append(phrases['restore_failure']);
                            ProgressAnimation.kill();
                            syncRunning = false;
                            localStorage.syncPressed = 'false';
                            $('.syncButton').prop('disabled', false);
                            clearInterval(syncInterval);
                        }
                    }
                });

            }
        }
    } else {
        DisplayMessage('.alert_error', phrases['file_not_upload'], 'danger');
    }
}

/**
 * Ελέγχει και εμφανίζει το progress
 */
function checkProgress()
{
    $.ajax({
        url: AJAX_path + "framework/getProgress",
        type: 'GET',
        dataType: "json",
        success: function(progressData) {
            if (progressData.success === true) {
                if(progressData.progressInPercent>97 && localStorage.syncPressed==='true') {
                    DisplayWindow(3, null, null);
                }

                // TODO να δω αν χρειάζεται όντως αυτός ο έλεγχος
                // if($('#o-resultsContainer').length!==0 && localStorage.syncPressed=='true') {
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

/**
 * Καθαρισμός του results container
 */
function clearResultsContainer()
{
    document.querySelector('.o-resultsContainer_text').innerHTML = '';
}

/**
 * Εμφανίζει το εικονίδιο για τα results
 */
function displayResultsIcon()
{
    $('.o-resultsContainer_iconContainer').toggleClass('isHidden', 'isVisible');
    BlinkElement.start('.o-resultsContainer_iconContainer');
}

/**
 * Εξαφανίζει το εικονίδιο για τα results
 */
function hideResultsIcon()
{
    $('.o-resultsContainer_iconContainer').toggleClass('isVisible', 'isHidden');
}

/**
 * Εμφανίζει το icon του kill command
 */
function displayKillCommandIcon()
{
    $('.o-resultsContainer_killCommandContainer').toggleClass('isHidden isVisible');
}

/**
 * Εξαφανίζει το icon του kill command
 */
function hideKillCommandIcon()
{
    $('.o-resultsContainer_killCommandContainer').toggleClass('isVisible isHidden');
}

// ************************************
// On load
$(function(){

    // Έναρξη των validates
    startValidates();

    // Εμφανίζει την ώρα
    getTime('#timetext');

    // Εμφανίζει συνεχώς την ώρα
    setInterval(function(){
        getTime('#timetext');
        if(checkFullscreen()) getTime('#overlay_time');
    }, 1000);

    // Ελέγχει τον χρόνο που βρίσκεται το βίντεο και όταν περάσει το όριο εκτελεί συγκεκριμένες ενέργειες
    $("#myVideo").on("timeupdate", function(event){
            curTimePercent = (this.currentTime/this.duration)*100; // O τρέχον χρόνος σε ποσοστό επί του συνολικού

            // Όταν περάσει το TimePercentTrigger ενημερώνει την βάση
            if( (curTimePercent>TimePercentTrigger) && (TimeUpdated === false) ) {
                updateVideoPlayed();
                TimeUpdated=true;

                // If we want to convert audio to lower bitrate
                if(localStorage.convertToLowerBitrate === 'true') {
                    // Preloading song
                    getNextVideoID(0, 'next', true);
                }
            }

            //Μετατροπή του track time σε λεπτά και δευτερόλεπτα
            timeInMinutesAndSeconds = seconds2MinutesAndSeconds(this.currentTime)['minutes']+' : ' +
                seconds2MinutesAndSeconds(this.currentTime)['seconds'];

            // Εμφάνιση του τρέχοντα track time
            if(checkFullscreen()) { // όταν είναι σε full screen
                $('#jsOverlayTrackTime').html(timeInMinutesAndSeconds);
                $('.o-trackTime--overlay__range ').val(curTimePercent);
            } else {   // όταν δεν είναι σε full screen
                $('#jsTrackTime').html(timeInMinutesAndSeconds);
                $('.o-trackTime__range').val(curTimePercent);
            }

    });

    // Ελέγχει αν είναι focus οι φόρμες
    checkFormsFocus();

    // Έλεγχος πατήματος πλήκτρων
    getShortcuts(window);

    // Έναρξη των ελέγχων για όταν γίνονται αλλαγές στα search fields
    checkSearchFieldChanges();

    // Ελέγχει την τρέχουσα έκδοση
    checkCurrentVersion();

    // Έλεγχος για garbage collection
    setInterval(garbageCollection, 600000);

    document.addEventListener('touchmove', displayFullscreenControls, false);
});



