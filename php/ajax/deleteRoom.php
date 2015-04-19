<?php

require_once '../functions.php';

if(validRequest('deleteThisRoom')){

    $ip = checkLoginReturnByIp(getClientIp());

    header("Content-type: text/plain; charset=UTF-8");

    if($ip[1] === true){
        $deleteThisRoom = intval($_POST['deleteThisRoom']);

        if($deleteThisRoom > 0){

            if(roomExistent($deleteThisRoom)){
                echo (removeRoomById($deleteThisRoom, $_SESSION['us_id']) === true ? 1 : 13);
            }else{
                echo 12;
            }
        }else{
            echo 12; // For this case room 0 exist it is the main room but It will be easier to say the it doesn't exist.
        }
    }else{
        echo -1;
    }
}