<?php

$langReference = array();

// The english sentences is reversed in the sense of: '?Add new field' instead of 'Add new field?'

$en_lang = array(

    'W_0' => 'Cancel',
    'W_1' => '?Add new field',
    'W_2' => 'Open',
    'W_3' => 'Add',
    'W_4' => 'Room No&nbsp;&larr;&nbsp;',
    'W_5' => 'Banish',
    'W_6' => 'Delete',
    'W_7' => 'User nick&nbsp;&larr;&nbsp;',
    'W_8' => 'Server error',
    'W_9' => 'Please pick a nickname',
    'W_10' => 'Please enter the code',
    'W_11' => 'Nickname already exists, please pick another',
    'W_12' => 'Nickname format invalid, only letters/numbers between 2 - 17 are allowed',
    'W_13_BEFORE' => 'This chat is limited for up to&nbsp;',
    'W_13_AFTER' => '&nbsp;users per IP',
    'W_14' => 'Close error',
    'W_15' => 'Nickname selection/creation',
    'W_16' => 'Letters/Numbers only',
    'W_17' => 'Field code',
    'W_18' => 'Connection',
    'W_19' => 'Chat',
    'W_20' => 'Communication section',
    'W_21' => 'Room list',
    'W_22' => 'Main room',
    'W_23' => 'New room',
    'W_24' => 'Room deletion',
    'W_25' => 'Switch user',
    'W_26' => 'Message sending form',
    'W_27' => 'Sending messages is done through here',
    'W_28' => 'Connected users',
    'W_29' => 'User deletion',
    'W_30' => 'Send'

    );

$he_lang = array(

    'W_0' => 'בטל',
    'W_1' => 'להוסיף חדר חדש?',
    'W_2' => 'פתח',
    'W_3' => 'הוספה',
    'W_4' => 'מספר חדר',
    'W_5' => 'הרחק',
    'W_6' => 'מחיקה',
    'W_7' => 'כינוי משתמש',
    'W_8' => 'שגיאת שרת',
    'W_9' => 'אנא בחר/י כינוי.',
    'W_10' => 'יש להקליד את הקוד.',
    'W_11' => 'כינוי כבר קיים אנא בחר/י אחר.',
    'W_12' => 'פורמט כינוי שגוי יש להשתמש באותיות באנגלית/עברית ואו מספרים בלבד, בין 2 - 17 תווים',
    'W_13_BEFORE' => 'צ\'אט זה מוגבל לעד&nbsp;',
    'W_13_AFTER' => '&nbsp;משתמשים לכתובת IP אחת',
    'W_14' => 'סגירת שגיאה',
    'W_15' => 'בחירת/יצירת כינוי',
    'W_16' => 'אותיות ומספרים',
    'W_17' => 'קוד חדש',
    'W_18' => 'התחברות',
    'W_19' => 'צ\'אט',
    'W_20' => 'אזור התקשורת',
    'W_21' => 'רשימת החדרים',
    'W_22' => 'חדר ראשי',
    'W_23' => 'הוספת חדר',
    'W_24' => 'מחיקת חדר',
    'W_25' => 'החלפת משתמש',
    'W_26' => 'טופס שליחת ההודעות',
    'W_27' => 'שליחת הודעה זה פה',
    'W_28' => 'משתמשים מחוברים',
    'W_29' => 'מחיקת משתמש',
    'W_30' => 'שליחה'

    );

switch(CHAT_LANGUAGE) {

    case 'he':
        $langReference = $he_lang;
    break;

    default:
        $langReference = $en_lang;

}