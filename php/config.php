<?php

// The reason for using mb_ functions is for all kind of urls for example hebrew url that require mb_ functions.
mb_internal_encoding('UTF-8');

// Your domain name for example: domain.com
define('DOMAIN', 'YOUR DOMAIN NAME');

// Users constants

/* Users constants:
 *
 * MAX_ROOMS_PER_USER, defines how many rooms each user can open.
 *
 * MAX_USERS_PER_IP, defines how many nick names each user can have.
 * */
define('MAX_ROOMS_PER_USER', 3);

define('MAX_USERS_PER_IP', 3);

// Database constants
define('DB_NAME', '');
define('DB_HOST', 'localhost'); // Default is localhost
define('DB_USER', 'root'); // Default is root

/* The reason for base64_decode is in case someone is behind your back and you don't want him to see the plaintext password.
 * You should input your plain text password into base64_encode function and then use the input inside the base64_decode function.
 * Just go online and search for base64 encode, type your password and put the output here, or remove base64_decode function and
 * drop your password as plain text.
 * */
define('DB_PASS', base64_decode(''));
define('DB_PORT', 3306); // Default is 3306.

/* The chat is session based, so there should be no problem at all with the sessions, so using this variable as configuration constant
 * users won't have any problem with sessions.
 * 
 * default is '', its the same as php default, but I is highly recommend changing it, for example to C:/tmp or any other location
 * */
define('SESSION_SAVE_PATH', '');

/* The language to be used, default: english.
 * 
 * Available languages:
 * - English (denote by the 'en' string).
 * - Hebrew (denote by the 'he' string).
 * */
define('CHAT_LANGUAGE', 'en');