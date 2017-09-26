/**
 *
 * File: _audioInputs.js
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 26/09/17
 * Time: 16:25
 *
 * functions για έλεγχο των audio output devices. Παίζουν μόνο σε https
 *
 */

function gotDevices(deviceInfos) {
    // window.deviceInfos = deviceInfos;
    for (var i = 0; i !== deviceInfos.length; ++i) {
        var deviceInfo = deviceInfos[i];

        if (deviceInfo.kind === 'audiooutput') {
            console.log('Found audio output device: ' + deviceInfo.deviceId + '  ' + deviceInfo.label);
        }
    }
}

function errorCallback(error) {
    console.log('Error: ', error);
}

// Attach audio output device to video element using device/sink ID.
function attachSinkId(element, sinkId) {
    if (typeof element.sinkId !== 'undefined') {
        element.setSinkId(sinkId)
            .then(function() {
                console.log('Success, audio output device attached: ' + sinkId);
            })
            .catch(function(error) {
                var errorMessage = error;
                if (error.name === 'SecurityError') {
                    errorMessage = 'You need to use HTTPS for selecting audio output ' +
                        'device: ' + error;
                }
                console.error(errorMessage);
                // Jump back to first output device in the list as it's the default.
                audioOutputSelect.selectedIndex = 0;
            });
    } else {
        console.warn('Browser does not support output device selection.');
    }
}