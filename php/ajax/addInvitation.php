<?php

require_once '../functions.php';

if(validRequest('userToInvite') &&
    validNick($_POST['userToInvite'])){

    $ip = checkLoginReturnByIp(getClientIp());

    header("Content-type: text/plain; charset=UTF-8");

    if($ip[1] === true){
        if(validToInvite($_POST['userToInvite'])){
            echo addInvitation($_POST['userToInvite']);
        }else{
            echo 3;
        }
    }else{
        echo -1;
    }
}