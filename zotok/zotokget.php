<?php
require("zotok.php");

$usage="Usage: 'php zotokget.php code [token_file_name]'.\nGet code: 'https://app.zonky.cz/api/oauth/authorize?client_id=mujrobot&redirect_uri=https://app.zonky.cz/api/oauth/code&response_type=code&scope=SCOPE_APP_BASIC_INFO+SCOPE_INVESTMENT_READ+SCOPE_INVESTMENT_WRITE&state=z'\n";
if(!isset($argv[1])) die ($usage);
if(!isset($argv[2])) $file="access_token"; else $file=$argv[2];
$code=$argv[1];

ZoTokGet($code,$file);

?>
