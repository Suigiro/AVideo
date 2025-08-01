var callerToast = [];
var callingTimeoutSeconds = 30;

function getCallJsonFromUser(to_users_id, to_identification) {
    var json = {
        to_users_id: to_users_id,
        to_identification: to_identification,
        to_socketResourceId: false,
        from_users_id: my_users_id,
        from_identification: my_identification,
        from_socketResourceId: socketResourceId
    };
    return json;
}

function callNow(to_users_id, to_identification) {
    var timeout;
    if ($('body').hasClass('calling')) {
        avideoToastError('Please finish the call first');
        return false;
    } else if (!users_id_online.length) {
        timeout = 1000;
        avideoToastInfo('Please wait ...');
    } else {
        timeout = 1;
    }
    setTimeout(function () {
        if (!isUserOnline(to_users_id)) {
            avideoToastError('User is not online anymore');
            console.log('User is not online anymore', users_id_online);
            return false;
        } else {
            avideoToastSuccess('Calling ...');
            setCallBodyClass('calling');
        }
        var json = getCallJsonFromUser(to_users_id, to_identification);
        console.log('callNow', json);
        sendSocketMessageToUser(json, 'incomeCall', to_users_id);
    }, timeout);

    //incomeCall(json);
}


function callUserNow(to_users_id) {
    avideoModalIframeFull(webSiteRootURL + 'plugin/YPTSocket/callUser.php?users_id='+to_users_id);
    return false;
}

function isJsonReceivingCall(json) {
    if (json.from_users_id != my_users_id) {
        // is receiving a call
        return true;
    } else {
        return false;
    }
}

function incomeCall(json) {
    if (isJsonReceivingCall(json)) {
        setCallBodyClass('callIncoming');
        users_id = json.from_users_id;
        userIdentification = json.from_identification;
    } else {
        setCallBodyClass('calling');
        users_id = json.to_users_id;
        userIdentification = json.to_identification;
    }
    if (!isUserOnline(users_id)) {
        avideoToastError('A user called you but he is not online anymore');
        return false;
    }
    if (typeof callerToast[users_id] !== 'undefined') {
        console.log('incomeCall callerToast already active', users_id);
        return false;
    }
    imageAndButton = getImageAndButton(json);
    callerToast[users_id] = $.toast({
        heading: userIdentification,
        text: imageAndButton,
        hideAfter: (callingTimeoutSeconds * 1000),
        showHideTransition: 'slide',
        //position: 'top-right',
        textAlign: 'center',
        afterHidden: function () {
            setCallBodyClass('notCalling');
            console.log('incomeCall afterHidden', users_id, shouldHangUpCall);
            if (shouldHangUpCall) {
                finishCall(json);
            }
            shouldHangUpCall = 1;
            callerToast[users_id] = null;
            delete callerToast[users_id];
        }
    });
    console.log('incomeCall', users_id, callerToast[users_id]);
}

function getImageAndButton(json) {
    if (isJsonReceivingCall(json)) {
        users_id = json.from_users_id;
    } else {
        users_id = json.to_users_id;
    }
    var imageAndButton = '';
    json.playCallBusySound = 0;
    imageAndButton += '<center>';
    imageAndButton += '<img src="' + webSiteRootURL + 'user/' + users_id + '/foto.png" class="img img-circle img-responsive incomeCallImage glowBox">';
    imageAndButton += '</center>';
    imageAndButton += '<div class="clearfix"></div>';
    imageAndButton += '<button class="btn btn-danger btn-circle incomeCallBtn" onclick=\'hangUpCall(' + JSON.stringify(json) + ')\'><i class="fas fa-phone-slash"></i></button>';
    if (isJsonReceivingCall(json)) {
        imageAndButton += '<button class="btn btn-success btn-circle incomeCallBtn" onclick=\'acceptCall(' + JSON.stringify(json) + ')\'><i class="fas fa-phone"></i></button>';
    }
    return imageAndButton;
}

