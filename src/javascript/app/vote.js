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

    $.ajax({
        url: AJAX_path+'app/voteSong',
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


// Αναζήτηση για διπλές εγγραφές και εμφάνιση τους
function getVotePlaylist(offset, step, firstTime) {
    ProgressAnimation.init(false);

    $.ajax({
        url: AJAX_path+"app/searchPlaylist",
        type: 'GET',
        data: {
            votePlaylist: 'true',
            firstTime: firstTime,
            offset: offset,
            step: step
        },
        success: function (data) {
            if (data) {
                $('#playlist_container').html(data);
                ProgressAnimation.kill();
                $('#search').hide();
            }
            else {
                $('#playlist_container').html('Δεν βρέθηκαν εγγραφές');
                ProgressAnimation.kill();
                $('#search').hide();
            }
        }
    });

}

// Τραβάει τα song info του τρέχοντος τραγουδιού
function getSongInfo() {
    $.ajax({
        url: AJAX_path+"app/getSongInfo",
        type: 'GET',
        dataType: "json",
        success: function (data) {
            if(data.success) {
                document.querySelector('#currentSongName').innerHTML = data.songName;
                document.querySelector('#currentSongArtist').innerHTML = data.artist;

                document.title = data.songName + ' : ' + data.artist;

                // Αν υπάρχει το συγκεκριμένο row τότε το σβήνει
                if($('#fileID'+data.fileID).length !== 0) {
                    $('#fileID' + data.fileID).addClass("blackRow");
                    setTimeout(function() {
                        $('#fileID' + data.fileID).remove();
                    }, 1000);
                }
            }
        }
    });
}


function closeVotesWindow() {
    $('#votesList').hide();
}

function getSongVotes() {
    $.ajax({
        url: AJAX_path+"app/getSongVotes",
        type: 'GET',
        success: function (data) {
            if (data) {
                $('#votesList').show();
                $('#votesListText').html(data);
            }
            else {
                $('#votesListText').html('Δεν βρέθηκαν εγγραφές');
            }
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