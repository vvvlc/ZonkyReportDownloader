<?php

//$zotokdbg=true; // Zakomentovat pro zruseni    

function ZoTokGet($code,$file)
	{
/*
Code: Zonky poskytne kod po prihlaseni zde:
https://app.zonky.cz/api/oauth/authorize?client_id=mujrobot&redirect_uri=https://app.zonky.cz/api/oauth/code&response_type=code&scope=SCOPE_APP_BASIC_INFO+SCOPE_INVESTMENT_READ+SCOPE_INVESTMENT_WRITE&state=opaque
File: Soubor pro ulozeni access_token, refresh_token, doba platnosti a dalsi v json formatu.
Return value: access_token nebo false 
*/
	global $zotokdbg;
	#$credentials=base64_encode("vitezslav+zonky@vvvlcek.info:Aom7a8Fdu@T6#tErXIp0GbDvYZgWJA5q");
	$credentials=base64_encode("mujrobot:mujrobot");
	
	//* //  file_get_contents - Nepotrebuje php-curl, ale nevypise obsah chybove stranky (napr pri kodech 400, 401)
	$url="https://api.zonky.cz/oauth/token";
	$postdata=http_build_query(["scope"=>"SCOPE_APP_BASIC_INFO SCOPE_INVESTMENT_READ SCOPE_INVESTMENT_WRITE",
		"grant_type"=>"authorization_code",
		"code"=>"$code",
		"redirect_uri"=>"https://app.zonky.cz/api/oauth/code"]);
	$opts=
		["http"=>
			[
	    	"method"=>"POST",
	    	"header"=>"Content-Type: application/x-www-form-urlencoded\r\n".
					"Authorization: Basic $credentials\r\n",
			"content"=>$postdata
			]
		];
	
	//print_r($opts);
	//die;
	$context=stream_context_create($opts);
	$returnd=file_get_contents($url,false,$context);
	$returnh=$http_response_header;
	$returnda=json_decode($returnd,true);
	//print_r($returnh);
	if(isset($zotokdbg)) {print_r($returnd);echo "\n";}
	if(isset($zotokdbg)) {print_r($returnda);echo "\n";}
	//*/
		
	/* //  CURL - Vypise i obsah chybove stranky (napr pri kodech 400, 401) 
	$ch = curl_init();
	
	curl_setopt($ch, CURLOPT_URL, "https://api.zonky.cz/oauth/token");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "scope=SCOPE_APP_BASIC_INFO SCOPE_INVESTMENT_READ SCOPE_INVESTMENT_WRITE&grant_type=authorization_code&code=$code&redirect_uri=https://app.zonky.cz/api/oauth/code");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	  "Content-Type: application/x-www-form-urlencoded",
	  "Authorization: Basic $credentials"
	));
	$returnd=curl_exec($ch);
	curl_close($ch);
	$returnda=json_decode($returnd,true);
	//print_r($returnh);
	if(isset($zotokdbg)) {print_r($returnd);echo "\n";}
	if(isset($zotokdbg)) {print_r($returnda);echo "\n";}
	//*/

	if(isset($returnda["access_token"]))
		{
		$t1=time();
		$t2=$t1+$returnda["expires_in"];
		$t1s=date("c",$t1);
		$t2s=date("c",$t2);
		$returnda["expires"]=$t2;
		$returnda["expires_string"]=$t2s;
		echo "Token valid from $t1s to $t2s.\n";
		file_put_contents("$file",json_encode($returnda));
		echo "Token and expiration time saved to '$file' file.\n";
		echo "Token:\n";
		echo $returnda["access_token"];
		echo "\n";
		return $returnda["access_token"];
		}
	else
		{
		echo "Error. Get token fail.\n";
		return false;
		}
	}


