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


/**
 * Ενημερώνει την υπάρχουσα εγγραφή στην βάση στο table paths, ή εισάγει νέα εγγραφή
 *
 * @param id
 */
function updatePath(id)
{
    // Παίρνουμε όλα τα form id's που έχουν class paths_form
    // var allForms = document.querySelectorAll('.paths_form');
    // var FormIDs = [];
    //
    // for(var i = 0; i < allForms.length;  i++) {
    //     FormIDs.push(allForms[i].id);
    // }

    var curID = id;

    var pathIDElem = $("#PathID" + curID);

    var file_path = pathIDElem.find('input[name="file_path"]').val();
    var kind = pathIDElem.find('select[name="kind"]').val();

    if ($('#paths_formID' + curID).valid()) {

        $.ajax({
            url: AJAX_path+"app/updatePath",
            type: 'GET',
            data: {
                id: curID,
                file_path: file_path,
                kind: kind
            },
            dataType: "json",
            success: function (data) {
                var updatedID = data.id;

                if (data.success === true) {
                    if (updatedID === '0') {   // αν έχει γίνει εισαγωγή νέας εγγρσφής, αλλάζει τα ονόματα των elements σχετικά
                        PathKeyPressed = false;
                        LastInserted = data.lastInserted;

                        $("#PathID0").prop('id', 'PathID' + LastInserted);

                        var PathID = $("#PathID" + LastInserted);
                        PathID.find('form').prop('id','paths_formID'+ LastInserted);
                        PathID.find('input[name="file_path"]').attr("onclick", "displayBrowsePath(" + LastInserted + ")");
                        PathID.find('input[name="update_path"]').attr("onclick", "updatePath(" + LastInserted + ")");
                        PathID.find('input[name="delete_path"]').attr("onclick", "deletePath(" + LastInserted + ")");
                        PathID.find('input[id^="messagePathID"]').prop('id', 'messagePathID' + LastInserted);
                        $("#messagePathID" + LastInserted).addClassDelay("success", 3000);

                    } else {
                        $("#messagePathID" + updatedID).addClassDelay("success", 3000);
                    }
                } else {
                    $("#messagePathID" + updatedID).addClassDelay("failure", 3000);
                }
            }
        });

    }

}

/**
 * Σβήνει την εγγραφή στο paths
 *
 * @param id
 */
