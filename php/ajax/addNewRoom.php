<?php

require_once '../functions.php';

if(validRequest()){

    $ip = checkLoginReturnByIp(getClientIp());

    header("Content-type: text/plain; charset=UTF-8");

    if($ip[1] === true){
        $userId = $_SESSION['us_id'];
        $userRank = getRankCodeById($userId);
        // The first step is to check if the query didn't returned false, after that we can use intval.
        if($userRank !== false){
            if(intval($userRank[0]) > 0){
                if(getUserRoomsNumber($userId) < MAX_ROOMS_PER_USER){
                    echo (addNewRoom($userId) === true ? 1 : 11);
                }else{
                    echo 10;
                }
            }else{
                echo 9;
            }
        }else{
            echo 8;
        }
    }else{
        echo -1;
    }
}