var shouldHangUpCall = 1;
function hangUpCall(json) {
    if (isJsonReceivingCall(json)) {
        users_id = json.from_users_id;
    } else {
        users_id = json.to_users_id;
    }
    if (typeof callerToast[users_id] == 'object' && typeof callerToast[users_id].close == 'function') {
        console.log('hangUpCall callerToast', users_id);
        shouldHangUpCall = 1;
        callerToast[users_id].close();
    } else if ($('body').hasClass('calling')) {
        avideoToastWarning('Hangup');
        console.log('hangUpCall page', users_id);
    }
    setCallBodyClass('notCalling');
    if (json.playCallBusySound) {
        playCallBusySound();
    }
}

function finishCall(json) {
    if (isJsonReceivingCall(json)) {
        users_id = json.from_users_id;
        json.playCallBusySound = 1;
    } else {
        users_id = json.to_users_id;
    }
    if (typeof callerToast[users_id] == 'object') {
        console.log('finishCall', users_id);
        sendSocketMessageToUser(json, 'hangUpCall', users_id);
        var obj = {users_id: users_id, shouldHangUpCall: 0};
        sendSocketMessageToUser(obj, 'hideCall', my_users_id);
        avideoToastWarning('Finished');
    } else {
        console.log('finishCall ERRRO', users_id);
    }

}

function acceptCall(json) {
    stopAllAudio();
    users_id = json.from_users_id;
    if (!isUserOnline(users_id)) {
        avideoToastError('The is not online anymore');
        return false;
    }
    if (typeof callerToast[users_id] == 'object' && typeof callerToast[users_id].close == 'function') {
        console.log('acceptCall', users_id);
        var obj = {users_id: users_id, shouldHangUpCall: 0};
        hideCall(obj);
        modal.showPleaseWait();
        if (!json.to_socketResourceId) {
            json.to_socketResourceId = socketResourceId;
        }
        setTimeout(function () {
            sendSocketMessageToResourceId(json, 'callAccepted', json.from_socketResourceId);
            sendSocketMessageToUser(obj, 'hideCall', my_users_id);
        }, 1000);
    } else {
        console.log('acceptCall ERROR', users_id);
    }
}

function hideCall(obj) {
    console.log('hideCall', obj);
    users_id = obj.users_id;
    shouldHangUpCall = obj.shouldHangUpCall;
    if (typeof callerToast[users_id] == 'object' && typeof callerToast[users_id].close == 'function') {
        callerToast[users_id].close();
    }
    setTimeout(function () {
        shouldHangUpCall = 1;
    }, 1000)
}

function callAccepted(json) {
    users_id = json.to_users_id;
    if (typeof callerToast[users_id] == 'object' && typeof callerToast[users_id].close == 'function') {
        console.log('callAccepted callerToast', users_id);
        obj = {users_id: users_id, shouldHangUpCall: 0};
        hideCall(obj);
    } else {
        setCallBodyClass('calling');
        console.log('callAccepted page', users_id);
    }
    stopAllAudio();
    startMeetForCall(json);
}

function startMeetForCall(json) {
    modal.showPleaseWait();
    var randomPass = parseInt(Math.random() * 100000);

    if (!isUserOnline(json.to_users_id)) {
        avideoToastError('Start a call fail, the user is not oline anymore');
        return false;
    }
    $.ajax({
        url: webSiteRootURL + 'plugin/API/set.json.php?APIName=meet',
        method: 'POST',
        data: {
            'public': 1,
            'RoomPasswordNew': randomPass,
            'RoomTopic': 'Call from ' + json.from_users_id + ' to ' + json.to_users_id
        },
        success: function (response) {
            console.log('startMeetForCall', response);
            if (!isUserOnline(json.to_users_id)) {
                avideoToastError('Start a call fail (2), the user is not oline anymore');
                return false;
            }
            if (response.error) {
                hangUpCall(json);
                avideoAlertError(response.message);
                sendSocketMessageToUser(users_id, 'hideCallPleaseWait', false);
                sendSocketMessageToUser(my_users_id, 'hideCallPleaseWait', false);
                modal.hidePleaseWait();
            } else {
                console.log('startMeetForCall', json, response);
                setTimeout(function () {
                    openMeetLink(response.linkAndPassword);
                    sendSocketMessageToResourceId(response.linkAndPassword, 'openMeetLink', json.to_socketResourceId);
                }, 1000);
            }
        }
    });
}
var callModalIFrameClosedInterval;
function openMeetLink(linkAndPassword) {
    setCallBodyClass('callActive');
    avideoModalIframeFull(linkAndPassword);
    callModalIFrameClosedInterval = setInterval(function () {
        if (!avideoModalIframeIsVisible()) {
            callModalIFrameClosed();
            sendSocketMessageToUser(users_id, 'callModalIFrameClosed', false);
            sendSocketMessageToUser(my_users_id, 'callModalIFrameClosed', false);
            setCallBodyClass('notCalling');
        }
    }, 1000);
    hideCallPleaseWait();
}

