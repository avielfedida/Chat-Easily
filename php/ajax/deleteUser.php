<?php

require_once '../functions.php';

if(validRequest('deleteMe') &&
    validNick($_POST['deleteMe'])){

    header("Content-type: text/plain; charset=UTF-8");

    if(nickExistent($_POST['deleteMe'])){
        if(isActive($_POST['deleteMe'])){
            echo 15;
        }else{
            if(deleteUser($_POST['deleteMe']) !== 1){
                echo 16;
            }else{
                echo 1;
            }
        }
    }else{
        echo 14;
    }
}