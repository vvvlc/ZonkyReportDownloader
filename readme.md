# zonky-downloader

Tool downloads details from zonky

```sh
$ ./zonky-downloader.py 
usage: zonky-downloader.py [-h] -t TOKEN [-i INVESTMENTS] [-n NOTIFICATIONS]
                           [-o OVERVIEW]
zonky-downloader.py: error: the following arguments are required: -t/--token
```

There are some alternative to download reports from Zonky, without using password

## Check token
succesfull authentication
```sh
./zonky-downloader.py -t ********-****-****-****-************ -c
statistics are OK
Notifications are OK
https://api.zonky.cz/users/me/investments/export
Investments are OK
```

failed authentication 
```sh
./zonky-downloader.py -t ********-****-****-****-************ -c
statistics: Token is invalid
Notifications: Token is invalid
https://api.zonky.cz/users/me/investments/export
Investments: Token is invalid
```

## Download reports from Zonky integration with zotok
```sh
cd zotok
php zotokload.php token.json
cd ..
./zonky-downloader.py -t $(jq -r '.access_token' zotok/token.json) -i investments.xlsx -n notifications.json -o stats.json
```

## Download reports from Zonky integration with Robozonky
 - setup token file see `-t` in https://github.com/RoboZonky/robozonky/releases/tag/robozonky-5.5.0, setup token file `/home/robozonky/token.xml`
```sh
./zonky-downloader.py -t $(xmllint --xpath '//access_token/text()' /home/robozonky/token.xml) -i investments.xlsx -n notifications.json -o stats.json
```

## Download reports from Zonky integration with Robozonky withou token file
 - activate http debug messages in `robozonky.log`
 - wait 60 sec 
 - extract token form log file
 - disable http debug messages

```sh
cd /home/robozonky/reports
sed '/apache.http/ s/\(debug\|error\)/debug/' -i ../conf/log4j2.xml
sleep 60 
./zonky-downloader.py -t $(grep org.apache.http ../logs/robozonky.log | grep Authorization  | grep headers | tail -1 | awk '{print $10}') -n ${PREFIX}-notifications.json -i ${PREFIX}-investments.xlsx -o ${PREFIX}-stats.json
sed '/apache.http/ s/\(debug\|error\)/error/' -i ../conf/log4j2.xml
```