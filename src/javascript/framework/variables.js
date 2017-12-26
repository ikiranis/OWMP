/**
 *
 * File: scripts.js
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 30/04/17
 * Time: 01:33
 *
 * Javascript public variables
 *
 */


var UserKeyPressed = false;
var PathKeyPressed = false;
var VideoLoaded = false;

var currentID; // Το τρέχον file id που παίζει
var currentPlaylistID = '1';  // Το τρέχον id στην playlist
var currentQueuePlaylistID = 0;  // Το τρέχον id στην queue playlist

var myVideo;
var FullscreenON = false; // κρατάει το αν είναι σε fullscreen ή όχι

var TimeUpdated = false; // Κρατάει το αν έχει ήδη ενημερωθεί ο played time του βίντεο για να μην το ξανακάνει
var FocusOnForm = false; // Κρατάει το αν είμαστε στην φόρμα

var PlaylistContainerHTML = null;   // τα περιεχόμενα του div playlist_content
var SearchHTML = null; // τα περιεχόμενα του div search
var MediaKindChosen = null;
var GlobalSearchArray = []; //  τα values στην αναζήτηση
var CurrentPage = 1;

var OverlayON = false;  // Κρατάει το αν το overlay εμφανίζεται
// var OverlayAllwaysOn=false;  // Κρατάει το αν αν έχει πατηθεί κουμπί για να παραμένει το overlay συνέχεια on

var myImage = '';   // Το cover art που κάνουμε upload
var myMime = '';  // Ο τύπος του cover art

var myFile = ''; // Το αρχείο που κάνουμε upload

var tabID;

var PlayTime = 0; // Κρατάει πόσα τραγούδια παίξανε

var initEventListenerHadler = false; // κρατάει το αν έχει ενεργοποιηθεί το event listener στο init()

var runningYoutubeDownload = false; // Κρατάει το αν τρέχει το download του youtube
var runningUpdateFiles = false;  // Κρατάει το αν τρέχει το μαζικό update αρχείων

var displayingMediaControls = false; // Κρατάει το αν εμφανίζονται τα media controls σε fullscreen

var currentPathFormID;

var syncRunning = false; // Κρατάει το αν τρέχει κάποια sync εργασία

var videoItems=[];

var theTimer; // Ο μετρητής του sleep timer

if(localStorage.OverlayAllwaysOn === undefined) localStorage.OverlayAllwaysOn='false';    // μεταβλητή που κρατάει να θέλουμε να είναι πάντα on το overlay
if(localStorage.AllwaysGiphy === undefined) localStorage.AllwaysGiphy='false';   // μεταβλητή που κρατάει αν θέλουμε πάντα να δείχνει gifs αντί για albums

if(localStorage.PlayMode === undefined) localStorage.PlayMode='continue';

// Αν δεν υπάρχει το localStorage.syncPressed θέτει αρχική τιμή
if(localStorage.syncPressed === undefined) localStorage.syncPressed='false';  // κρατάει το αν έχει πατηθεί συγχρονισμός

(localStorage.convertToLowerBitrate === undefined) ? localStorage.convertToLowerBitrate = 'false' : null;