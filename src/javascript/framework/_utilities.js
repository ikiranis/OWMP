/**
 *
 * File: _utilities.js
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 26/09/17
 * Time: 16:37
 *
 * Utility functions
 *
 */

/**
 * μετράει τα πεδία ενός json object
 *
 * @param obj
 * @returns {number}
 */
function countjson(obj) {
    var count=0;
    for(var prop in obj) {
        if (obj.hasOwnProperty(prop)) {
            ++count;
        }
    }
    return count;
}

/**
 * Μετατρέπει τα δευτερόλεπτα σε "ανθρώπινα" λεπτά και δευτερόλεπτα. Επιστρέφει τιμές σε array (minutes, seconds)
 *
 * @param timeInSeconds
 * @returns {{minutes: Number, seconds: Number}}
 */
function seconds2MinutesAndSeconds(timeInSeconds) {
    var timeInMinutes=parseInt(timeInSeconds/60);
    var newTimeInSeconds=parseInt(timeInSeconds%60);

    if(timeInMinutes<10) timeInMinutes='0'+timeInMinutes.toString();
    if(newTimeInSeconds<10) newTimeInSeconds='0'+newTimeInSeconds.toString();

    return {  // Μετατροπή σε array
        'minutes': timeInMinutes,
        'seconds': newTimeInSeconds
    }

}

/**
 * Προσθέτει το 0 μπροστά από τον αριθμό όταν είναι κάτω από το 10
 *
 * @param i
 * @returns {*}
 */
function addZero(i) {
    if (i < 10) {
        i = "0" + i;
    }
    return i;
}

/**
 * Επιστρέφει την τρέχουσα ώρα σε string και το εμφανίζει στο element name
 *
 * @param name
 */
function getTime(name) {
    var myTime = new Date();

    var curTime=addZero(myTime.getHours())+':'+
        addZero(myTime.getMinutes())+':'+
        addZero(myTime.getSeconds());

    $(name).text(curTime);
}

/**
 * Δημιουργεί ένα cookie
 *
 * @param name
 * @param value
 * @param minutes
 */
function createCookie(name, value, minutes) {
    var expires;
    if (minutes) {
        var date = new Date();
        date.setTime(date.getTime() + (minutes));
        expires = date;
    } else {
        expires = "";
    }
    document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
}