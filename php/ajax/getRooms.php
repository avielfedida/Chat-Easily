<?php

require_once '../functions.php';

if(validRequest()){
    $ip = checkLoginReturnByIp(getClientIp());

    header("Content-type: text/plain; charset=UTF-8");

    if($ip[1] === true){

        $returnedRooms = 18;

        foreach(getOwnedRoomsByUserId($_SESSION['us_id']) as $k => $v){
            if($k === 0) $returnedRooms = '';
            $returnedRooms .= '<option value='. $v['ro_id'] .'>' . $v['ro_id'] . '</option>';
        }

        echo $returnedRooms;
    }else{
        echo -1;
    }
}