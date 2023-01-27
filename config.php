<?php
// HTTP
define('HTTP_SERVER', 'https://develop.100-dverei.ru/');

// HTTPS
define('HTTPS_SERVER', 'https://develop.100-dverei.ru/');

// DIR
define('DIR_APPLICATION', '/home/r/rumluxsq/develop.100-dverei.ru/public_html/catalog/');
define('DIR_SYSTEM', '/home/r/rumluxsq/develop.100-dverei.ru/public_html/system/');
define('DIR_IMAGE', '/home/r/rumluxsq/develop.100-dverei.ru/public_html/image/');
define('DIR_STORAGE', dirname(dirname(__FILE__)).'/storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'rumluxsq_dverdev');
define('DB_PASSWORD', 'asw4Z&Q7');
define('DB_DATABASE', 'rumluxsq_dverdev');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');