<?php

require_once '../functions.php';

if(validRequest('kickMe') &&
    validNick($_POST['kickMe'])){

    $ip = checkLoginReturnByIp(getClientIp());

    header("Content-type: text/plain; charset=UTF-8");

    if($ip[1] === true){
        echo addKick($_POST['kickMe']);
    }else{
        echo -1;
    }
}