function deletePath(id) {
    $.ajax({
        url: AJAX_path+"app/deletePath",
        type: 'GET',
        data: {
            id: id
        },
        dataType: "json",
        success: function (data) {
            if(data.success === true) {
                var PathID = $("#PathID0");
                var PathWithID = $("#PathID" + id);

                $("#messagePathID"+id).addClassDelay("success",3000);

                var myClasses = PathWithID.find('input[name=delete_path]').classes();   // Παίρνει τις κλάσεις του delete_path

                if(!myClasses[2]) {   // Αν δεν έχει κλάση dontdelete σβήνει το div
                    PathWithID.remove();
                }
                else {   // αλλιώς καθαρίζει μόνο τα πεδία
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
            } else {
                $("#messagePathID"+id).addClassDelay("failure",3000);
            }
        }
    });

}

/**
 * Εισάγει νέα div γραμμή αντιγράφοντας την τελευταία και μηδενίζοντας τις τιμές που είχε η τελευταία
 */
function insertPath() {

    if(!PathKeyPressed) {

        // clone last div row
        $('div[id^="PathID"]:last').clone().insertAfter('div[id^="PathID"]:last').prop('id','PathID0');

        var PathID = $("#PathID0");
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

/**
 * Παίρνει τα paths που είναι μέσα σε συγκεκριμένο directory
 *
 * @param path
 */
function getPaths(path) {
    document.querySelector('#displayPaths').innerHTML = '';

    document.querySelector('#chosenPathText').innerText = path;

    $.ajax({
        url: AJAX_path + "app/getPaths",
        type: 'GET',
        data: {
            path: path
        },
        dataType: "json",
        success: function (data) {
            for(var i = 1; i<data.length; i ++) {
                // Προσθέτει κάθε directory σαν span
                var newSpan = document.createElement('span');
                newSpan.className = 'thePaths';
                newSpan.innerText = data[i];
                var newPath = null;

                if(data[i] === '..') {  // Αν είναι '..' κόβει το τελευταίο directory από το string
                    newPath = path.replace(/\/[^\/]+\/?$/, '')+'/';
                } else {
                    newPath = path + data[i] + '/';
                }

                newSpan.setAttribute('onclick', 'getPaths("' + newPath + '")' );

                document.querySelector('#displayPaths').append(newSpan);
            }
        }
    });
}

/**
 * Εμφάνιση παράθυρου αναζήτησης διαδρομής
 *
 * @param formID
 */
function displayBrowsePath(formID) {
    currentPathFormID = formID;
    getPaths('/');
    $('#browsePathWindow').show();
}

/**
 * Κλείσιμο παράθυρου αναζήτησης διαδρομής
 */
function cancelTheBrowse() {
    $('#browsePathWindow').hide();
}

/**
 * Εισαγωγή διαδρομής στο σχετικό text input field
 */
function importPath() {
    document.querySelector('#'+currentPathFormID+' #file_path').value = document.querySelector('#chosenPathText').innerText.slice(0, -1);
    $('#browsePathWindow').hide();
}

/**
 * Ενημερώνει το download path
 *
 * @param pathName {string} To path name του σχετικού row στο download_paths, που θέλουμε να ενημερώσουμε
 */
function updateDownloadPath(pathName)
{
    var filePath = document.querySelector('#' + pathName + ' #file_path').value;

    $.ajax({
        url: AJAX_path + 'app/updateDownloadPath',
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

/**
 * Εμφανίζει rating αστεράκια στο elem
 *
 * @param rating
 * @param elem
 */
function ratingToStars(rating,elem) {
    rating = parseInt(rating);

    $(elem).html('');

    for(var i=1;i<=rating;i++){
        var img = document.createElement("img");
        img.src = "img/star.png";
        var src = document.querySelector(elem);
        src.appendChild(img);
    }

}

/**
 * Αλλάζει όλα τα checkItems checkboxes με την τιμή του checkAll
 *
 * @param checkAll
 * @param checkItems
 */
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

// TODO να προστεθεί κουμπί που να κάνεις toggle την εμφάνιση ή όχι στην αρχή του κάθε τραγουδιού του overlay
/**
 * Εμφανίζει το div με τα metadata όταν είναι σε fullscreen
 *
 * @param toggle
 */
function showFullScreenVideoTags(toggle) {

    if (checkFullscreen()) {  // Αν είναι σε fullscreen
        if(toggle !== undefined) { // Όταν έχει πατηθεί το I και ζητάμε αλλαγή του OverlayAllwaysOn
            if (!OverlayON) {
                if (toggle === 'on') {
                    $('#overlay').show();
                    localStorage.OverlayAllwaysOn = 'true';
                } else {
                    $('#overlay').hide();
                    localStorage.OverlayAllwaysOn = 'false';
                }
            }
        } else {
            if (localStorage.OverlayAllwaysOn === 'false') {  // αν δεν εχει πατηθεί να πρέπει να είναι allways on
                if (!OverlayON) {  // αν δεν είναι on ήδη
                    OverlayON = true;
                    $('#overlay').stop().show().delay(5000).hide(0);
                    OverlayON = false;
                }
            } else {
                $('#overlay').show();
            }
        }

    } else {
        $('#overlay').hide();
        $('#overlay_volume').hide();
    }

}

/**
 * Κάνει active το τρέχον row στην playlist
 *
 * @param id
 */
function makePlaylistItemActive(id) {
    $('.track').removeClass('is-active');  // Κάνει unactive όλα τα rows
    var fileIDElem = $("#fileID" + id);

    if(fileIDElem.length) { // Αν υπάρχει στην λίστα το συγκεκριμένο row το κάνει active
        fileIDElem.addClass('is-active');

        // if (!checkFullscreen ()) // Αν δεν είναι σε fullscreen, αλλιώς λειτουργεί περιέργως
        //     document.querySelector("#fileID"+id).scrollIntoView();  // κάνει scrolling στο συγκεκριμένο row
    }

}

/**
 * Ενημερώνει τα tags του κομματιού
 *
 * @param key_rating
 */
function update_tags(key_rating) {
    var songID = $('#FormTags #songID').val();
    var song_name = $('#FormTags #title').val();
    var artist = $('#FormTags #artist').val();
    var genre = $('#FormTags #genre').val();
    var song_year = $('#FormTags #year').val();
    var album = $('#FormTags #album').val();
    var rating;
    if(!key_rating) {
        rating = $('#FormTags #rating').val();
    } else {
        rating = key_rating;
    }  // Αν έχει πατηθεί νούμερο για βαθμολογία
    var live = $('#FormTags #live').val();

    // Αν το songID είναι ίσο με το currentID, σε περίπτωση από κάποιο κόλλημα πάει να γράψει σε λάθος τραγούδι
    if(parseInt(songID) === parseInt(currentID)) {
        $.ajax({
            url: AJAX_path + "app/updateTags",
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
            success: function (data) {
                if (data.success === true) {

                    $("#message").addClassDelay("success", 3000);

                    var fileCurrentID = $("#fileID" + currentID);

                    if (fileCurrentID.length) {   // Ενημερώνει τα σχετικά πεδία στην λίστα
                        fileCurrentID.find('.song_name').text(song_name);
                        fileCurrentID.find('.artist').text(artist);
                        fileCurrentID.find('.genre').text(genre);
                        fileCurrentID.find('.album').text(album);
                        fileCurrentID.find('.song_year').text(song_year);
                        fileCurrentID.find('.rating').text(rating);
                    }


                    if (key_rating) {   // Αν έχει πατηθεί νούμερο για βαθμολογία
                        $('#rating').val(rating);
                        $('#rating_output').val(rating);
                    }

                    FocusOnForm = false;

                    // Βάζει τα metadata για εμφάνιση όταν είναι σε fullscreen
                    $('#overlay_artist').html(artist);
                    $('#overlay_song_name').html(song_name);
                    $('#overlay_song_year').html(song_year);
                    $('#overlay_album').html(album);
                    $('#overlay_live').html(liveOptions[live]);

                    ratingToStars(rating, '#overlay_rating');

                    showFullScreenVideoTags();


                } else {
                    $("#message").addClassDelay("failure", 3000);
                }
            }

        })
    } else {
        console.log('BAD ID!!!!!!   SongID: (' + songID + ') currentID: (' + currentID + ')');
    }
}

/**
 * Ενημερώνει τα play count και date last played
 */
function updateVideoPlayed() {
    $.ajax({
        url: AJAX_path+"app/updateTimePlayed",
        type: 'GET',
        data: {
            id: currentID
        },
        dataType: "json",
        success: function (data) {
            if (data.success === true) {

                $('#play_count').val(data.play_count);     // Ενημερώνει τα σχετικά input πεδία
                $('#date_played').val(data.date_last_played);

                var fileCurrentID = $("#fileID" + currentID);

                if(fileCurrentID.length) {    // Ενημερώνει τα σχετικά πεδία στην λίστα
                    fileCurrentID.find('.play_count').text(data.play_count);
                }

                $('#overlay_play_count').html(data.play_count);
            }
        }
    });
}

/**
 * Αναζήτηση για διπλές εγγραφές και εμφάνιση τους
 *
 * @param offset {int} Το τρέχον σημείο στην λίστα
 * @param step {int} Ο αριθμός των εγγραφών που θα εμφανίσει
 * @param firstTime {bool} True όταν τρέχει για πρώτη φορά η αναζήτηση
 */
function findDuplicates(offset, step, firstTime) {
    ProgressAnimation.init(false);

    $.ajax({
        url: AJAX_path + "app/searchPlaylist",
        type: 'GET',
        data: {
            duplicates: 'true',
            firstTime: firstTime,
            offset: offset,
            step: step,
            tabID: tabID
        },
        success: function (data) {
            if (data) {
                $('#playlist_container').html(data);
                if(!syncRunning) {
                    ProgressAnimation.kill();
                }
                $('#search').modal('hide');
            } else {
                $('#playlist_container').html('Δεν βρέθηκαν εγγραφές');
                if(!syncRunning) {
                    ProgressAnimation.kill();
                }
                $('#search').modal('hide');
            }
        }
    });
}

/**
 * Το σύνολο των γραμμών div μέσα στην φόρμα #SearchForm
 *
 * @returns {*}
 */
function getNumberOfSearchRows()
{
    var searchFormID = $('#SearchForm');

    // Το σύνολο των γραμμών div μέσα στην φόρμα #SearchForm
    var searchRows = searchFormID.children('div').length;
    // Το σύνολο των γραμμών .groupRow στην φόρμα #SearchForm
    var groupRows = searchFormID.children('.groupRow').length;
    // TODO more test... maybe need to remove 2 from searchRows
    searchRows = (searchRows-groupRows)-1;

    return searchRows;
}

/**
 * Διαβάζει την φόρμα και επιστρέφει τα πεδία αναζήτησησης σε μορφή array
 *
 * @returns {Array}
 */
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

/**
 * αναζήτηση στην playlist
 *
 * @param offset
 * @param step
 * @param firstTime
 * @param search
 */
function searchPlaylist(offset, step, firstTime, search) {
    ProgressAnimation.init(false);
    var jsonArray;

    if(!search) { // Αν δεν υπάρχει ήδη json search array, διαβάζουμε την φόρμα
        var searchArray = getSearchArray();
        jsonArray = JSON.stringify(searchArray);
    } else {
        jsonArray = JSON.stringify(search);
    }

    var mediaKind = document.querySelector('#ChooseMediaKind select[name=mediakind]').value;

    currentPlaylistID = '1';

    $.ajax({
        url: AJAX_path + "app/searchPlaylist",
        type: 'GET',
        data: {
            jsonArray: jsonArray,
            offset: offset,
            step: step,
            firstTime: firstTime,
            mediaKind: mediaKind,
            tabID: tabID
        },
        success: function (data) {
            if (data) {
                $('#playlist_container').html(data);
                if(!syncRunning) {
                    ProgressAnimation.kill();
                }
                $('#search').modal('hide');
            }
            else {
                $('#playlist_container').html('Δεν βρέθηκαν εγγραφές');
                if(!syncRunning) {
                    ProgressAnimation.kill();
                }
                $('#search').modal('hide');
            }
        }
    });
}

/**
 * Φορτώνει μια manual playlist
 *
 * @param offset
 * @param step
 */
function playMyPlaylist(offset, step) {
    var playlistID = document.querySelector('#playlist').value;

    if(playlistID === '') {  // Αν δεν έχει επιλεχτεί μια playlist
        DisplayMessage('.alert_error', phrases['you_have_to_choose_playlist']);
        return;
    }

    ProgressAnimation.init(false);

    // Αντιγραφή της manual playlist στην current playlist
    $.ajax({
        url: AJAX_path + "app/loadPlaylist",
        type: 'GET',
        data: {
            playlistID: playlistID,
            tabID: tabID
        },
        dataType: "json",
        success: function (data) {
            // var playlistName=document.querySelector('#playlist option:checked').text; // Το όνομα της playlist

            if (data.success === true) {

                // Κάνει search και φορτώνει τα περιεχόμενα της manual playlist
                $.ajax({
                    url: AJAX_path + 'app/searchPlaylist',
                    type: 'GET',
                    data: {
                        tabID: tabID,
                        firstTime: 'true',
                        loadPlaylist: 'true',
                        offset: offset,
                        step: step
                    },
                    success: function (data) {
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
                    }
                });

            } else {
                DisplayMessage('.alert_error', phrases['playlist_loading_problem']);
            }
        }
    });
}

/**
 * Προσθέτει μια γραμμή searchRow
 */
function addSearchRow()
{
    var lastElementID = $('div[id^="searchRow"]:last').prop('id'); // To id του τελευταίου searchRow
    var newID = parseInt(lastElementID.match(/[0-9]+/))+1;
    var newElementID = 'searchRow' + newID; // To id του νέου searchRow

    // Προσθέτει το νέο searchRow αντιγράφοντας το searchRow0 και το κάνει visible
    $('#searchRow0').clone().insertAfter('div[id^="searchRow"]:last').prop('id',newElementID);
    var theNewElementID = $('#' + newElementID);
    theNewElementID.toggleClass('isHidden', 'isVisible');

    // Αλλάζει τα id όλων των child elements
    $('.search_field', '#'+newElementID).prop('id', 'search_field' + newID ).prop('name', 'search_field' + newID );
    $('.search_equality', '#'+newElementID).prop('id', 'search_equality' + newID ).prop('name', 'search_equality' + newID );
    $('.search_text', '#'+newElementID).prop('id', 'search_text' + newID ).prop('name', 'search_text' + newID );
    $('.search_operator', '#'+newElementID).prop('id', 'search_operator' + newID ).prop('name', 'search_operator' + newID );
    $('.search_text_group', '#'+newElementID).prop('id', 'search_text_group' + newID ).prop('name', 'search_text_group' + newID );
    theNewElementID.find('label[for^="search_field"]').prop('for', 'search_field' + newID );
    theNewElementID.find('label[for^="search_text"]').prop('for', 'search_text' + newID );

    // Αλλάζει τις τιμές στις onclick functions
    theNewElementID.find('span[id="jsAddGroup"]').attr("onclick", "addOrAndToGroup("+newID+")");
    theNewElementID.find('span[id="jsRemoveSearchRow"]').attr("onclick", "removeSearchRow("+newID+")");

    checkSearchFieldChanges();  // επανεκίννηση του έλεγχου αλλαγών στα search fields

}

/**
 * Αφαιρεί μία γραμμή searchRow
 *
 * @param elementID
 */
function removeSearchRow(elementID)
{
    if(elementID !== 1) { // αν δεν είναι η πρώτη γραμμή
        $("#searchRow" + elementID).remove();
    }
}

/**
 * Αφαιρεί το group Row
 *
 * @param elementID
 */
function removeGroupRow(elementID) {
    $('#searchRow-' + elementID).remove();
}

/**
 * Προσθέτει OR/AND στο group πεδίων
 *
 * @param elementID
 */
function addOrAndToGroup(elementID)
{
    var currentElement = document.querySelector('#searchRow' + elementID); // To element μετά το οποίο θα προστεθεί το select

    // Το div element μέσα στο οποίο θα μπει το select
    var divElement = document.createElement('div');
    divElement.setAttribute('id', 'searchRow-' + elementID);
    divElement.setAttribute('class', 'row w-25 mx-1' );

    // Δημιουργεί το select
    var selectElement = document.createElement('select');
    selectElement.setAttribute('type', 'text');
    selectElement.setAttribute('class', 'form-control form-control-sm col-6 search_operator');
    selectElement.setAttribute('id', 'group_operator' + elementID);
    selectElement.setAttribute('name', 'group_operator' + elementID);

    // Δημιουργεί το κουμπί για αφαίρεση της γραμμής
    var removeRowButton = document.createElement('span');
    removeRowButton.setAttribute('class', 'fa fa-minus-circle col-6 my-auto hasCursorPointer');
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

/**
 * Καθαρίζει την φόρμα search
 */
function clearSearch()
{
    // Σβήνει όλα τα searchRow εκτός του searchRow0
    $('div[id^="searchRow"]').not('#searchRow0').remove();

    // Προσθέτει ένα
    addSearchRow();
}

/**
 * Φορτώνει την λίστα του ιστορικού
 */
function loadPlayedQueuePlaylist() {
    ProgressAnimation.init(false);
    $('#search').modal('hide');

    $.ajax({
        url: AJAX_path + 'app/loadPlayedQueue',
        type: 'GET',
        data: {
          tabID: tabID
        },
        dataType: "json",
        success: function (data) {
            if (data.success === true) {
                $.ajax({
                    url: AJAX_path + 'app/searchPlaylist',
                    type: 'GET',
                    data: {
                        tabID: tabID,
                        firstTime: 'true',
                        loadPlaylist: 'true'
                    },
                    success: function (data) {
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
                    }
                });
            } else {
                DisplayMessage('.alert_error', phrases['playlist_loading_problem']);
            }
        }
    });

}

/*
 * Κάνει τον συγχρονισμό των αρχείων
 *
 * @param operation
 */
function startTheSync(operation) {
    var mediaKind = document.querySelector('#mediakind').value;
    var GDOK =  document.querySelector('#jsGDOK').value;

    // Έλεγχος αν είναι εγκατεστημένη η GD library
    if ( (operation === 'sync' && GDOK === 'false' && mediaKind === 'Music') || (operation === 'coverConvert' && GDOK === 'false') ) {
        var confirmAnswer = confirm(phrases['GD_not_installed']);

        if(!confirmAnswer) {
            return;
        }
    }

    if(localStorage.syncPressed === 'false'){  // Έλεγχος αν δεν έχει πατηθεί ήδη
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
            url: AJAX_path+"app/syncTheFiles",
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

/**
 * Έλεγχος αν η process τρέχει
 */
function checkProcessAlive() {
    // TODO να τεστάρω τι γίνεται την στιγμή που διαβάζει αρχεία και δεν στέλνει σημείο ζωής

    if (localStorage.syncPressed === 'true') { // αν η process τρέχει
        $('.syncButton').prop('disabled', true);
    }
    else {
        $('.syncButton').prop('disabled', false);
    }

    var TheSyncInterval = setInterval(function(){

        $.ajax({
            url: AJAX_path + "framework/checkLastMomentAlive",
            type: 'GET',
            dataType: "json",
            success: function (data) {
                var syncButtonID = $('.syncButton');

                if (data.success === true) { // αν η process τρέχει
                    localStorage.syncPressed = 'true';
                    syncButtonID.prop('disabled', true);
                } else {
                    localStorage.syncPressed = 'false';
                    syncButtonID.prop('disabled', false);
                }

                if(syncButtonID.length == 0) {
                    clearInterval(TheSyncInterval);
                }

            }
        });

    }, 1000);
}

/**
 * Σβήνει το αρχείο που μόλις περάσαμε, επειδή υπάρχει ήδη
 *
 * @param id
 */
function deleteExistedFile(id)
{
    var confirmAnswer = confirm(phrases['sure_to_delete_file']);

    if (confirmAnswer === true) {
        $.ajax({
            url: AJAX_path + "app/deleteFile",
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

/**
 * Καλεί το ajax σε queue για να κάνει το μαζικό update αρχείων
 *
 * @param path
 * @param filename
 * @param id
 * @param counter
 * @param total
 */
function callUpdateTheFile(path, filename, id, counter, total, newID) {
    $.ajaxQueue({  // χρησιμοποιούμε το extension του jquery (αντί του $.ajax) για να εκτελεί το επόμενο AJAX μόλις τελειώσει το προηγούμενο
        url: AJAX_path + "app/updateFile",
        type: 'GET',
        async: true,
        data: {
            path: path,
            filename: filename,
            id: id,
            newID: newID
        },
        dataType: "json",
        beforeSend: function (xhr) {
            if(runningUpdateFiles) {
                var progressPercent = parseInt(((counter + 1) / total) * 100);

                ProgressAnimation.setProgressPercent(progressPercent);

                // $("#theProgressNumber").html(progressPercent + '%');
                // document.querySelector('#theProgressBar').value = progressPercent;
            } else {
                xhr.abort();
            }

        },
        success: function (data) {
            if (data.success) {
                $("#updateRow" + data.id).remove();
            }
        }
    });

}

/**
 * Καλεί το ajax σε queue για να κάνει το μαζικό delete αρχείων
 *
 * @param fullpath
 * @param filename
 * @param id
 * @param counter
 * @param total
 */
function callDeleteTheFile(fullpath, filename, id, counter, total) {
    $.ajaxQueue({  // χρησιμοποιούμε το extension του jquery (αντί του $.ajax) για να εκτελεί το επόμενο AJAX μόλις τελειώσει το προηγούμενο
        url: AJAX_path + "app/deleteFile",
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
                var progressPercent = parseInt(((counter + 1) / total) * 100);

                ProgressAnimation.setProgressPercent(progressPercent);
            } else {
                xhr.abort();
            }

        },
        success: function (data) {
            if (data.success) {
                $("#deleteRow" + data.id).remove();
            }
        }
    });

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

    var parentElement = document.querySelector('#search_text_group' + elementID); // Parent element to insert new select

    // Δημιουργεί το select
    var element = document.createElement('select');
    element.setAttribute('type', 'text');
    element.setAttribute('id', 'search_text' + elementID);
    element.setAttribute('name', 'search_text' + elementID);
    element.setAttribute('class', 'form-control form-control-sm');

    var option=[];

    // Δημιουργεί τα options του select
    for (var i = 0; i < optionsArray.length; i++) {
        option[i] = document.createElement('option');
        option[i].value = i;
        option[i].innerHTML = optionsArray[i];
    }

    // Insert options to element
    for (i = 0; i < optionsArray.length; i++) {
        element.appendChild(option[i]);
    }

    // Insert new select element to parent element
    parentElement.appendChild(element);

}

/**
 * Αλλάζει ένα select σε input
 *
 * @param elem
 * @param elementID
 */
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

/**
 * εμφανίζει το sliderId value στο outputId
 *
 * @param sliderId
 * @param outputId
 */
function printValue(sliderId, outputId)
{
    outputId.value = sliderId.value;
}

/**
 * Σβήνει ένα αρχείο μαζί με την αντίστοιχη εγγραφή στην βάση
 *
 * @param id
 */
function deleteFile(id) {
    if(id == 0) {  // Αν το id 0 παίρνει τα ids όλων των checkbox items σε πίνακα
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
            $.ajax({
                url: AJAX_path + "app/deleteFile",
                type: 'GET',
                data: {
                  id: id
                },
                dataType: "json",
                success: function (data) {
                    if (data.success === true) {
                        $("#fileID" + id).remove();
                    }
                }
            });
        } else {  // σβήνει μαζικά όσα αρχεία έχουν τσεκαριστεί
            for(var i = 0; i < checkIDs.length;  i++) {
                $.ajax({
                    url: AJAX_path + "app/deleteFile",
                    type: 'GET',
                    data: {
                        id: checkIDs[i]
                    },
                    dataType: "json",
                    success: function (data) {
                        if (data.success === true) {
                            $("#fileID" + data.id).remove();
                        }
                    }
                });
            }
        }
    }
}

/**
 * Σβήνει μια λίστα (array) αρχείων
 *
 * @param filesArray
 */
function deleteFiles(filesArray) {
    var confirmAnswer = confirm(phrases['sure_to_delete_files']);

    if (confirmAnswer === true) {
        ProgressAnimation.init(true);
        ProgressAnimation.setProgressPercent(0);

        $("#AgreeToDeleteFiles").remove();
        displayKillCommandIcon();

        runningUpdateFiles = true;

        for (var i = 0; i < filesArray.length; i++) {
            callDeleteTheFile(filesArray[i]['fullpath'], filesArray[i]['filename'], filesArray[i]['id'], i, filesArray.length);
        }

        $(document).one("ajaxStop", function() {  // Μόλις εκτελεστούν όλα τα ajax κάνει το παρακάτω
            ProgressAnimation.kill();
            hideKillCommandIcon();
            runningUpdateFiles = false;
        });
    }
}

/**
 * Ανοίγει το παράθυρο για edit των tags
 */
function openMassiveTagsWindow() {
    $('#editTag').show();
}

/**
 * Κλείνει το παράθυρο για edit των tags
 */
function cancelTheEdit() {
    $('#editTag').hide();
}

/**
 * Κλείνει το παράθυρο για search
 */
function cancelTheSearch() {
    $('#search').modal('hide');
}

/**
 * Διαβάζει το αρχείο εικόνας που έχει επιλέξει ο χρήστης
 *
 * @param files
 */
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

/**
 * Κάνει μαζικό edit των στοιχείων μιας λίστας (array) αρχείων
 */
function editFiles() {

    var confirmAnswer = confirm(phrases['sure_to_update_files']);

    if (confirmAnswer === true) {
        var all_checkboxes = document.querySelectorAll('input[name="check_item[]"]:checked');

        var checkIDs = [];

        for(var i = 0; i < all_checkboxes.length;  i++)
        {
            checkIDs.push(all_checkboxes[i].value);
        }

        var artist = $('#FormMassiveTags #artist').val();
        var genre = $('#FormMassiveTags #genre').val();
        var song_year = $('#FormMassiveTags #year').val();
        var album = $('#FormMassiveTags #album').val();
        var rating= $ ('#FormMassiveTags #rating').val();
        var live = $('#FormMassiveTags #live').val();


        if(myImage !== '') {
            coverImage = myImage;
            coverMime = myMime;
        }
        else {
            coverImage = '';
            coverMime = '';
        }

        for (i = 0; i < checkIDs.length; i++) {

            $.ajax({
                url: AJAX_path+"app/updateTags",
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

/**
 * Ενημερώνει μια λίστα (array) αρχείων που έχουν αλλάξει filepath και filename
 *
 * @param filesArray
 */
function updateFiles(filesArray) {
    var confirmAnswer = confirm(phrases['sure_to_update_files']);

    if (confirmAnswer === true) {
        ProgressAnimation.init(true);
        ProgressAnimation.setProgressPercent(0);
        $("#AgreeToUpdateFiles").remove();
        displayKillCommandIcon();

        runningUpdateFiles = true;

        for (var i = 0; i < filesArray.length; i++) {
            callUpdateTheFile(filesArray[i]['path'], filesArray[i]['filename'], filesArray[i]['id'], i, filesArray.length, filesArray[i]['newID']);
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
    var playlistID = document.querySelector('#playlist').value;

    if(playlistID === '') {  // Αν δεν έχει επιλεχτεί μια playlist
        if(!checkFullscreen()) { // αν δεν είναι σε full screen
            DisplayMessage('.alert_error', phrases['you_have_to_choose_playlist']);
        } else { // αν είναι σε full screen
            DisplayMessage('#error_overlay', phrases['you_have_to_choose_playlist']);
        }

        return;

    }

    $.ajax({
        url: AJAX_path + "app/addToPlaylist",
        type: 'GET',
        data: {
            playlistID: playlistID,
            fileID: fileID
        },
        dataType: "json",
        success: function (data) {
            var playlistName = document.querySelector('#playlist option:checked').text; // Το όνομα της playlist

            if (data.success === true) {
                if(!checkFullscreen()) { // αν δεν είναι σε full screen
                    DisplayMessage('.alert_error', phrases['song_added_to'] + ' ' + data.song_name
                        + ' ' + phrases['_to_playlist'] + ' ' + playlistName);
                } else { // αν είναι σε full screen
                    DisplayMessage('#error_overlay', phrases['song_added_to'] + ' ' + data.song_name
                        + ' ' + phrases['_to_playlist'] + ' ' + playlistName);
                }
            }
            else {
                if(data.errorID === 2) {
                    if(!checkFullscreen()) { // αν δεν είναι σε full screen
                        DisplayMessage('.alert_error', phrases['song_exist_to'] + ' ' + data.song_name
                            + ' ' + phrases['_to_playlist'] + ' ' + playlistName);
                    } else { // αν είναι σε full screen
                        DisplayMessage('#error_overlay', phrases['song_exist_to'] + ' ' + data.song_name
                            + ' ' + phrases['_to_playlist'] + ' ' + playlistName);
                    }
                }
            }
        }
    });
}

/**
 * Αφαίρεση κομματιού από την playlist
 *
 * @param fileID
 */
function removeFromPlaylist(fileID) {
    var playlistID = document.querySelector('#playlist').value;

    if(playlistID === '') {  // Αν δεν έχει επιλεχτεί μια playlist
        if(!checkFullscreen()) { // αν δεν είναι σε full screen
            DisplayMessage('.alert_error', phrases['you_have_to_choose_playlist']);
        } else { // αν είναι σε full screen
            DisplayMessage('#error_overlay', phrases['you_have_to_choose_playlist']);
        }

        return;

    }

    $.ajax({
        url: AJAX_path + "app/removeFromPlaylist",
        type: 'GET',
        data: {
            playlistID: playlistID,
            fileID: fileID
        },
        dataType: "json",
        success: function (data) {
            var playlistName=document.querySelector('#playlist option:checked').text; // Το όνομα της playlist

            if (data.success === true) {

                if(!checkFullscreen()) { // αν δεν είναι σε full screen
                    DisplayMessage('.alert_error', phrases['song_deleted_from'] + ' ' + data.song_name
                        + ' ' + phrases['_from_playlist'] + ' ' + playlistName);
                } else { // αν είναι σε full screen
                    DisplayMessage('#error_overlay', phrases['song_deleted_from'] + ' ' + data.song_name
                        + ' ' + phrases['_from_playlist'] + ' ' + playlistName);
                }

                // Σβήσιμο της σχετικής γραμμής στην λίστα
                document.querySelector('#fileID'+data.fileID).remove();
            } else {
                if(!checkFullscreen()) { // αν δεν είναι σε full screen
                    DisplayMessage('.alert_error', phrases['song_not_deleted'] + ' ' + data.song_name
                        + ' ' + phrases['_from_playlist'] + ' ' + playlistName);
                } else {
                    DisplayMessage('#error_overlay', phrases['song_not_deleted'] + ' ' + data.song_name
                        + ' ' + phrases['_from_playlist'] + ' ' + playlistName);
                }
            }
        }
    });
}

/**
 * Εμφανίζει το volume
 *
 * @param operation
 */
function displayVolume(operation) {
    if(checkFullscreen()) {
        var volume = parseInt(localStorage.volume * 100);
        var overlayTextID = $('#overlay_volume_text');

        if(operation !== 'giphyON' && operation !== 'giphyOFF') {
            document.querySelector('#overlay_volume_text').innerText = volume;
        }

        // overlayTextID.removeClass();

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

/**
 * Αλλάζει τον χρόνο που βρίσκεται το track αναλόγως την θέση στον slider
 */
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

/**
 * Εμφανίζει το τρέχον cover image, όπου είναι ο κέρσορας
 *
 * @param elem
 */
function displayCoverImage(elem) {
    $('.coverImage').hide();
    $('#'+elem).find('img').show();
}

/**
 * Εξαφανίζει όλα τα cover image
 */
function hideCoverImage() {
    $('.coverImage').hide();
}

/**
 * Εμφανίζει το παράθυρο για αναζήτηση
 */
function displaySearchWindow() {
    $('#search').show();
}

/**
 * Εμφανίζει το παράθυρο για εισαγωγή playlist
 */
function displayInsertPlaylistWindow() {
    $('#insertPlaylistWindow').show();
}

/**
 * Εμφανίζει το παράθυρο για εισαγωγή smart playlist
 */
function displayInsertSmartPlaylistWindow()
{
    $('#insertSmartPlaylistWindow').show();
}

/**
 * Κλείνει το παράθυρο για εισαγωγή playlist
 */
function cancelCreatePlaylist()
{
    $('#insertPlaylistWindow').modal('hide');
}

/**
 * Κλείνει το παράθυρο για εισαγωγή smart playlist
 */
function cancelCreateSmartPlaylist()
{
    $('#insertSmartPlaylistWindow').hide();
}

/**
 * Ελέγχει τις αλλαγές στα πεδία της φόρμας
 *
 * @param element
 */
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

/**
 * Έλεγχος για όταν γίνονται αλλαγές στα search fields
 */
function checkSearchFieldChanges()
{
    var searchFieldID = $('.search_field');

    searchFieldID.off('change'); // Αφαίρεση προηγούμενων change events

    // Έλεγχος πιο πεδίο έχουμε διαλέξει για να ψάξουμε, ώστε να αλλάξουμε τον τύπο του search text
    searchFieldID.change(function() {
        checkTheChanges(this);
    });
}

/**
 * Κάνει export την τρέχουσα playlist
 */
function exportPlaylist() {
    var confirmAnswer = confirm(phrases['sure_to_export_playlist']);

    if (confirmAnswer === true) {

        if(localStorage.syncPressed === 'false'){  // Έλεγχος αν δεν έχει πατηθεί ήδη
            localStorage.syncPressed = 'true';

            ProgressAnimation.init(true);
            ProgressAnimation.setProgressPercent(0);
            displayKillCommandIcon();

            syncRunning = true;

            var exportInterval=setInterval(function(){

                $.ajax({
                    url: AJAX_path + "framework/getProgress",
                    type: 'GET',
                    dataType: "json",
                    success: function (data) {
                        if (progressData.success === true) {
                            ProgressAnimation.setProgressPercent(progressData.progressInPercent);
                            // $("#theProgressNumber" ).html(progressData.progressInPercent+'%');
                            // document.querySelector('#theProgressBar').value=progressData.progressInPercent;
                        }
                    }
                });

            }, 1000);

            $.ajax({
                url: AJAX_path + "app/exportPlaylist",
                type: 'GET',
                data: {
                    tabID: tabID
                },
                dataType: "json",
                success: function (data) {
                    hideKillCommandIcon();
                    localStorage.syncPressed='false';
                    clearInterval(exportInterval);
                    syncRunning = false;
                }
            });

        } else {
            alert (phrases['running_process']);
        }
    }
}

/**
 * Δημιουργεί μια manual playlist
 */
function createPlaylist() {
    var playlistName = document.querySelector('#playlistName').value;

    $.ajax({
        url: AJAX_path + "app/createPlaylist",
        type: 'GET',
        data: {
            playlistName: playlistName
        },
        dataType: "json",
        success: function (data) {
            if (data.success === true) {
                $('#insertPlaylistWindow').modal('hide');

                // Προσθέτει στο select #playlist καινούργιο option με την νέα playlist
                var option = document.createElement('option');
                option.value = data.playlistID;
                option.innerHTML = data.playlistName;

                document.querySelector('#playlist').appendChild(option); // προσθέτει το νέο option

                DisplayMessage('.alert_error', phrases['playlist_created'] + ' ' + data.playlistName);

                document.querySelector('#insertPlaylist').reset();
            } else {
                DisplayMessage('.alert_error', phrases['playlist_not_created'] + ' ' + data.playlistName);
            }
        }
    });
}

/**
 * Δημιουργεί μια smart playlist
 */
function createSmartPlaylist() {
    var playlistName = document.querySelector('#smartPlaylistName').value;

    $.ajax({
        url: AJAX_path + "app/createSmartPlaylist",
        type: 'GET',
        data: {
            playlistName: playlistName
        },
        dataType: "json",
        success: function (data) {
            if (data.success === true) {
                $('#insertSmartPlaylistWindow').hide();

                // Προσθέτει στο select #playlist καινούργιο option με την νέα playlist
                var option = document.createElement('option');
                option.value = data.playlistID;
                option.innerHTML = data.playlistName;

                document.querySelector('#smartPlaylist').appendChild(option); // προσθέτει το νέο option

                DisplayMessage('.alert_error', phrases['smart_playlist_created'] + ' ' + data.playlistName);

                document.querySelector('#insertSmartPlaylist').reset();
            } else {
                DisplayMessage('.alert_error', phrases['smart_playlist_not_created'] + ' ' + data.playlistName);
            }
        }
    });
}

// Σβήνει μια manual playlist
function deletePlaylist() {
    var playlistID = document.querySelector('#playlist').value;

    if(playlistID === '') {  // Αν δεν έχει επιλεχτεί μια playlist
        DisplayMessage('.alert_error', phrases['you_have_to_choose_playlist']);
        return;
    }

    var confirmAnswer = confirm(phrases['sure_to_delete_playlist']);

    if (confirmAnswer === true) {

        $.ajax({
            url: AJAX_path + "app/deletePlaylist",
            type: 'GET',
            data: {
                playlistID: playlistID
            },
            dataType: "json",
            success: function (data) {
                var playlistName = document.querySelector('#playlist option:checked').text; // Το όνομα της playlist

                if (data.success === true) {
                    DisplayMessage('.alert_error', phrases['playlist_deleted'] + ' ' + playlistName);

                    // Σβήνει το συγκεκριμένο option από το select #playlist
                    document.querySelector("#playlist option:checked").remove();

                } else {
                    DisplayMessage('.alert_error', phrases['playlist_not_deleted'] + ' ' + playlistName);
                }
            }
        });

    }
}

/**
 * Σβήνει μια smart playlist
 */
function deleteSmartPlaylist() {
    var playlistID = document.querySelector('#smartPlaylist').value;

    if(playlistID === '') {  // Αν δεν έχει επιλεχτεί μια playlist
        DisplayMessage('.alert_error', phrases['you_have_to_choose_playlist']);
        return;
    }

    var confirmAnswer = confirm(phrases['sure_to_delete_playlist']);

    if (confirmAnswer === true) {

        $.ajax({
            url: AJAX_path + "app/deleteSmartPlaylist",
            type: 'GET',
            data: {
                playlistID: playlistID
            },
            dataType: "json",
            success: function (data) {
                var playlistName = document.querySelector('#smartPlaylist option:checked').text; // Το όνομα της playlist

                if (data.success === true) {
                    DisplayMessage('.alert_error', phrases['smart_playlist_deleted'] + ' ' + playlistName);

                    // Σβήνει το συγκεκριμένο option από το select #playlist
                    document.querySelector("#smartPlaylist option:checked").remove();

                } else {
                    DisplayMessage('.alert_error', phrases['smart_playlist_not_deleted'] + ' ' + playlistName);
                }
            }
        });

    }
}

/**
 * Σώζει το search query σε smart playlist, σε μορφή json
 */
function saveSmartPlaylist() {
    var playlistID = document.querySelector('#smartPlaylist').value;

    if(playlistID) {
        var searchArray = getSearchArray();
        var searchJsonString = JSON.stringify(searchArray);

        $.ajax({
            url: AJAX_path + "app/saveSmartPlaylist",
            type: 'GET',
            data: {
                playlistID: playlistID,
                searchJsonString: searchJsonString
            },
            dataType: "json",
            success: function (data) {
                var playlistName = document.querySelector('#smartPlaylist option:checked').text; // Το όνομα της playlist

                if (data.success === true) {
                    DisplayMessage('.alert_error', phrases['smart_playlist_saved'] + ' ' + playlistName);
                } else {
                    DisplayMessage('.alert_error', phrases['smart_playlist_not_saved'] + ' ' + playlistName);
                }
            }
        });

    }
}

/**
 * Φορτώνει τις τιμές των search fields
 *
 * @param elementID
 * @param searchArray
 */
function loadSearchFields(elementID, searchArray)
{
    $('#search_field' + elementID).val(searchArray['search_field']);
    $('#search_text' + elementID).val(searchArray['search_text']);
    $('#search_operator' + elementID).val(searchArray['search_operator']);
    $('#search_equality' + elementID).val(searchArray['search_equality']);
}

/**
 * Φορτώνει μία smart playlist και εμφανίζει όλα τα search items
 */
function loadSmartPlaylist()
{
    var playlistID = document.querySelector('#smartPlaylist').value;

    $.ajax({
        url: AJAX_path + "app/loadSmartPlaylist",
        type: 'GET',
        data: {
            playlistID: playlistID
        },
        dataType: "json",
        success: function (data) {
            var playlistName = document.querySelector('#smartPlaylist option:checked').text; // Το όνομα της playlist

            if (data.success === true) {
                var jsonArray = JSON.parse(data.searchJsonArray);

                // Καθαρίζει τα υπάρχοντα searchRows
                clearSearch();
                $("#searchRow1").remove();

                // Προσθέτει όλες τις γραμμές με τα περιεχόμενα τους
                for(var i=1; i<jsonArray.length; i++) {
                    // αν δεν είναι group operator
                    if(jsonArray[i]['group_operator'] === undefined) {
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

            } else {
                DisplayMessage('.alert_error', phrases['smart_playlist_not_loaded'] + ' ' + playlistName);
            }
        }
    });

}

// Καθαρίζει όλες τις τιμές main (τις κάνεις not main) και αφήνει μόνο την μία για το συγκεκριμένο media kind
// Δεν χρησιμοποιείται
function checkMainSelected(formID, checkAll) {
    var currentMediaKind = document.querySelector('#paths_formID'+formID+' #kind').value;

    var founded = 0;  // μετράει αν υπάρχει έστω κι ένα main
    var firstFindedMediaKind = null;

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
        if(founded == 0) {
            document.querySelector('#paths_formID' + formID + ' #main').selectedIndex = '1';
        }
    }
    else {
        document.querySelector('#paths_formID' + firstFindedMediaKind + ' #main').selectedIndex = '1';
        return firstFindedMediaKind;
    }


}

// TODO να εμφανίζει το progress
/**
 * Στέλνει την τρέχουσα playlist στην jukebox list
 */
function sendToJukeboxList() {
    $('#progress').show();

    $.ajax({
        url: AJAX_path + 'app/sendToJukeBox',
        type: 'GET',
        data: {
            tabID: tabID
        },
        dataType: "json",
        success: function (data) {
            if (data.success === true) {
                DisplayMessage('.alert_error', phrases['playlist_loaded_to_jukebox']);
                $('#progress').hide();
            } else {
                DisplayMessage('.alert_error', phrases['problem_to_copy_to_jukebox']);
                $('#progress').hide();
            }
        }
    });
}

/**
 * Προσθέτει μία ψήφο στο τραγούδι
 *
 * @param id
 */
function voteSong(id) {
    $.ajax({
        url: AJAX_path + 'app/voteSong',
        type: 'GET',
        data: {
            id: id
        },
        dataType: "json",
        success: function (data) {
            if (data.success === true) {
                DisplayMessage('.alert_error', phrases['vote_accepted']);
            } else {
                DisplayMessage('.alert_error', phrases['vote_not_accepted']);
            }
        }
    });
}

// TODO να κάνω γενική κλάση για upload
/**
 * Ανεβάζει ένα αρχείο
 *
 * @param files
 */
function jsUploadFile(files) {
    var selectedFile = document.getElementById('uploadSQLFile').files[0];

    var myMime = selectedFile.type;

    var f = files[0];

    var reader = new FileReader();

    // Called when the file content is loaded, e.target.result is
    // The content
    reader.onload = function (e) {
        // console.log(e.target.result);

        myFile = e.target.result;

        $.ajax({
            // Your server script to process the upload
            url: AJAX_path + 'app/uploadFile',
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
 * Εμφανίζει το παράθυρο επιλογής του sleep timer
 */
function displayTheSleepTimer()
{
    $('#insertSleepTimerWindow').show();
}

/**
 * Εξαφανίζει το παράθυρο επιλογής του sleep timer
 */
function cancelTheSleepTimer()
{
    $('#insertSleepTimerWindow').hide();
}

/**
 * Αρχίζει την αντίστροφη μέτρηση για το sleep
 */
function startSleepTimer()
{
    var sleepMinutes = document.querySelector('#sleepMinutes').value;

    var timeInSeconds = sleepMinutes*60;

    clearInterval(theTimer);

    theTimer = setInterval(function () {
        timeInSeconds--;

        var timeInMinutesAndSeconds = seconds2MinutesAndSeconds(timeInSeconds);
        document.querySelector('#theSleepTimer').innerText = timeInMinutesAndSeconds['minutes'] + ':' + timeInMinutesAndSeconds['seconds'];

        if (timeInSeconds == 0) {
            clearInterval(theTimer);
            if (!myVideo.paused) {
                myVideo.pause();
                displayPlayButton();
            }
        }

    }, 1000);

    $('#insertSleepTimerWindow').hide();
}

/**
 * Εμφανίζει/εξαφανίζει το resultsContainter
 */
function toggleResultsContainer()
{
    $('.o-resultsContainer').toggleClass('isHidden isVisible');

    BlinkElement.stop(); // Σταματάει το αναβόσβησμα του εικονίδιου
}

/**
 * Κάνει ένα element να αναβοσβήνει
 */
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



