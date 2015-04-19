<?php

require_once '../functions.php';

if(validRequest("ajaxCounter")){

    header("Content-type: text/plain; charset=UTF-8");

    /* This renewSession() is very important because the user can while inside the chat delete his cookies,
     * after the user deleted his cookies the browser is no longer have the cookie to identify in front of the server
     * so the server sends another cookie to the browser but the new cookie don't know anything about us_nick or us_id
     * that was defined by chatInit() when the page load, so renewSession will renew the sessions values for the new session
     * cookie that was send to the browser and that is how the communication can stay untouched.
     * */

    $ip = checkLoginReturnByIp(getClientIp());

    if($ip[1] === true){
        echo getData();
    }else{
        echo -1;
    }
}