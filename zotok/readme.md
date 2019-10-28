# Zotok - obtains Zonky token for further communication with Zonky

Created by Zdenek

## Prereqisities 
 - PHP
## Usage
Obtains a token for a new robot.

### Run firsttime to obtain token
```sh
$ php zotokget.php

Usage: 'php zotokget.php code [token_file_name]'.
Get code: 'https://app.zonky.cz/api/oauth/authorize?client_id=mujrobot&redirect_uri=https://app.zonky.cz/api/oauth/code&response_type=code&scope=SCOPE_APP_BASIC_INFO+SCOPE_INVESTMENT_READ+SCOPE_RESERVATIONS_READ+SCOPE_RESERVATIONS_SETTINGS_READ+SCOPE_NOTIFICATIONS_READ&state=z'
```
 - Open https://app.zonky.cz/api/oauth/authorize?client_id=mujrobot&redirect_uri=https://app.zonky.cz/api/oauth/code&response_type=code&scope=SCOPE_INVESTMENT_READ+SCOPE_NOTIFICATIONS_READ&state=z in a browser and obtain token
   
   **NOTE**: SCOPE_INVESTMENT_READ, SCOPE_NOTIFICATIONS_READ is sufficient
 - Then run `zotokget.php` with token from Zonky
```sh
$ php zotokget.php i****u token.json

Token valid from 2019-10-28T11:58:55+01:00 to 2019-10-29T11:58:54+01:00.
Token and expiration time saved to 'token.json' file.
Token:
********-****-****-****-************
```

Content of `token.json`
```json
{
    "access_token": "********-****-****-****-************",
    "token_type": "bearer",
    "refresh_token": "********-****-****-****-************",
    "expires_in": 86399,
    "scope": "SCOPE_INVESTMENT_READ SCOPE_INVESTMENT_WRITE SCOPE_APP_BASIC_INFO",
    "expires": 1572346734,
    "expires_string": "2019-10-29T11:58:54+01:00"
}
```

### Run always before using token
Refresh token first 
```sh
$ php zotokload.php token.json

Time: 2019-10-28T11:59:27+01:00.
Token valid from 2019-10-28T11:58:55+01:00 to 2019-10-29T11:58:54+01:00. Refresh after 2019-10-28T23:58:54+01:00.
Token:
********-****-****-****-************
```

Query for fresh token using `jq`
```sh
$ jq -r '.access_token' token.json

********-****-****-****-************
```



