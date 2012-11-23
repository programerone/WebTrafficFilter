<?
# lock the control panel by ip?
const IP_LOCK  = false;
const ADMIN_IP = '';

# mysql connection
const MYSQL_HOST = 'localhost';
const MYSQL_DB	 = 'traffix';
const MYSQL_USER = 'user';
const MYSQL_PASS = 'password';

# verify that these file types are being downloaded?
const CSS_DOWNLOAD_CHECK = false;
const JS_DOWNLOAD_CHECK  = false;
const IMG_DOWNLOAD_CHECK = false;

# check database for these banned lists?
const CHECK_BANNED_USER_AGENTS = true;
const CHECK_BANNED_IPS	       = true;

# .htaccess manipulation
const ALLOW_HTACCESS_OVERWRITE = false;
const HTACCESS_FILE_PATH = '/www/traffix/';

# absolute path to the page where banned traffic will be sent
const BANNED_PAGE = '/banned_user.php';
?>
