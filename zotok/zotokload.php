<?php
require("zotok.php");

$usage="Usage: 'php zotokload.php [token_file_name]'.\nOutputs token from file or refresh token if needed.\nOutputs 'Error.' and description on failure.";

if(!isset($argv[1])) $file="access_token"; else $file=$argv[1];
if(!file_exists($file)) die("$usage\nError. File '$file' not found.\n");

ZoTokLoad($file)

?>
