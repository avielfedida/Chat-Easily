<p align="center">
	<img height="162" width="332" src="http://i.imgur.com/fZRAbn3.png">
</p>

---

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

4. Use `mysql -u username -p database_name < tables.sql` to import the tables into your database.

## Notes

1. The `fonts` folder contains captcha fonts, I included only 1 basic font, but you can add more, each captcha refresh may choose different font.

2. Supports IE10+

## Contact

Feel free to contact me at `avielfedida@gmail.com`.

###### Version: `1.0`

###### License: `MIT`