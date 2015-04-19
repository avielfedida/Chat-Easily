<?php

require_once '../functions.php';

if(validRequest()){

    $ip = checkLoginReturnByIp(getClientIp());

    header("Content-type: text/plain; charset=UTF-8");

    if($ip[1] === true){

        $returnedNegatedUsers = '';

        foreach(getUsers($_SESSION['current_room'], ' = 0 ') as $v){
            $returnedNegatedUsers .= '<option value='. $v['us_nick'] .'>' . $v['us_nick'] . '</option>';
        }

        echo $returnedNegatedUsers;
    }else{
        echo -1;
    }
}