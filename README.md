# chat-easily
> Easy to configure php + javascript chat.

## Installation

After you clone or download the folder:
	
	1. Checkout the `.htaccess` file, read the comments and make your own version for that file, you should decide what
	rules to put there, I added few basic rules, after you finish editing the `.htaccess` file and you know what rules
	the `.htaccess` contains open the `httpd.conf` and set `AllowOverride` value depending what rules used
	within your `.htaccess` file.

	2. Open `php.ini` and make sure to uncomment the following extensions:

		* php_mbstring.dll (mb_functions)
		* php_pdo_mysql.dll (mysql interactions)
		* php_gd2.dll (captcha)

	3. Open `config.php` within the `php` directory and set the constants.

## Notes

	1. The `fonts` folder contains captcha fonts, I included only 1 basic font, but you can add more, each captcha refresh may choose different font.

	2. Supports IE10+

###### Version: `1.0`

###### License: `MIT`