<!DOCTYPE html>
<html lang="en">
    <head>
        <title>ChatTitle</title>
        <script type="text/javascript" src="js/chat.min.js"></script>
        <link rel="stylesheet" href="css/main.min.css">
        <meta charset="utf-8">
    </head>
    <body dir="rtl">
    <?php
        require_once 'php/functions.php';
    ?>
    <div id="newRoomDiv">
        <span title="<?=$langReference['W_0']?>" id="cancelRoomDiv">x</span>
        <p><?=$langReference['W_1']?></p>
        <span title="<?=$langReference['W_2']?>" id="approveRoomDiv">&rsaquo;</span>
    </div>
    <div id="newInvitationDiv">
        <span title="<?=$langReference['W_0']?>" id="cancelInvitationDiv">x</span>
        <form action="" id="invitationForm" method="post">
            <label for="userToInvite"><?=$langReference['W_7']?></label>
            <select name="userToInvite" id="userToInvite"></select>
        </form>
        <span title="<?=$langReference['W_3']?>" id="approveInvitationDiv">&rsaquo;</span>
    </div>
    <div id="newKickDiv">
        <span title="<?=$langReference['W_0']?>" id="cancelKickDiv">x</span>
        <p><?=$langReference['W_5']?>&nbsp;<span id="kickMe"></span>?</p>
        <span title="<?=$langReference['W_5']?>" id="approveKickDiv">&rsaquo;</span>
    </div>
    <div id="newRoomDeletionDiv">
        <span title="<?=$langReference['W_0']?>" id="cancelRoomDeletionDiv">x</span>
        <form action="" id="roomDeletionForm" method="post">
            <label for="deleteThisRoom"><?=$langReference['W_4']?></label>
            <select name="deleteThisRoom" id="deleteThisRoom"></select>
        </form>
        <span title="<?=$langReference['W_6']?>" id="approveRoomDeletionDiv">&rsaquo;</span>
    </div>
    <div id="newUserDeletionDiv">
        <span title="<?=$langReference['W_0']?>" id="cancelUserDeletionDiv">x</span>
        <p><?=$langReference['W_6']?>&nbsp;<span id="deleteMe"></span>?</p>
        <span title="<?=$langReference['W_5']?>" id="approveUserDeletionDiv">&rsaquo;</span>
    </div>
    <?php
        chatInit();
    ?>
    </body>
</html>