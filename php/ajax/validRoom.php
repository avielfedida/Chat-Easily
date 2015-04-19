<?php

require_once '../functions.php';

if(validRequest('roomId')){

    $ip = checkLoginReturnByIp(getClientIp());

    header("Content-type: text/plain; charset=UTF-8");

    if($ip[1] === true){
        if($_POST['roomId'] == '*'){
            $_SESSION['current_room'] = 0;
            echo '*|' . 0;
        }else{
            $roomId = intval($_POST['roomId']);
            $userId = $_SESSION['us_id'];
            if($roomId > 0){
                $roomArray = getRoomById($roomId);
                if($userId == $roomArray['ro_owner']){
                    $_SESSION['current_room'] = $roomId;
                    echo 'own|' . $roomId;
                }else if(preg_match('/'. $userId . '\,/', $roomArray['ro_combined'])){
                    $_SESSION['current_room'] = $roomId;
                    echo 'invited|' . $roomId;;
                }
            }else{
                echo 1;  // For this case room 0 exist it is the main room but It will be easier to say the it doesn't exist.
            }
        }
    }else{
        echo -1;
    }
}