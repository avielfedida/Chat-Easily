<?php

require_once '../functions.php';

if(validRequest('usNick') &&
    validNick($_POST['usNick'])){

    header("Content-type: text/plain; charset=UTF-8");

    $ip = checkLoginReturnByIp(getClientIp());

    if($ip[1] === true){
        echo -1;
    }else{
        if(setCurrentUser($_POST['usNick'])){
            echo 1;
        }else{
            echo 17;
        }
    }
}