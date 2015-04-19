<?php

/* For backward compatibilities I won't be using someFunc()[]
 * I'm using the $bcp variable to hold for a temporary time the array and than I reference the key I want.
 *
 * Another problem is that I can't initiate variable as array with array literal: $var = [];, Instead I must use $var = array();
 *
 * Both explained here: http://php.net/manual/en/language.types.array.php
 * */

require_once 'config.php';
require_once 'language.php';
require_once 'largeStrings.php';

$dbh = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT, DB_USER, DB_PASS, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

/************************************************ Getter functions *************************************************** (16)
 *
 * getUsersToSelect - Returned html that represent the users by a specified users array.
 *
 * getUserRoomsNumber - Gets the number of open rooms specific user have.
 *
 * getNick - Gets user nick by a specified id.
 *
 * getRoomById - Get a specific room ro_owner_id, ro_invited_id by a specified room id.
 *
 * getId - Gets a specific user id by a specified user nick.
 *
 * getMessages - get last inserted messages.
 *
 * getInvitedUsers - Use to check invitation list.
 *
 * getUsers - Check if users logged or logout from a specified room and gets them.
 *
 * getClientIp - Gets the user ip address.
 *
 * getData - The core function used to update the chat.
 *
 * getRoomsIds - Get rooms ids which user relate to owner/invited.
 *
 * getRoomsByRegExp - Get rooms by specified regex pattern.
 *
 * getRankName - Gets rank name by rank code.
 *
 * getUserByIp - Gets user by specific ip address.
 *
 * getOwnedRoomsByUserId - Gets user owned rooms by specified user id.
 *
 * getRankCodeById - Gets user rank code by specified user id.
 * */

function getUsersToSelect($usersToSelect) {

    global $langReference;

    $returnedHtml = '';

    $returnedHtml .= '<div id="mainUserSelection"><ul>';

    foreach($usersToSelect as $v) {
        $returnedHtml .= '<li>' . $v['us_nick'] . '<span title="' . $langReference['W_29'] . '">x</span></li>';
    }

    $returnedHtml .= '</ul></div>';

    return $returnedHtml;
}

function getOwnedRoomsByUserId($usId) {
    return $GLOBALS['dbh']->query("SELECT ro_id FROM rooms WHERE ro_owner_id = '$usId'")->fetchAll(PDO::FETCH_ASSOC);
}

function getUserRoomsNumber($usId) {
    $bcp = $GLOBALS['dbh']->query("SELECT us_rc FROM users WHERE us_id = '$usId'")->fetch(PDO::FETCH_NUM);
    return $bcp[0];
}

function getNick($usId) {
    $bcp = $GLOBALS['dbh']->query("SELECT us_nick FROM users WHERE us_id = '$usId'")->fetch(PDO::FETCH_NUM);
    return $bcp[0];
}

function getRoomById($roomId) {
    return $GLOBALS['dbh']->query("SELECT SUBSTRING_INDEX(CONCAT_WS(',', ro_owner_id, ro_invited_id), ',', 1) AS `ro_owner`, CONCAT_WS(',', ro_owner_id, ro_invited_id) AS `ro_combined` FROM rooms WHERE ro_id = '$roomId'")->fetch(PDO::FETCH_ASSOC);
}

function getId($usNick) {
    $bcp = $GLOBALS['dbh']->query("SELECT us_id FROM users WHERE us_nick = '$usNick'")->fetch(PDO::FETCH_NUM);
    return $bcp[0];
}

function getMessages() {
    /* Every 2 seconds the ajax will sent request for updates, So here I making sure the user gets only messages that was defined
     * at the last 2 seconds so for example:
     *
     * If time is 28 than 27-26 will be checked.
     * if time is 30 than 29-28 will be checked.
     *
     * As you can see there is no overlap(27-26, 27-28, 28-29), and because there is no overlap I will get only the last 2 seconds
     * messages updates.
     * */
    $New = time();
    $Old = $New - 3;
    return $GLOBALS['dbh']->query("SELECT mes_content, mes_room_id, mes_send_by_id, mes_dop FROM messages WHERE mes_dop < '$New' AND mes_dop > '$Old'")->fetchAll(PDO::FETCH_ASSOC);
}

