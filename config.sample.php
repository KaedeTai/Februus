<?php

define('ROOT_PATH', '/var/www/rd');
define('LOG_PATH', ROOT_PATH . '/log');
define('LIB_PATH', ROOT_PATH . '/lib');

define('DB_HOST', '192.168.0.210');
define('DB_USER', 'februus');
define('DB_PASS', 'februus');
define('DB_NAME', 'februus');

set_include_path(LIB_PATH . ':' . ini_get('include_path'));

