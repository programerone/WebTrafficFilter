<?php
# mysql connection
const MYSQL_HOST = '';
const MYSQL_DB	 = '';
const MYSQL_USER = '';
const MYSQL_PASS = '';

# verify that these file types are being downloaded?
const CSS_DOWNLOAD_CHECK = false;
const JS_DOWNLOAD_CHECK  = false;
const IMG_DOWNLOAD_CHECK = false;

# check database for these banned lists?
const CHECK_BANNED_USER_AGENTS = true;
const CHECK_BANNED_IPS	       = true;

# absolute path to the page where banned traffic will be sent
const BANNED_PAGE = '/banned_user.php';
?>
