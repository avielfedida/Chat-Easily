<?php

require_once '../functions.php';

if(validRequest("usMessage") &&
    preg_match('/^[\s\S]+$/', $_POST['usMessage']) &&
    strlen(trim($_POST['usMessage'])) > 0){

    $ip = checkLoginReturnByIp(getClientIp());

    header("Content-type: text/plain; charset=UTF-8");

    if($ip[1] === true){
        echo (addMessage($_SESSION['us_id'], $_POST["usMessage"], $_SESSION['current_room']) === true ? 1 : 7);
    }else{
        echo -1;
    }
}