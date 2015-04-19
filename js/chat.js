(function(window, document, undefined) {




    /* The language to be used, server-default: english.
     * 
     * Available languages:
     * - English (denote by the 'en' string).
     * - Hebrew (denote by the 'he' string).
     * */
    var langReference = {},

    // Regex patterns object.
        regExpPatterns = {
            usMessage : /^[\s\S]+$/,
            usNick : /^[a-zא-ת\d]{2,8}(\s[a-zא-ת\d]{1,8})?$/i,
            idExtractor : /data\-id="(\d+)"/
        },

    // Chat variables.
        messagesContainer,
        chatForm,
        ajaxCounter = 0,
        scrollTopFlag = false,
        messagesInRooms = [],
        communicationSection,
        usMessage,
        usersTable,
        roomsList,
        errorParagraph,

    // New Rooms variables.
        newRoomDiv,
        cancelRoomDiv,
        approveRoomDiv,

    // New invitation variables.
        newInvitationDiv,
        cancelInvitationDiv,
        approveInvitationDiv,
        invitationForm,
        userToInvite,

    // User kick variables.
        newKickDiv,
        cancelKickDiv,
        approveKickDiv,
        kickMe,

    // Room deletion variables.
        newRoomDeletionDiv,
        cancelRoomDeletionDiv,
        approveRoomDeletionDiv,
        roomDeletionForm,
        deleteThisRoom,

    // Registration form variables.
        registrationForm,
        usNick,
        newCodeButton,
        usCode,
        codeImg,

    // User deletion variables.
        newUserDeletionDiv,
        cancelUserDeletionDiv,
        deleteMe,
        approveUserDeletionDiv,

    /* I choose P element even if its a block level element because inside
     * the newInvitationDiv, newRoomDeletionDiv I already have 2 spans so for the css
     * if I only want to style the middle span is a bit
     * inconvenient and that is why I choose p.
     * */
        noUsers = document.createElement('p'),


    // spamDetector variables.

    /* Key 0: Identify the counter.
     * Key 1: Identify the current case.
     * Key 2: Identify the last case.
     * */
        spamDetectorCounter = [0, null, null],

    /* Key 0: New time.
     * Key 1: Old time.
     * */
        spamDetectorDate = [null, null];

    // I preset the value of noUsers paragraph.
    setText(noUsers, langReference['W_0']);

    /************************************* Removal functions ***************************************** (2)
     *
     * removeLoadListeners - "DOMContentLoaded" and "load" are 2 different events I don't want them both
     *                       activate initChat so the first one to be activated should deactivate the second.
     *
     * removeElementsIdAndSecondClass - removes elements within a given container, it uses tagName to select elements
     *                                  from inside the container and removes the id and second class if set.
     * */

    function removeLoadListeners() {
        document.removeEventListener("DOMContentLoaded", initChat, false);
        window.removeEventListener("load", initChat, false);
    }

    function removeElementsIdAndSecondClass(container, tagName) {
        var elements = container.getElementsByTagName(tagName);

        for (var i = elements.length - 1; i >= 0; --i) {
            elements[i].removeAttribute('id');
            elements[i].setAttribute('class', elements[i].getAttribute('class').split(' ')[0]);
        }
    }

    /********************************************* Helper functions ************************************************* (14)
     *
     * spamDetector - Disable spam, uses the delay between messages to determine when there is spam.
     *
     * initChat - The core function used to activate the chat.
     *
     * checkAndSetValidRoom - Sends the room id to the server, if the room id is ok the server side will set the new room id
     *                        to be the current room, it also uses setCurrentRoom to set the current room on the client side.
     *
     * setCurrentRoom - The actual function used to set the current room on the client side.
     *
     * sortRemoveAddRooms - Sort / Remove / Add rooms.
     *
     * invitationApproveCancel - Invitations handler.
     *
     * deleteRoomApproveCancel - Room deletion handler.
     *
     * kickApproveCancel - User kicking handler.
     *
     * roomApproveCancel - New room creation handler.
     *
     * deleteUserApproveCancel - User deletion handler.
     *
     * initAjax - Used for every ajax call in this chat.
     *
     * minusDisplayHandler - Handel the display of the minus room, the display is depended whether the user own
     *                       any room he can delete.
     *
     * plusDisplayHandler - Handel the display of the plus room depending on the user rank.
     *
     * reloadPage - Reloads the page.
     *
     * */

    function selectUserHandler(container) {

        var users = container.getElementsByTagName('li');

        for(var i = 0; i < users.length; ++i) {

            users[i].lastChild.onclick = function(evt) {
                /* The span elements are inside the li elements the problem is that the event will bubble to the li element
                 * as you can see below I also have event handler for li click, the problem is that if I click the span
                 * the li handler will also be activated due to the bubble and that is why I have to use evt.stopPropagation();
                 * */
                evt.stopPropagation();

                // Slice is well explained below.
                setText(deleteMe, getText(evt.target.parentNode).slice(0, -1));
                show(newUserDeletionDiv, 50);
            };

            users[i].onclick = function(evt) {
                initAjax('php/ajax/setCurrentUser.php', function() {

                        if(this.readyState === 4 && this.status === 200) {
                            if(this.responseText === '1') {
                                reloadPage();
                            }else{
                                setError(this.responseText);
                            }
                        }

                    }, true, 'POST', {'Content-type' : 'application/x-www-form-urlencoded; charset=UTF-8'},
                    /* The reason for slicing the last character is because the last character is the x character
                     * inside the span element that inside the li element, so for example for the name 'aviel' we get
                     * 'avielx'.
                     * */
                    {'usNick' : getText(evt.target).slice(0, -1)});
            };
        }
    }

    function spamDetector() {

        var spamDetected = false;

        /* The / operator over the Date object will make the object return the milliseconds since midnight Jan 1, 1970.
         * Than i divide by 1000 to get seconds, and finally i use Math.floor to floor to cut milliseconds fractions.
         * */
        var currentTime = Math.floor(new Date() / 1000);

        if(spamDetectorDate[0] === null) { // There is no need to check key 1 for null.
            spamDetectorDate[0] = currentTime;
            spamDetectorDate[1] = currentTime;
        }else{
            spamDetectorDate[1] = spamDetectorDate[0];
            spamDetectorDate[0] = currentTime;
        }

        if((spamDetectorDate[0] - 2) <= spamDetectorDate[1]) {
            spamDetectorCounter[1] = 5; // The value 5 came from the 5 messages sequence I allow.
        }else if((spamDetectorDate[0] - 3) === spamDetectorDate[1]) {
            spamDetectorCounter[1] = 10; // The value 10 came from the 5 messages sequence I allow.
        }else if((spamDetectorDate[0] - 4) === spamDetectorDate[1]) {
            spamDetectorCounter[1] = 15; // The value 15 came from the 5 messages sequence I allow.
        }else if((spamDetectorDate[0] - 5) === spamDetectorDate[1]) {
            spamDetectorCounter[1] = 20; // The value 20 came from the 5 messages sequence I allow.
        }else{
            spamDetectorCounter[0] = 1;
            return false; // There is no need to continue if there is no spam.
        }

        switch(spamDetectorCounter[1]) {
            case 5:
                spamDetectorCounter[0] >= 5 ? spamDetected = true :
                    (spamDetectorCounter[2] === 5 ? ++spamDetectorCounter[0] : spamDetectorCounter[2] = 5);
                break;
            case 10:
                spamDetectorCounter[0] >= 10 ? spamDetected = true :
                    (spamDetectorCounter[2] === 10 ? ++spamDetectorCounter[0] : spamDetectorCounter[2] = 10);
                break;
            case 15:
                spamDetectorCounter[0] >= 15 ? spamDetected = true :
                    (spamDetectorCounter[2] === 15 ? ++spamDetectorCounter[0] : spamDetectorCounter[2] = 15);
                break;
            case 20:
                spamDetectorCounter[0] >= 20 ? spamDetected = true :
                    (spamDetectorCounter[2] === 20 ? ++spamDetectorCounter[0] : spamDetectorCounter[2] = 20);
                break;
        }

        return spamDetected;
    }

    function initChat() {


        var enLang = {

            'W_0' : 'No unsolicited connected users',
            'W_1' : 'Spam detected, please try in a few seconds',
            'W_2' : 'You can\'t create more rooms, please delete to recreate another one',
            'W_3' : 'Cannot delete active user, please retry in a minute',
            'W_4' : 'The chosen nickname doesn\'t exists or or deleted',
            'W_5' : 'Unknown error code',
            'W_6' : 'Server side has occurred, please try again later',
            'W_7' : 'The system detected an invalid operation',
            'W_8' : 'Error code',
            'W_9' : 'Error closing',
            'W_10' : 'The server failed to Identify your request'

        };

        var heLang = {

            'W_0' : 'לא נמצאו משתמשים מחוברים שלא הוזמנו',
            'W_1' : 'המערכת זיהתה ספאם אנא נסה/י שנית בעוד מספר שניות',
            'W_2' : 'הגעת למכסת החדרים המותרת לכל משתמש, תוכל/י למחוק חדרים וליצור חדשים',
            'W_3' : 'אין באפשרות המערכת למחוק משתמש פעיל, אנא עצור את פעילות המשתמש ונסה/י שנית כעבור כדקה',
            'W_4' : 'הכינוי הנבחר אינו קיים או נמחק',
            'W_5' : 'קוד שגיאה לא מוכר',
            'W_6' : 'שגיאת שרת התרחשה, אנא נסה\י במועד מאוחר יותר',
            'W_7' : 'המערכת זיהתה פעולה לא חוקית',
            'W_8' : 'קוד שגיאה',
            'W_9' : 'סגירת שגיאה',
            'W_10' : 'השרת נכשל בזיהוי בקשתך'
        };

        switch(document.getElementById('lang').value) {

            case 'he':
                langReference = heLang;
            break;

            default:
                langReference = enLang;

        }

        // So initChat won't be call by "load" event and "DOMContentLoaded" event.
        removeLoadListeners();

        // So I can know if I should activate script for chat or for chat registration.
        chatForm = document.getElementById('chatForm');
        errorParagraph = document.getElementById('errorParagraph');

        // I don't need to predefine this variable because there will be no other use of this variable than the following onclick event.
        var closeErrorParagraph = document.getElementById('closeErrorParagraph');

        // The if statement is for cases where user wants to register and there is no error presented yet.
        if(closeErrorParagraph !== null) {
            closeErrorParagraph.onclick = function() {
                hide(errorParagraph, 50);
            };
        }

        if(chatForm !== null) {

            messagesContainer = document.getElementById('messagesContainer');
            communicationSection = document.getElementById('communicationSection');
            usMessage = document.getElementById('message');
            usersTable = document.getElementById('usersTable');
            roomsList = document.getElementById('roomsList');

            /* When a user first come into the chat the server will set the current room automatically to 0,
             * if the user entered another room the server will set to be that room, if the user refresh the page
             * server is still remember the last room the user entered and not 0, and that is why I need to set the current
             * room to 1 per refresh.
             * */
            checkAndSetValidRoom(roomsList, "*", messagesInRooms);

            cancelRoomDiv = document.getElementById('cancelRoomDiv');
            approveRoomDiv = document.getElementById('approveRoomDiv');
            newRoomDiv = document.getElementById('newRoomDiv');

            userToInvite = document.getElementById('userToInvite');
            newInvitationDiv = document.getElementById('newInvitationDiv');
            cancelInvitationDiv = document.getElementById('cancelInvitationDiv');
            approveInvitationDiv = document.getElementById('approveInvitationDiv');
            invitationForm = document.getElementById('invitationForm');

            newKickDiv = document.getElementById('newKickDiv');
            cancelKickDiv = document.getElementById('cancelKickDiv');
            approveKickDiv = document.getElementById('approveKickDiv');
            kickMe = document.getElementById('kickMe');

            newRoomDeletionDiv = document.getElementById('newRoomDeletionDiv');
            cancelRoomDeletionDiv = document.getElementById('cancelRoomDeletionDiv');
            approveRoomDeletionDiv = document.getElementById('approveRoomDeletionDiv');
            deleteThisRoom = document.getElementById('deleteThisRoom');
            roomDeletionForm = document.getElementById('roomDeletionForm');

            approveRoomDeletionDiv.onclick = function() {
                deleteRoomApproveCancel(true, newRoomDeletionDiv);
            };

            cancelRoomDeletionDiv.onclick = function() {
                deleteRoomApproveCancel(false, newRoomDeletionDiv);
            };

            approveKickDiv.onclick = function() {
                kickApproveCancel(true, newKickDiv);
            };

            cancelKickDiv.onclick = function() {
                kickApproveCancel(false, newKickDiv);
            };

            approveRoomDiv.onclick = function() {
                roomApproveCancel(true, newRoomDiv);
            };

            cancelRoomDiv.onclick = function() {
                roomApproveCancel(false, newRoomDiv);
            };

            approveInvitationDiv.onclick = function() {
                invitationApproveCancel(true, newInvitationDiv);
            };

            cancelInvitationDiv.onclick = function() {
                invitationApproveCancel(false, newInvitationDiv);
            };

            usMessage.onkeyup = function() {
                colors(usMessage, regExpPatterns.usMessage);
            };


            /* This self executing function is the core functionality of this chat,
             * its update everything, users, messages and rooms.
             * */


             setInterval(function() {

                initAjax('php/ajax/getData.php', function() {

                        if(this.readyState === 4 && this.status === 200) {

                            if(this.responseText === '-1') {
                                reloadPage();
                            }else{
                                var splitResult = this.responseText.split('SEP');

                                var messages = splitResult[0];
                                var users = splitResult[1];
                                var newRooms = splitResult[2];
                                var removedRooms = splitResult[3];
                                var rank = parseInt(splitResult[4]);

                                var currentRoom = document.getElementById('currentRoom');
                                var currentRoomInnerText;

                                /* As you can see the problem is that currentRoom may be not be set yet because the ajax response
                                 * from checkAndSetValidRoom above is delayed, what will happen is that I will not have element
                                 * with currentRoom id, if there is no element than there is no innerText, so until the server
                                 * response I set it to "*", the value should be anyway "*", so there is no problem with sending "*".
                                 * */
                                if(currentRoom !== null) {
                                    currentRoomInnerText = getText(currentRoom);
                                }else{
                                    currentRoomInnerText = '*';
                                }

                                insertMessagesToRooms(messages, messagesInRooms);
                                appendMessages(messagesInRooms, currentRoomInnerText);
                                plusDisplayHandler(roomsList, rank);
                                sortRemoveAddRooms(roomsList, newRooms, removedRooms, currentRoomInnerText);
                                usersTable.innerHTML = users;
                                addRowsHandler(usersTable);
                                addRoomsHandler(roomsList, messagesInRooms);
                            }
                        }

                    }, true, 'POST', {'Content-type' : 'application/x-www-form-urlencoded; charset=UTF-8'},
                    {'ajaxCounter' : ajaxCounter});
                ++ajaxCounter;

             }, 2000);

            chatForm.onsubmit = function() {
                if(!spamDetector()) {
                    if(regExpPatterns.usMessage.test(usMessage.value) && usMessage.value.trim().length > 0) {
                        initAjax('php/ajax/addMessage.php', function() {

                                if(this.readyState === 4 && this.status === 200) {

                                    if(this.responseText !== '1') setError(7);

                                    usMessage.value = '';
                                    colors(usMessage, regExpPatterns.usMessage);
                                }

                            }, true, 'POST', {'Content-type' : 'application/x-www-form-urlencoded; charset=UTF-8'},
                            {'usMessage' : usMessage.value});
                    }else{
                        /* The reason for calling colors() before the focus() is because if I have send a message than the value will be ''
                         * but the className will remain green from the previous message I have send only after keyup event the className
                         * will be updated so here I call colors without having to wait for the user to trigger keyup event.
                         * */
                        colors(usMessage, regExpPatterns.usMessage);
                        usMessage.focus();
                    }
                }else{
                    setError(2);
                }

                return false;
            };
        }else{

            registrationForm = document.getElementById('registrationForm');
            var mainUserSelection = document.getElementById('mainUserSelection');

            if(registrationForm !== null) {
                registrationForm = document.getElementById('registrationForm');
                usNick = document.getElementById('usNick');
                newCodeButton = document.getElementById('newCodeButton');
                usCode = document.getElementById('usCode');
                codeImg = document.getElementById('codeImg');

               /* The cM parameter is used so some browser, as I tested firefox, may decide not to change the src captcha.php again.
                * They will not reset src attribute to its previous value, so I have to add parameter.
                *
                * cM stands for change me.
                * */


                newCodeButton.onclick = function() {
                    codeImg.src = "php/captcha.php?cM=" + (new Date() / 1000);
                };

                usNick.onkeyup = function() {
                    colors(usNick, regExpPatterns.usNick);
                };

                registrationForm.onsubmit = function() {

                    if(!regExpPatterns.usNick.test(usNick.value)) {

                        /* The reason for calling colors() before the focus() is because if I have send a message than the value will be ''
                         * but the className will remain green from the previous message I have send only after keyup event the className
                         * will be updated so here I call colors without having to wait for the user to trigger keyup event.
                         * */
                        colors(usNick, regExpPatterns.usNick);
                        usNick.focus();
                        usCode.src = "php/captcha.php?cM=" + (new Date() / 1000); // The cM parameter is explained above.
                        // I only want to return false if there is a problem, otherwise reload the page.
                        return false;
                    }
                    return true;
                };
            }

            if(mainUserSelection !== null) {

                newUserDeletionDiv = document.getElementById('newUserDeletionDiv');
                cancelUserDeletionDiv = document.getElementById('cancelUserDeletionDiv');
                deleteMe = document.getElementById('deleteMe');
                approveUserDeletionDiv = document.getElementById('approveUserDeletionDiv');

                approveUserDeletionDiv.onclick = function() {
                    deleteUserApproveCancel(true, newUserDeletionDiv);
                };

                cancelUserDeletionDiv.onclick = function() {
                    deleteUserApproveCancel(false, newUserDeletionDiv);
                };

                selectUserHandler(mainUserSelection);
            }
        }
    }

    function checkAndSetValidRoom(container, roomId, messagesInRooms) {
        initAjax('php/ajax/validRoom.php', function() {
                if(this.readyState === 4 && this.status === 200) {
                    if(this.responseText !== '0')
                        setCurrentRoom(container, this.responseText.split('|')[0], this.responseText.split('|')[1], messagesInRooms);
                }
            }, true, 'POST', {'Content-type' : 'application/x-www-form-urlencoded; charset=UTF-8'},
            {'roomId' : roomId});
    }

    function setCurrentRoom(container, roomContext, roomId, messagesInRooms) {

        var newRoom = getRoomByInnerText(container, roomId);

        if(newRoom) {
            removeElementsIdAndSecondClass(container, 'div');
            newRoom.setAttribute('id', 'currentRoom');
            appendMessages(messagesInRooms, roomId);
            if(roomContext === 'own') newRoom.setAttribute('class', newRoom.getAttribute('class') + ' withInviteOption');
        }else{
            setError(0);
        }
    }

    function sortRemoveAddRooms(roomsList, newRooms, removedRooms, currentRoomInnerText) {
        var allDivs = roomsList.getElementsByTagName('div');
        var firstInvitedDiv;
        var userSwitchDiv;
        var minusDivIndex;

        for(var k = 0; k < allDivs.length; ++k) {
            if(allDivs[k].getAttribute('class') === 'invited') {
                /* Identify the firstInvitedDiv so new invited rooms can appear at the top of the invited rooms stack.
                 * If not found it will be appended to the end of all rooms stack.
                 * */
                firstInvitedDiv = allDivs[k];
            }else if(getText(allDivs[k]) === '↑↓') {
                // Identify the roomDeletionDiv so new own rooms can appear at the top of the own rooms stack.
                userSwitchDiv = allDivs[k];
            }else if(getText(allDivs[k]) === '-') {
                // This identity of minus div index is only to be send to minusDisplayHandler.
                minusDivIndex = k;
            }
        }

        if(newRooms !== '') {

            scrollTopFlag = true;

            var alreadyExistPattern = getAlreadyExistPattern(roomsList);

            /* I can't use separator from the server like the messages '</div>'.
             * the reason is because insertBefore, getText, getAttribute they all require dom node,
             * and converting every array value(contain html) into node
             * will wast more resources than the following 3 lines.
             * */
            var tmpDiv = document.createElement('div');
            tmpDiv.innerHTML = newRooms;

            var allNewRooms = tmpDiv.getElementsByTagName('div');

            for(var i = 0; i < allNewRooms.length; ++i) {

                if((new RegExp(alreadyExistPattern)).test(getText(allNewRooms[i]))) continue;

                if(allNewRooms[i].getAttribute('class') === 'invited') {
                    if(firstInvitedDiv === undefined) {
                        roomsList.insertBefore(allNewRooms[i].cloneNode(true), null);
                    }else{
                        firstInvitedDiv.parentNode.insertBefore(allNewRooms[i].cloneNode(true), firstInvitedDiv);
                    }
                }else if(allNewRooms[i].getAttribute('class') === 'own') {
                    userSwitchDiv.parentNode.insertBefore(allNewRooms[i].cloneNode(true), userSwitchDiv.nextSibling);
                }
            }
        }

        if(removedRooms !== '') {
            scrollTopFlag = true;

            /* removedRooms is returned from the server in the form of a regex pattern,
             * this pattern contain all the rooms ids that should be removed.
             * */
            var removedRoomsPattern = new RegExp(removedRooms);

            for (var j = allDivs.length - 1; j >= 0; --j) {
                if(removedRoomsPattern.test(getText(allDivs[j]))) {
                    /* As you can see messagesInRooms is not passed as a argument, this decision is made because this function
                     * already contain many parameters.
                     * */
                    if(getText(allDivs[j]) === currentRoomInnerText) {
                        // This function used to set the current room at the server.
                        checkAndSetValidRoom(roomsList, "*", messagesInRooms);
                    }

                    /* I need to cleanup the object in case I truncate all the rooms from the database so for example
                     * messagesInRooms will contain messages for room with 1, after I truncate the room will be removed
                     * but the messages for room id 1 will remain in the messagesInRooms object, so the problem is that
                     * the increment counter will reset and if the user will create new room the id will be 1 and the object
                     * with the old id 1 room messages will load the messages to the new id 1 room(after the truncate).
                     * */
                    messagesInRooms.splice(getText(allDivs[j]), 1);
                    roomsList.removeChild(allDivs[j]);
                }
            }
        }

        messagesContainer.style.height = (roomsList.clientHeight > 500 ? roomsList.clientHeight : 500) + 'px';
        if(scrollTopFlag) messagesContainer.scrollTop = messagesContainer.clientHeight;
        minusDisplayHandler(roomsList, minusDivIndex);
    }

    function invitationApproveCancel(approveCancel, newInvitationDiv) {

        if(approveCancel && invitationForm.style.display === 'block') {

            initAjax('php/ajax/addInvitation.php', function() {

                    if(this.readyState === 4 && this.status === 200)
                        if(this.responseText !== '1') setError(this.responseText);

                }, true, 'POST', {'Content-type' : 'application/x-www-form-urlencoded; charset=UTF-8'},
                {'userToInvite' : userToInvite.value});

            /* The reason for the above check is only to allow the ajax request to be send where the form is displayed,
             * the form should not be displayed in cases where there is no more users that have not yet invited and the user
             * should also be active, instead a message explaining the user that there is no more users to invite, if I will not
             * include the above check(invitationForm.style.display === 'block') even when the message will be shown and the
             * user click approve, the request to the server will be send with userToInvite.value, the problem is that userToInvite.value
             * may be the previous selected user from a different room where it was available to invite to users, or if the options
             * was not populated before the value will be "", the server may result in '0' for the first case(previous selected user)
             * or '0' in case of ('') is sent.
             * */
        }

        // I hide the div no matter if approve or cancel was clicked.
        hide(newInvitationDiv, 50);
    }

    function deleteRoomApproveCancel(approveCancel, newRoomDeletionDiv) {
        if(approveCancel) {
            initAjax('php/ajax/deleteRoom.php', function() {
                    if(this.readyState === 4 && this.status === 200)
                        if(this.responseText !== '1') setError(this.responseText);

                }, true, 'POST', {'Content-type' : 'application/x-www-form-urlencoded; charset=UTF-8'},
                {'deleteThisRoom' : deleteThisRoom.value});
        }

        // I hide the div no matter if approve or cancel was clicked.
        hide(newRoomDeletionDiv, 50);
    }


    function kickApproveCancel(approveCancel, newKickDiv) {
        if(approveCancel) {
            initAjax('php/ajax/addKick.php', function() {
                    if(this.readyState === 4 && this.status === 200)
                        if(this.responseText !== '1') setError(this.responseText);

                }, true, 'POST', {'Content-type' : 'application/x-www-form-urlencoded; charset=UTF-8'},
                {'kickMe' : getText(kickMe)});
        }

        // I hide the div no matter if approve or cancel was clicked.
        hide(newKickDiv, 50);
    }

    function roomApproveCancel(approveCancel, newRoomDiv) {
        if(approveCancel) {
            initAjax('php/ajax/addNewRoom.php', function() {

                if(this.readyState === 4 && this.status === 200)
                    if(this.responseText !== '1') setError(this.responseText);

            }, true, 'POST', {'Content-type' : 'application/x-www-form-urlencoded; charset=UTF-8'}, {});
        }

        // I hide the div no matter if approve or cancel was clicked.
        hide(newRoomDiv, 50);
    }

    function deleteUserApproveCancel(approveCancel, newUserDeletionDiv) {
        if(approveCancel) {
            initAjax('php/ajax/deleteUser.php', function() {

                    if(this.readyState === 4 && this.status === 200) {

                        if(this.responseText === '1') {
                            reloadPage();
                        }else{
                            setError(this.responseText);
                        }
                    }

                }, true, 'POST', {'Content-type' : 'application/x-www-form-urlencoded; charset=UTF-8'},
                {'deleteMe' : getText(deleteMe)});
        }

        // I hide the div no matter if approve or cancel was clicked.
        hide(newUserDeletionDiv, 50);
    }

    function initAjax(url, changeFunction, syn, method, headers, paramsObject) {
        var xhr;

        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest(); // for IE7+, Firefox, Chrome, Opera, Safari
        } else {
            xhr = new ActiveXObject("Microsoft.XMLHTTP"); // for IE6, IE5
        }

        xhr.onreadystatechange = changeFunction; // This line take place on true and false

        xhr.open(method, url, syn);

        for (var headerKey in headers) {
            if(headers.hasOwnProperty(headerKey))
                xhr.setRequestHeader(headerKey, headers[headerKey]);
        }

        var paramsString = '';

        for (var paramKey in paramsObject) {
            if(paramsObject.hasOwnProperty(paramKey))
                paramsString += (paramKey + '=' + paramsObject[paramKey] + '&');
        }

        paramsString = paramsString.slice(0, -1); // To remove the last '&'

        xhr.send(paramsString);
    }

    function minusDisplayHandler(roomsList, minusDivIndex) {
        var allDivs = roomsList.getElementsByTagName('div');

        var display = 'none';

        for(var i = 0; i < allDivs.length; ++i) {
            // The first case is for any own room presented, the second case is if the owner is currently on this room.
            if(allDivs[i].getAttribute('class') === 'own' || allDivs[i].getAttribute('class') === 'own withInviteOption') {
                display = 'block';
                break;
            }
        }

        allDivs[minusDivIndex].style.display = display;
    }

    function plusDisplayHandler(roomsList, rank) {
        var allDivs = roomsList.getElementsByTagName('div');
        var plusDivIndex;

        for(var i = 0; i < allDivs.length; ++i) {
            if(getText(allDivs[i]) === '+') {
                plusDivIndex = i;
                break;
            }
        }

        allDivs[plusDivIndex].style.display = (rank > 0 ? 'block' : 'none');
    }


    function reloadPage() {
        // To bypass POST warning I must reload page with full URL.
        window.location.href = window.location.protocol +'//'+ window.location.host + window.location.pathname;
    }

    /********************************************** Effects functions ************************************************ (3)
     *
     * show - same as fade in effect.
     *
     * hide - same as fade out effect.
     *
     * colors - Small colorful helper to allow the user know when the field is not ready to be send.
     * */

    function show(element, miliseconds) {
        element.style.display = 'block';
        var j = 0.1,
            intrv = setInterval(function() {
            // toString() is used just for better understanding the value inserted, style attribute accept strings.
            element.style.opacity = j.toString();
            if(j>1) clearInterval(intrv);
            j+=0.1;
        }, miliseconds);
    }

    function hide(element, miliseconds) {

        /* This all savedHandler concept is because after the user click the create room or invite user, etc button the hide is activate
         * the problem is that the hide take time to really hide the element(element.style.display = ''), so until then the user can create
         * rooms or invite, etc again and again the same user or if he is really fast even another user or two, anyway what I did is to remove the
         * handler from the start of the hiding and retrieve it after the element.style.display = '';
         * */
        var savedHandlers = [];

        var currentApproveCancel = element.getElementsByTagName('span');

        for(var i = 0; i < currentApproveCancel.length; ++i) {
            savedHandlers[i] = currentApproveCancel[i].onclick;
            currentApproveCancel[i].onclick = null;
        }

        var j = 0.9,
            intrv = setInterval(function() {
            // toString() is used just for better understanding the value inserted, style attribute accept strings.
            element.style.opacity = j.toString();
            if(j<0) {
                element.style.display = '';

                // After the effect is finished I want to return the handlers.
                for(var i = 0; i < currentApproveCancel.length; ++i) {
                    currentApproveCancel[i].onclick = savedHandlers[i];
                }

                clearInterval(intrv);
            }
            j-=0.1;

        }, miliseconds);
    }

    function colors(field, pattern) {
        if(field.value === '' || field.value.trim().length === 0) {
            field.setAttribute('class', 'neutral');
        }else if(pattern.test(field.value)) {
            field.setAttribute('class', 'valid');
        }else{
            field.setAttribute('class', 'invalid');
        }
    }


    /********************************* Add / Insert / append / setter / functions ************************************ (6)
     *
     *  setError - Display an error based on server/client error codes.
     *
     *  insertMessagesToRooms - Populate the messages array.
     *
     *  addRowsHandler - Add handlers for kicking users at users table.
     *
     *  appendMessages - This function used to populate the messagesContainer.
     *
     *  setText - Cross-browser innerText function.
     *
     *  addRoomsHandler - Every rooms update may result in new rooms and that is why this function will renew the
     *                    handler every update whether or not a new room added or not.
     * */

    function setError(errorCode) {
        console.log(errorCode);
        /* For cases where I set errorText = false I state that the error is the result of illegal action made by the user.
         * For cases where I set errorText = true I state that the error is the result server error.
         * */

        if(typeof errorCode === 'string') {

            if(errorCode.length === 0) {
                /* f the server validRequest() will fail to identify the client request, empty string
                 * will be returned, hence for cases where empty string returned, for that kind of cases I will
                 * set the errorCode to 19
                 * */
                errorCode = 19;
            } else {
                // parseInt is for cases where this.responseText is sent, not as the above case.
                errorCode = parseInt(errorCode);
            }


        }

        
        var errorText;

        switch (errorCode) {
            /* You may say why the reload here, well there may be a second before getData will return -1, maybe some other php
             * ajax file may return -1 before getData, in that case the reload will happen faster, if I won't return -1 from
             * all the ajax files I may result the default case in this switch for a second before getData will reload the page.
             * */
            case -1:
                reloadPage();
                break;
            case 0: // The room clicked was not found at the database.
                errorText = false;
                break;
            case 1: // When user try to enter nonexistent room.
                errorText = false;
                break;
            case 2:
                errorText = langReference['W_1'];
                break;
            case 3: // Invitation of a nonexistent user, or the user was already invited, or it is you.
                errorText = false;
                break;
            case 4: // Kicking yourself out of your own room is not legal
                errorText = false;
                break;
            case 5: // Kicking nonexistent user.
                errorText = false;
                break;
            case 6: // Kicking existent user but not invited to this room.
                errorText = false;
                break;
            case 7: // Sending message failed due to server error.
                errorText = true;
                break;
            case 8: // When user try to add room, getting the rank via the database was failed due to server error.
                errorText = true;
                break;
            case 9: // Your rank do not allow you to add new rooms.
                errorText = false;
                break;
            case 10:
                errorText = langReference['W_2'];
                break;
            case 11: // Creating new room failed due to server error.
                errorText = true;
                break;
            case 12: // When a user try to delete nonexistent room.
                errorText = false;
                break;
            case 13: // Room deletion was failed due to server error.
                errorText = true;
                break;
            case 14: // When user try to delete nonexistent user.
                errorText = false;
                break;
            case 15:
                errorText = langReference['W_3'];
                break;
            case 16: // User deletion failed due to server error.
                errorText = true;
                break;
            case 17:
            /* Most of the chances are that the user manipulate the DOM, but there may be a chance where
             * the user was deleted from another computer on the same IP, and that is why I don't set errorText = false;.
             * */
                errorText = langReference['W_4'];
                break;
            case 18:
            /* The idea is that the user shouldn't even see the '-' room div if he/she doesn't have any room/s he/she owns,
             * in that kind of case the '-' room div style.display should be none, if a user using an inspector will change
             * it display value to block than click it, the server will search and see that this user do not have its own
             * rooms so there is nothing to delete, but in case there is no rooms to delete the '-' shouldn't be displayed at all.
             * */
                errorText = false;
                break;

            // For cases where the server validRequest() failed to identify the request as valid.
            case 19:

                errorText = langReference['W_10'];

            break;

            default:
                errorText = langReference['W_5'];
        }

        if(errorText === true) errorText = langReference['W_6'];

        if(errorText === false) errorText = langReference['W_7'];

        errorParagraph.innerHTML = errorText + ',&nbsp;' + langReference['W_8'] + ':&nbsp;' + errorCode + '<span title="' + langReference['W_9'] + '" id="closeErrorParagraph">x</span>';

        document.getElementById('closeErrorParagraph').onclick = function() {
            hide(errorParagraph, 50);
        };    

        show(errorParagraph, 50);
    }

    function insertMessagesToRooms(messages, messagesInRooms) {

        /* Instead of having to create element
         * and than append the html and finally
         * use getElementsByTagNames, I split the
         * messages from the server using messages.split('</div>');
         * later on I add the '</div>' back.
         * */

        var afterSplit = messages.split('</div>');

        afterSplit.pop(); // Because when split result with no match there will be an empty string element [""].

        if(afterSplit.length > 0) {

            scrollTopFlag = true;
            var roomId;

            for(var i  = 0; i < afterSplit.length; ++i) {
                roomId = regExpPatterns.idExtractor.exec(afterSplit[i])[1];

                if(!(roomId in messagesInRooms)) {
                    messagesInRooms[roomId] = [];
                }
                messagesInRooms[roomId].push(afterSplit[i] + '</div>');
            }
        }else{
            scrollTopFlag = false;
        }
    }

    function addRowsHandler(tableContainer) {
        var kickTds = tableContainer.getElementsByClassName('kick');
        var currentRow;

        if(kickTds.length > 0) {
            for (var i = 0; i < kickTds.length; ++i) {

                currentRow = kickTds[i].parentNode;

                currentRow.onclick =  function(evt) {
                    if(newInvitationDiv.style.display === '' && newRoomDiv.style.display === '') {
                        setText(kickMe, getText(evt.target.parentNode.firstChild));
                        show(newKickDiv, 50);
                    }
                };
            }
        }
    }

    function appendMessages(messagesInRooms, currentRoomInnerText) {

        var currentRoomId = (currentRoomInnerText === '*' ? '0' : currentRoomInnerText);

        if(currentRoomId in messagesInRooms) {
            var insertedHtml = '';

            for(var i = 0; i < messagesInRooms[currentRoomId].length; ++i) {
                insertedHtml += messagesInRooms[currentRoomId][i];
            }

            messagesContainer.innerHTML = insertedHtml;

        }else{
            messagesContainer.innerHTML = '';
        }
    }

    function setText(element, text) {
        if(element.innerText) { // Chrome + IE
            element.innerText = text;
        }else{ // Firefox
            element.textContent = text;
        }
    }

    function addRoomsHandler(container, messagesInRooms) {

        var allDivs = container.getElementsByTagName("div");

        for(var i = 0; i < allDivs.length; ++i) {
            allDivs[i].onclick = function() {
                if(getText(this) === '+') {
                    // The style attribute only gives you information about inline styles, and that is why I check for ''.
                    if(newInvitationDiv.style.display === '' && newKickDiv.style.display === '' && newRoomDeletionDiv.style.display === '') show(newRoomDiv, 50);
                }else if(getText(this) === '↑↓') {
                    initAjax('php/ajax/sessionKill.php', function() {}, true, 'POST', {}, {});
                }else if(getText(this) === '-') {
                    // The style attribute only gives you information about inline styles, and that is why I check for ''.
                    if(newInvitationDiv.style.display === '' && newKickDiv.style.display === '' && newRoomDiv.style.display === '') {
                        initAjax('php/ajax/getRooms.php', function() {

                            if(this.readyState === 4 && this.status === 200) {

                                if(this.responseText === '-1') {
                                    reloadPage();
                                }else if(this.responseText === '18') {

                                    setError(18);

                                }else{

                                    deleteThisRoom.innerHTML = this.responseText;

                                    show(newRoomDeletionDiv, 50);
                                }
                            }
                        }, true, 'POST', {}, {});
                    }
                }else{
                    if(this.id !== 'currentRoom') {

                        checkAndSetValidRoom(container, getText(this), messagesInRooms);

                    }else if(this.getAttribute('class') === 'own withInviteOption') {

                        if(newRoomDiv.style.display === '' && newKickDiv.style.display === '' && newRoomDeletionDiv.style.display === '') {

                            initAjax('php/ajax/getUsers.php', function() {

                                if(this.readyState === 4 && this.status === 200) {

                                                                    
                                    if(this.responseText === '-1') reloadPage();

                                    if(this.responseText === '') {

                                        invitationForm.style.display = 'none';

                                        noUsers.style.display = 'block';

                                        newInvitationDiv.insertBefore(noUsers, approveInvitationDiv);

                                    }else{

                                        noUsers.style.display = 'none';

                                        invitationForm.style.display = 'block';

                                        userToInvite.innerHTML = this.responseText;

                                    }

                                    show(newInvitationDiv, 50);
                                }
                            }, true, 'POST', {}, {});
                        }
                    }
                }
            };
        }
    }


    /*********************************************** getter functions ************************************************ (3)
     *
     * getText -  Cross-browser getInnerText function.
     *
     * getRoomByInnerText - Gets the room inner text.
     *
     * getAlreadyExistPattern - The following function is used for the next scenario:
     *                          while the user is in the chat he/she deleted the browser history, the server
     *                          will renew the sessions, the problem is that now there server won't remember
     *                          what rooms it already sent to the client so there will be a duplication
     *                          rooms, so this function will return a regex pattern in the form of '*|+|5|4|2' with
     *                          rooms that exist, now javascriptcan check rooms coming from the server before it
     *                          insert them into the roomList.
     * */

    function getText(element) {
        if(element.innerText) { // Chrome + IE
            return element.innerText;
        }else{ // Firefox
            return element.textContent;
        }
    }

    function getRoomByInnerText(container, roomId) {
        var allDivs = container.getElementsByTagName("div");

        if(roomId === '0') roomId = '*';

        var returnedRoom; // I predefine returnedRoom in case the iteration couldn't find the room.

        for(var i = 0; i < allDivs.length; ++i) {
            if(getText(allDivs[i]) === roomId) {
                returnedRoom = allDivs[i];
                break;
            }
        }

        return (returnedRoom === undefined ? false : returnedRoom);
    }

    function getAlreadyExistPattern(roomsList) {
        var allDivs = roomsList.getElementsByTagName('div');

        var returnedPattern = '';
        var currentRoomInnerText;

        for(var i = 0; i < allDivs.length; ++i) {
            currentRoomInnerText = getText(allDivs[i]);

            if(currentRoomInnerText === '*' || currentRoomInnerText === '+' || currentRoomInnerText === '-' || currentRoomInnerText === '↑↓') continue;

            returnedPattern += (currentRoomInnerText + '|');
        }

        /* Slice the last character because for each iteration I add '|' but I want to remove the last '|', In case of
         * an empty string it will just slice empty string(there will not be any error).
         * */

        return ('^(' + (returnedPattern.slice(0, -1)) + ')$');
    }

    // This if/else statements are the first step to initiate the chat.
    if (document.readyState === "complete") {
        initChat();
    }else{
        // Preferred.
        document.addEventListener("DOMContentLoaded", initChat, false);

        // In case "DOMContentLoaded" is not supported, equal to window.onload.
        window.addEventListener("load", initChat, false);
    }

})(window, document);