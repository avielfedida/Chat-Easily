<?php

$chatRegistration = '
        <p id="errorParagraph"></p>
        <div id="mainNickContainer">
            <h1>' . $langReference['W_15'] . '</h1>
            <form method="post" action="" id="registrationForm">
                <label for="usNick">' . $langReference['W_16'] . '</label>
                <input autocomplete="off" class="neutral" maxlength="17" id="usNick" name="usNick" type="text">
                <label for="usCode">' . $langReference['W_10'] . '</label>
                <input autocomplete="off" maxlength="5" id="usCode" name="usCode" type="text">
                <img src="images/refresh.png" title="' . $langReference['W_17'] . '" id="newCodeButton">
                <img src="php/captcha.php" id="codeImg">
                <input type="submit" value="' . $langReference['W_18'] . '" name="connect">
            </form>
        </div>';

$chat = '
        <p id="errorParagraph"></p>
        <div id="mainChatContainer" class="clearFix">
            <h1>' . $langReference['W_19'] . '</h1>
            <div id="communicationSection">
                <h2>' . $langReference['W_20'] . '</h2>
                <div id="roomsList"><h3>' . $langReference['W_21'] . '</h3><div title="' . $langReference['W_22'] . '" class="native">*</div><div title="' . $langReference['W_23'] . '" class="native">+</div>
                <div title="' . $langReference['W_24'] . '" style="display:none;" class="native">-</div><div class="native" title="' . $langReference['W_25'] . '">↑↓</div></div>
                <div id="messagesContainer" class="clearFix"><h3>Messages</h3></div>
                <div id="addMessageForm">
                    <h3>' . $langReference['W_26'] . '</h3>
                    <form method="post" action="" id="chatForm">
                        <label  for="message">↓&nbsp;' . $langReference['W_27'] . '&nbsp;↓</label>
                        <input autocomplete="off" class="neutral" maxlength="200" id="message" name="message">
                        <input type="submit" value="' . $langReference['W_30'] . '">
                    </form>
                </div>
            </div>
            <div id="loggedSection">
                <h2>' . $langReference['W_28'] . '</h2>
                <table id="usersTable"></table>
            </div>
        </div>';
