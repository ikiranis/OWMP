//
// File: scripts.js
// Created by rocean
// Date: 28/12/16
// Time: 0.59
//
// Javascript functions for vote page
//


function DisplayMessage (element, error) {
    $(element).text(error);
    $(element).stop().show(0).delay(5000).hide(0);
}



// Προσθέτει μία ψήφο στο τραγούδι
function voteSong(id) {

    callFile=AJAX_path+'voteSong.php?id='+id;

    $.get(callFile, function (data) {

        if (data.success == true) {

            DisplayMessage('#alert_error', phrases['vote_accepted']);

        }
        else {
            DisplayMessage('#alert_error', phrases['vote_not_accepted']);
        }

    }, "json");
}


// Αναζήτηση για διπλές εγγραφές και εμφάνιση τους
function getVotePlaylist(offset, step, firstTime) {
    callFile=AJAX_path+"searchPlaylist.php?votePlaylist=true"+"&firstTime="+firstTime+"&offset="+offset+"&step="+step;
    $('#progress').show();

    $.get(callFile, function(data) {
        if (data) {
            $('#playlist_container').html(data);
            $('#progress').hide();
            $('#search').hide();
        }
        else {
            $('#playlist_container').html('Δεν βρέθηκαν εγγραφές');
            $('#progress').hide();
            $('#search').hide();
        }

    });
}