var callModalIFrameClosedTimeout;
function callModalIFrameClosed() {
    clearTimeout(callModalIFrameClosedTimeout);
    clearInterval(callModalIFrameClosedInterval);
    avideoModalIframeFullScreenClose();
    callModalIFrameClosedTimeout = setTimeout(function () {
        avideoToastInfo('Call disconnected');
    }, 1000);

}

function hideCallPleaseWait() {
    modal.hidePleaseWait();
}

var playCallIncomingSoundTimeout;
var playCallBusySoundTimeout;
var playCallingSoundTimeout;
function playCallIncomingSound() {
    stopAllCallSounds();
    playCallIncomingSoundTimeout = playAudio(webSiteRootURL + 'plugin/YPTSocket/mp3/call-incoming.mp3');
    //avideoToastInfo('playCallIncomingSound', playCallIncomingSoundTimeout);
}

function playCallBusySound() {
    stopAllCallSounds();
    playCallBusySoundTimeout = playAudio(webSiteRootURL + 'plugin/YPTSocket/mp3/call-busy.mp3');
    //avideoToastWarning('playCallBusySound');
}

function playCallingSound() {
    stopAllCallSounds();
    playCallingSoundTimeout = playAudio(webSiteRootURL + 'plugin/YPTSocket/mp3/calling.mp3');
    //avideoToastInfo('playCallIncomingSound', playCallIncomingSoundTimeout);
}

function stopAllCallSounds() {
    clearTimeout(playCallIncomingSoundTimeout);
    clearTimeout(playCallBusySoundTimeout);
    clearTimeout(playCallingSoundTimeout);
    stopAllAudio();
}

function isCalling() {
    return $('body').hasClass("calling");
}

function isReceivingCall() {
    return $('body').hasClass("callIncoming");
}

function callerNewConnection(json) {
    callerCheckUser(json.msg.users_id);
}

function callerDisconnection(json) {
    callerCheckUser(json.msg.users_id);
}

var callerCheckUserList = [];
function callerCheckUser(users_id) {
    callerCheckUserList.push(users_id);
    if (isUserOnline(users_id)) {
        //console.log('callerCheckUser OK', users_id, users_id_online);
        $('.caller' + users_id).show();
    } else {
        //console.log('callerCheckUser NO', users_id, users_id_online);
        $('.caller' + users_id).hide();
    }
}

async function callerCheckUserTimer() {
    if(!isReadyToCheckIfIsOnline()){
        setTimeout(function(){callerCheckUserTimer();},1000);
        return false;
    }
    
    var localCallerCheckUserList = callerCheckUserList;
    callerCheckUserList = [];
    
    for (var i in localCallerCheckUserList) {
        var users_id = localCallerCheckUserList[i];
        if(typeof users_id == 'function'){
            continue;
        }
        if (isUserOnline(users_id)) {
            //console.log('callerCheckUser OK', users_id, users_id_online);
            $('.caller' + users_id).show();
        } else {
            //console.log('callerCheckUser NO', users_id, users_id_online);
            $('.caller' + users_id).hide();
        }        
    }
    setTimeout(function(){callerCheckUserTimer();},2000);
}

function setCallBodyClass(name) {
    $('body').removeClass('calling');
    $('body').removeClass('callIncoming');
    $('body').removeClass('notCalling');
    $('body').removeClass('callActive');
    //$('body').removeClass('callerUserOffline');
    $('body').addClass(name);
}

$(document).ready(function () {
    setInterval(function () {
        if (isCalling()) {
            playCallingSound();
        } else if (isReceivingCall()) {
            playCallIncomingSound();
        }
    }, 5000);
    callerCheckUserTimer();

});