function ZoTokLoad($file)
	{
/*
File: Ulozene tokeny a doba platnosti v json formatu.
Return value: access_token nebo false 
*/

	global $zotokdbg;

	if(!file_exists($file)) 
		{
		echo "Error. File '$file' not found.\n";
		return false;
		}
	$t=time();
	$ts=date("c",$t);
	echo "Time: $ts.\n";
	$tokenjson=file_get_contents("$file");
	if(isset($zotokdbg)) {print_r($tokenjson);echo "\n";}
	$token=json_decode($tokenjson,true);
	if(isset($zotokdbg)) {print_r($token);echo "\n";}
	
	if(isset($token["access_token"]))
		{
		
		$t2=$token["expires"]; // Konec platnosti
		$t1=$t2-$token["expires_in"]; // Zacatek platnosti 
		$tr=($t1+(($t2-$t1)/2)); // V polovine platnosti obnovit token
		$t1s=date("c",$t1);
		$t2s=date("c",$t2);
		$trs=date("c",$tr);               
		echo "Token valid from $t1s to $t2s. Refresh after $trs.\n";
		if ($t>$tr)
		//if ($t>0) // debug
			{
			echo "Refreshing token.\n";
			return ZoTokRefresh($file,$token["refresh_token"]);
			}
		else
			{
			echo "Token:\n";
			echo $token["access_token"];
			echo "\n";
			return $token["access_token"];
			}   
		}
	else
		{
		echo "Error. Token decode fail.\n";
		return false;
		}
	}


function ZoTokRefresh($file,$refresh_token)
	{
/*
File: Soubor pro ulozeni access_token, refresh_token, doba platnosti a dalsi v json formatu.
Refresh_token: Pomoci nej Zonky vyda novy access_token a ostatni.
Return value: access_token nebo false  
*/

	global $zotokdbg;
	$credentials=base64_encode("mujrobot:mujrobot");

	//* //  file_get_contents
	$url="https://api.zonky.cz/oauth/token";
	$postdata=http_build_query(["scope"=>"SCOPE_APP_BASIC_INFO SCOPE_INVESTMENT_READ SCOPE_INVESTMENT_WRITE",
		"grant_type"=>"refresh_token",
		"refresh_token"=>"$refresh_token"]);
	$opts=
		["http"=>
			[
	    	"method"=>"POST",
	    	"header"=>"Content-Type: application/x-www-form-urlencoded\r\n".
					"Authorization: Basic $credentials\r\n",
			"content"=>$postdata
			]
		];
	
	//print_r($opts);
	//die;
	$context=stream_context_create($opts);
	$returnd=file_get_contents($url,false,$context);
	$returnh=$http_response_header;
	$returnda=json_decode($returnd,true);
	//print_r($returnh);
	if(isset($zotokdbg)) {print_r($returnd);echo "\n";}
	if(isset($zotokdbg)) {print_r($returnda);echo "\n";}
	//*/
		
	/* //  CURL...
	$ch = curl_init();
	
	curl_setopt($ch, CURLOPT_URL, "https://api.zonky.cz/oauth/token");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "refresh_token=$refresh_token&grant_type=refresh_token&scope=SCOPE_APP_BASIC_INFO SCOPE_INVESTMENT_READ SCOPE_INVESTMENT_WRITE");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	  "Content-Type: application/x-www-form-urlencoded",
	  "Authorization: Basic $credentials"
	));
	$returnd=curl_exec($ch);
	curl_close($ch);
	$returnda=json_decode($returnd,true);
	//print_r($returnh);
	if(isset($zotokdbg)) {print_r($returnd);echo "\n";}
	if(isset($zotokdbg)) {print_r($returnda);echo "\n";}
	//*/
	
	if(isset($returnda["access_token"]))
		{
		$t1=time();
		$t2=$t1+$returnda["expires_in"];
		$t1s=date("c",$t1);
		$t2s=date("c",$t2);
		$returnda["expires"]=$t2;
		$returnda["expires_string"]=$t2s;
		echo "New token valid from $t1s to $t2s.\n";
		file_put_contents("$file",json_encode($returnda));
		echo "New token and expiration time saved to '$file' file.\n";
		echo "Token:\n";
		echo $returnda["access_token"];
		echo "\n";
		return $returnda["access_token"];
		}
	else
		{
		echo "Error. Get new token fail.\n";
		return false;
		}
	}

?>