function getInvitedUsers($roomId) {
    return $GLOBALS['dbh']->query("
        SELECT us_nick, us_rank
        FROM users
        WHERE (SELECT concat_ws(',', ro_owner_id, ro_invited_id)
        FROM rooms
        WHERE ro_id = '$roomId')
        REGEXP concat(us_id, '\,')
    ")->fetchAll(PDO::FETCH_ASSOC);
}

function getUsers($roomId, $negateRegex = '') {
    // $negateRegex is used in cases where we want users that are not in the current room but we want to invite them.

    $time = time() - 10; // I'm not using the new/old because not like the messages the innerHTML will replace the content so for example if an js alert will delay the script(js) execution the user will not show until the next getData.
    if($roomId === 0) {
        return $GLOBALS['dbh']->query("
        SELECT us_nick, us_rank
        FROM users
        WHERE us_ldoa > '$time'
    ")->fetchAll(PDO::FETCH_ASSOC);
    }else{
        return $GLOBALS['dbh']->query("
        SELECT us_nick, us_rank
        FROM users
        WHERE (SELECT concat_ws(',', ro_owner_id, ro_invited_id)
        FROM rooms
        WHERE ro_id = '$roomId')
        REGEXP concat(us_id, '\,') $negateRegex
        AND us_ldoa > '$time'
    ")->fetchAll(PDO::FETCH_ASSOC);
    }
}

function getClientIp() {
    if (getenv('HTTP_CLIENT_IP'))
        $ipAddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipAddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipAddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipAddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
        $ipAddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipAddress = getenv('REMOTE_ADDR');
    else
        $ipAddress = 'UNKNOWN';

    return filter_var($ipAddress);
}

function getData() {

    $returnedMessages = '';
    $returnedUsers = '';
    $returnedRooms = '';
    $returnedRank = getRankCodeById($_SESSION['us_id']);

    $allRooms = getRoomsIds($_SESSION['us_id']);

    $newRoomsString = implode('|', array_diff($allRooms, $_SESSION['all_rooms'])); // Php

    $removedRoomsString = implode('|', array_diff($_SESSION['all_rooms'], $allRooms)); // Javascript

    $_SESSION['all_rooms'] = $allRooms;

    $roomsToIterate = array();

    if($newRoomsString != '') { // Will evaluated as true when the session was created for the first time or when new rooms was added.
        $roomsToIterate = getRoomsByRegExp($newRoomsString);
    }else if($_POST['ajaxCounter'] === '0' && count($allRooms) > 0) { // Will evaluated as true when user refresh the page and if there is even rooms to show.
        $roomsToIterate = getRoomsByRegExp(implode('|', $allRooms));
    }

    $ownRooms = '';
    $invitedRooms = '';

    foreach($roomsToIterate as $v) {
        if($v['ro_owner_id'] == $_SESSION['us_id']) {
            $ownRooms .= '<div class="own">' . $v['ro_id'] . '</div>';
        }else{
            $invitedRooms .= '<div class="invited">' . $v['ro_id'] . '</div>';
        }
    }

    $returnedRooms .= ($ownRooms . $invitedRooms);

    foreach(getMessages() as $v) {

        $messageSender = getNick($v['mes_send_by_id']);
        $messageSide = 'right';

        if($_SESSION['us_nick'] != $messageSender) {
            $messageSide = 'left';
        }
        // % is used as a message split character for javascript.
        $returnedMessages .= '<div data-id="' . $v['mes_room_id'] . '" class="singleMessage ' . $messageSide . '">' . ($messageSide == 'left' ? ('<span class="sendBy">&nbsp;:&nbsp;' . ucwords($messageSender) . '</span>') : '') . $v['mes_content'] . '</div>';
    }


    $kickUserOptions = '';
    /* validRoom is used not to check that $_SESSION['current_room'] is a valid room but just to get the owner of the room
     * after I get the owner I can check if the owner is the currently logged user, if this is the case than I want to supply
     * him a way kick some users from his room.
     * */

    $bcp = getRoomById($_SESSION['current_room']);

    if($bcp['ro_owner'] === $_SESSION['us_id']) {
        $kickUserOptions = '<td class="kick" title="הרחקת משתמש">x</td>';
    }

    foreach(getUsers($_SESSION['current_room']) as $v) {
        $returnedUsers .= '<tr><td '. ($v['us_nick'] === $_SESSION['us_nick'] ? 'colspan="2"' : '') .' class="'  . getRankName($v['us_rank']) . '">' . $v['us_nick'] . '</td>' . ($v['us_nick'] === $_SESSION['us_nick'] ? '' : $kickUserOptions) . '</tr>';
    }

    updateLDoA($_SESSION['us_id']);

    return ($returnedMessages . 'SEP' . $returnedUsers . 'SEP' . $returnedRooms . 'SEP' . $removedRoomsString . 'SEP' . $returnedRank);
}

function getRoomsIds($usId) {
    return $GLOBALS['dbh']->query("SELECT ro_id FROM rooms WHERE concat_ws(',', ro_owner_id, ro_invited_id) REGEXP '$usId\,'")->fetchAll(PDO::FETCH_COLUMN);
}

function getRoomsByRegExp($exp) {
    return $GLOBALS['dbh']->query("SELECT ro_id, ro_owner_id FROM rooms WHERE ro_id REGEXP '$exp'")->fetchAll(PDO::FETCH_ASSOC);
}

function getRankName($rankCode) {

    switch($rankCode) {
        case 0:
            $rankName = 'disabled';
            break;
        default: // The actual default rank on the database is 1(rookie).
            $rankName = 'rookie';
    }

    return $rankName;
}

function getUserByIp($ip) {
    return $GLOBALS['dbh']->query("SELECT us_id, us_nick FROM users WHERE us_ip = '$ip'")->fetchAll(PDO::FETCH_ASSOC);
}

function getRankCodeById($usId) {
    $bcp = $GLOBALS['dbh']->query("SELECT us_rank FROM users WHERE us_id = '$usId'")->fetch(PDO::FETCH_NUM);
    return $bcp[0];
}

/************************************************** Add functions **************************************************** (5)
 *
 * addNewRoom - Adds a new room with a specific expiration date.
 *
 * addMessage - Adds a new message to a specified room.
 *
 * addInvitation - Send invitation to a specified user nick(gets the id out of the nick using getId function).
 *
 * addUser - Registers a new user.
 *
 * addKick - Kicks user out of invitations list.
 * */


function addNewRoom($ownerId) {

    $GLOBALS['dbh']->beginTransaction();

    $GLOBALS['dbh']->exec("UPDATE users SET us_rc = us_rc + 1 WHERE us_id = '$ownerId'");

    $stmt = $GLOBALS['dbh']->prepare("INSERT INTO rooms (ro_owner_id) VALUES (:ownerId)");
    $stmt->bindParam(':ownerId', $ownerId);

    $stmt->execute();

    if($GLOBALS['dbh']->commit()) {
        return true;
    }else{
        $GLOBALS['dbh']->rollback();
        return false;
    }
}

function addMessage($sendById, $content, $currentRoom) {

    $contWithEntities = htmlentities($content, ENT_QUOTES, "UTF-8");

    if($contWithEntities !== '') {
        $time = time();
        $stmt = $GLOBALS['dbh']->prepare("INSERT INTO messages (mes_send_by_id, mes_content, mes_room_id, mes_dop) VALUES (:sendBy, :content, :mes_room_id, :mes_dop)");
        $stmt->bindParam(':sendBy', $sendById);
        $stmt->bindParam(':content', $contWithEntities);
        $stmt->bindParam(':mes_room_id', $currentRoom);
        $stmt->bindParam(':mes_dop', $time);
        return $stmt->execute();
    }else{
        return 0;
    }
}

function addInvitation($nick) {
    $invitedUserId = (getId($nick) . ',');
    $currentRoom = $_SESSION['current_room'];
    return $GLOBALS['dbh']->exec("UPDATE rooms SET ro_invited_id = concat(ro_invited_id, '$invitedUserId') WHERE ro_id = '$currentRoom'");
}

function addUser($usNick, $ip) {
    $time = time();

    $stmt = $GLOBALS['dbh']->prepare("INSERT INTO users (us_nick, us_ip, us_ldoa) VALUES (:us_nick, :us_ip, :us_ldoa)");
    $stmt->bindParam(':us_nick', $usNick);
    $stmt->bindParam(':us_ip', $ip);
    $stmt->bindParam(':us_ldoa', $time);

    if($stmt->execute() === true) {
        $_SESSION['us_nick'] = $usNick;
        $_SESSION['us_id'] = $GLOBALS['dbh']->lastInsertId();
        $_SESSION['current_room'] = 0;
        $_SESSION['all_rooms'] = array();
        return true;
    }

    return false;
}

function addKick($nick) {

    if($nick !== $_SESSION['us_nick']) {
        if(nickExistent($nick) != null) {
            $returnedValue = 6;
            $currentRoom = $_SESSION['current_room'];

            foreach(getUsers($currentRoom) as $v) {
                if($v['us_nick'] === $nick) {
                    $usId = (getId($nick) . ','); // I wanted to add the , here because its more understandable than do '$usId,' in the query.
                    $returnedValue = $GLOBALS['dbh']->exec("UPDATE rooms SET ro_invited_id = (SELECT replace(ro_invited_id, '$usId', '')) WHERE ro_id = '$currentRoom'");
                    break;
                }
            }
            return $returnedValue;
        }else{
            $returnedValue = 5;
        }
    }else{
        $returnedValue = 4;
    }

    return $returnedValue;
}

/*********************************************** Validation functions ************************************************ (3)
 *
 * validToInvite - check that the user exists in the database, also that the user is not
 *                 inviting himself or anyone else that already invited.
 *
 * validRequest - check for HTTP_REFERER(http/s://specifiedDomain/), the REQUEST_METHOD to be equal to POST,
 *                and if requested it will check if a specific post key is set.
 *
 * validNick - check if a nick is valid using regexp pattern.
 * */

function validToInvite($nick) {
    if(nickExistent($nick)) {
        $returnedValue = true;
        foreach(getInvitedUsers($_SESSION['current_room']) as $v) {
            if($v['us_nick'] === $nick) {
                $returnedValue = false;
                break;
            }
        }
    }else{
        $returnedValue = false;
    }

    return $returnedValue;
}

function validRequest($postKey = 'DEFAULT') {

    /* As you can see I set $_POST['DEFAULT'] for the isset($_POST[$postKey]) check, there may be a case where i call
     * validRequest without parameter so the default parameter will be 'DEFAULT', but there is no any $_POST['DEFAULT'] defined
     * so I have to define it myself, the worst case that can happen is that someone will change the name in the input field via
     * some dom inspector, that will cause validRequest to return false which is ok with me.
     * */

    $_POST['DEFAULT'] = true;



    if(isset($_SERVER["HTTP_REFERER"]) && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST[$postKey])) {


        $getDomainPattern = '/^(https?\:\/\/)?([a-z\d]{1,5}\.)?([\da-z\-]+)\.[a-z\d\-\_\~\:\/\?\#\[\]\.\@\!\$\&\'\(\)\*\+\,\;\=]+$/iu';

        preg_match($getDomainPattern, DOMAIN, $domainMatches);
        preg_match($getDomainPattern, $_SERVER["HTTP_REFERER"], $referrerMatches);


        if(isset($domainMatches[3]) && isset($referrerMatches[3])) {
            $filteredDomain = $domainMatches[3];
            $filteredReferrer = $referrerMatches[3];

            return ($filteredDomain === $filteredReferrer ? true : false);
        }
    }

    return false;
}

function validNick($nick) {
    if(preg_match('/^[a-zא-ת\d]{2,8}(\s[a-zא-ת\d]{1,8})?$/iu', $nick) === 1)
        return true;
    return false;
}

/************************************************ Existence functions ************************************************* (2)
 *
 * roomExistent - Check room existent in database.
 *
 * nickExistent - Used to check if user exists in the database.
 * */

function roomExistent($roId) {
    $bcp = $GLOBALS['dbh']->query("SELECT 1 FROM rooms WHERE ro_id = '$roId'")->fetch(PDO::FETCH_NUM);

    if($bcp[0] === '1')
        return true;
    return false;
}

function nickExistent($usNick) {
    $bcp = $GLOBALS['dbh']->query("SELECT 1 FROM users WHERE us_nick = '$usNick'")->fetch(PDO::FETCH_NUM);

    if($bcp[0] === '1')
        return true;
    return false;
}


/************************************************** Helper functions ************************************************* (8)
 *
 * initSessionKillerSequence - Used to terminate all sessions.
 *
 * isActive - Used to check if users is active.
 *
 * deleteUser - Delete users by a specified user nick if this nick relate to a specific ip.
 *
 * setCurrentUser - When multiple users use the same IP I let them choose which user they want to use.
 *
 * removeRoomById - Remove a specific room by a given room id and also decrement the user rooms number by a specific user id.
 *
 * updateLDoA - Updates the last time a javascript ajax request was made by specific user to the server.
 *
 * getErrorString - Only used by chat registration form to return error strings as html.
 *
 * checkLoginReturnByIp - Check if user is login, if no than check the database by specified ip address.
 *
 * chatInit - The core function which initiate the chat.
 * */

function initSessionKillerSequence()
{
    // Initialize the session.
    // If you are using session_name("something"), don't forget it now!
    session_save_path(SESSION_SAVE_PATH);
    session_start();

    // Unset all of the session variables.
    $_SESSION = array();

    // If it's desired to kill the session, also delete the session cookie.
    // Note: This will destroy the session, and not just the session data!
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Finally, destroy the session.
    session_destroy();
}

function isActive($usNick) {
    $time = time() - 10;
    $bcp = $GLOBALS['dbh']->query("SELECT 1 FROM users WHERE us_nick = '$usNick' AND us_ldoa > '$time'")->fetch(PDO::FETCH_NUM);

    if($bcp[0] === '1')
        return true;
    return false;
}

function deleteUser($usNick) {
    /* The "AND us_ip = '$ip'" is kind of a check to make sure no one could delete someone else user unless
     * the user ip address matches the nick to be deleted.
     * */
    $ip  = getClientIp();

    return ($ip == null ? 0 : $GLOBALS['dbh']->exec("DELETE FROM users WHERE us_nick = '$usNick' AND us_ip = '$ip'"));
}

function setCurrentUser($usNick) {

    if(nickExistent($usNick) != null) {

        $usId = getId($usNick);

        // There is no need to session_start because setCurrentUser.php already calls checkLoginReturnByIp which call session_start().
        $_SESSION['us_nick'] = $usNick;
        $_SESSION['us_id'] = $usId;
        $_SESSION['all_rooms'] = array();
        $_SESSION['current_room'] = 0;
        updateLDoA($usId);

        return true;
    }

    return false;
}

function removeRoomById($roId, $usId) {

    $GLOBALS['dbh']->beginTransaction();

    $GLOBALS['dbh']->exec("UPDATE users SET us_rc = us_rc - 1 WHERE us_id = '$usId'");

    $GLOBALS['dbh']->exec("DELETE FROM rooms WHERE ro_id = '$roId'");

    if($GLOBALS['dbh']->commit()) {
        return true;
    }else{
        $GLOBALS['dbh']->rollback();
        return false;
    }
}

function updateLDoA($usId) {
    $time = time();
    $GLOBALS['dbh']->exec("UPDATE users SET us_ldoa = '$time' WHERE us_id = '$usId'");
}

function getErrorString($code) {

    global $langReference;

    $errorString = '';

    switch($code) {
        case 0:
            $errorString = $langReference['W_8'];
            break;
        case 1:
            $errorString = $langReference['W_9'];
            break;
        case 2:
            $errorString = $langReference['W_10'];
            break;
        case 3:
            $errorString = $langReference['W_11'];
            break;
        case 4:
            $errorString = $langReference['W_12'];
            break;
        case 5:
            $errorString = $langReference['W_13_BEFORE'] . MAX_USERS_PER_IP . $langReference['W_13_AFTER'];
    }
    /* The reason for display: block; is:
     * The javascript hide function works with elements that do not displayed as default via css rule display: none;,
     * so if I want this elements to appear I set style.display = 'block';, and when I use hide it will set style.display = ''
     * so the css rule display: none; will take place, same is done with errorParagraph this paragraph has css rule display: none;
     * and If I want to show it I should use style="display:block;" as below, the hide function will set style.display = '' so the
     * css rule display: none; will take place.
     * */
    return ('<p style="display:block;" id="errorParagraph">' . $errorString . '<span title="' . $langReference['W_14'] . '" id="closeErrorParagraph">x</span></p>');
}

function checkLoginReturnByIp($ip) {
    if($ip != false) {
        session_save_path(SESSION_SAVE_PATH);
        session_start();

        if(isset($_SESSION['us_nick']) && isset($_SESSION['us_id']) && isset($_SESSION['all_rooms']) && isset($_SESSION['current_room'])) {
            return array($ip, true);
        }else{
            $dbResult = getUserByIp($ip);

            if(count($dbResult) > 0) return array($ip, $dbResult);
        }
    }
    return array($ip, false);
}

function chatInit() {

    $ip = getClientIp();

    if($ip != null) {

        $printedChat = false;

        $bcp = checkLoginReturnByIp($ip);

        // Used to allow the client side to detect the language, the inline styling to avoid css problems. 
        echo '<span style="display:none;" id="lang">' . CHAT_LANGUAGE . '</span>';

        if($bcp[1] === true) {
            $printedChat = true;
            echo $GLOBALS['chat'];
        }else{


            if(isset($_POST['connect']) && isset($_POST['usCode']) && isset($_POST['usNick'])) {

                        if(!isset($_POST['usNick'])) {
                            echo getErrorString(1);
                            /* I check to see if its even set, because I may present x top users list(for example there are 3 top users I allow and
                             * at the moment the user own 3 users, I dont even show the registration), so the registration form won't appear and thus
                             * I can't check only !isset($_POST['usCode']).
                             *
                             * The last else if(isset($_POST['usCode']) && $_POST['usCode'] != '') is for cases where the user as decided becouse the above case
                             * to delete one of his users, as result of that the page will refresh, and if I was just using else instead of else if the
                             * $_SESSION['security_code'] == $_POST['usCode'] would echo the same error as the previous
                             * isset($_POST['usCode']) && $_POST['usCode'] == '', so the secret is to check with the second else if that the $_POST is set and its value is not '',
                             * and that is how only for cases where the $_POST['usCode'] is indeed set AND(not like before else if) not empty only then check the
                             * $_SESSION['security_code'] == $_POST['usCode']
                             *
                             * */
                        }else if(isset($_POST['usCode']) && $_POST['usCode'] == '') {
                            echo getErrorString(2);
                        }else if(isset($_POST['usCode']) && $_POST['usCode'] != '') {
                            if(validNick($_POST['usNick'])) {
                                if(isset($_SESSION['security_code']) && $_SESSION['security_code'] == $_POST['usCode']) {
                                    $usNick = $_POST['usNick'];
                                    if(!nickExistent($usNick)) {
                                        if(addUser($usNick, $ip)) {
                                            $printedChat = true;
                                            echo $GLOBALS['chat'];
                                        }else{
                                            echo getErrorString(0);
                                        }
                                    }else{
                                        echo getErrorString(3);
                                    }
                                }else{
                                    echo getErrorString(2);
                                }
                            }else{
                                echo getErrorString(4);
                            }
                        }
                    }


            if(gettype($bcp[1]) === 'array') {

                if(count($bcp[1]) < MAX_USERS_PER_IP) {

                    if($printedChat === false) {
                        echo $GLOBALS['chatRegistration'];
                        echo getUsersToSelect($bcp[1]);
                    }
                }else{
                    echo getErrorString(5);
                    echo getUsersToSelect($bcp[1]);
                }

            }else{
                if($printedChat === false) echo $GLOBALS['chatRegistration'];
            }
        }

    }else{
        echo getErrorString(2);
        echo $GLOBALS['chatRegistration'];
    }
}