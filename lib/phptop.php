<?php
function phptop_end() { apc_delete($GLOBALS['phptop_key']); }
$GLOBALS['phptop_key'] = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . ($_SERVER['QUERY_STRING']? '?' . $_SERVER['QUERY_STRING']: '') . ':' . uniqid();
apc_store($GLOBALS['phptop_key'], true);
register_shutdown_function('phptop_end');
