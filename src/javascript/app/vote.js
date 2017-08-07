//
// File: scripts.js
// Created by Yiannis Kiranis <rocean74@gmail.com>
// http://www.apps4net.eu
// Date: 28/12/16
// Time: 0.59
//
// Javascript functions for vote page
//

// extension στην jquery. Προσθέτει την addClassDelay. π.χ. $('div').addClassDelay('somedivclass',3000)
// Προσθέτει μια class και την αφερεί μετά από λίγο
$.fn.addClassDelay = function(className,delay) {
    var $addClassDelayElement = $(this), $addClassName = className;
    $addClassDelayElement.addClass($addClassName);
    setTimeout(function(){
        $addClassDelayElement.removeClass($addClassName);
    },delay);
};

function DisplayMessage (element, error) {
    $(element).text(error);
    $(element).stop().show(0).delay(5000).hide(0);
}



// Προσθέτει μία ψήφο στο τραγούδι
function voteSong(id) {

    callFile=AJAX_path+'app/voteSong.php?id='+id;

    $.get(callFile, function (data) {

        if (data.success == true) {

            DisplayMessage('.alert_error', phrases['vote_accepted']);

        }
        else {
            DisplayMessage('.alert_error', phrases['vote_not_accepted']);
        }

    }, "json");
}


// Αναζήτηση για διπλές εγγραφές και εμφάνιση τους
function getVotePlaylist(offset, step, firstTime) {
    callFile=AJAX_path+"app/searchPlaylist.php?votePlaylist=true"+"&firstTime="+firstTime+"&offset="+offset+"&step="+step;
    initProgressAnimation();

    $.get(callFile, function(data) {
        if (data) {
            $('#playlist_container').html(data);
            killAnimation();
            $('#search').hide();
        }
        else {
            $('#playlist_container').html('Δεν βρέθηκαν εγγραφές');
            killAnimation();
            $('#search').hide();
        }

    });
}

// Τραβάει τα song info του τρέχοντος τραγουδιού
function getSongInfo() {
    callFile=AJAX_path+"app/getSongInfo.php";

    $.get(callFile, function (data) {
        if(data.success) {
            document.querySelector('#currentSongName').innerHTML = data.songName;
            document.querySelector('#currentSongArtist').innerHTML = data.artist;

            document.title = data.songName+' : '+data.artist;

            // Αν υπάρχει το συγκεκριμένο row τότε το σβήνει
            if($('#fileID'+data.fileID).length!==0) {
                $('#fileID' + data.fileID).addClass("blackRow");
                setTimeout(function() {
                    $('#fileID' + data.fileID).remove();
                }, 1000);
            }
        }
    }, "json");
}


function closeVotesWindow() {
    $('#votesList').hide();
}

function getSongVotes() {
    callFile=AJAX_path+"app/getSongVotes.php";

    $.get(callFile, function(data) {
        if (data) {
            $('#votesList').show();
            $('#votesListText').html(data);
        }
        else {
            $('#votesListText').html('Δεν βρέθηκαν εγγραφές');
        }

    });
}

// On load
$(function(){

    // Ψάχνει και εμφανίζει το τρέχον τραγούδι κάθε 10 δευτερόλεπτα
    getSongInfo();
    setInterval(function(){
        getSongInfo();
    }, 10000);

});