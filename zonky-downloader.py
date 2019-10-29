#!/usr/bin/env python3
import requests
import sys
import argparse
import time

zonky_url="https://api.zonky.cz"
token=None
test=False

def download_file(url, local_filename ,headers = None):
    # NOTE the stream=True parameter
    print(url)
    r = requests.get(url, stream=True, headers=headers)
    with open(local_filename, 'wb') as f:
        for chunk in r.iter_content(chunk_size=1024):
            if chunk: # filter out keep-alive new chunks
                f.write(chunk)
                #f.flush() commented by recommendation from J.F.Sebastian
    return local_filename


def download(endpoint,filename=None, sms=None):    
    url=f"{zonky_url}/{endpoint}"
    print(url)
    
    resp=requests.post(url,headers={'Authorization': f"Bearer {token}"})
    while True:        
        if resp.status_code==204 and not test:
            break
        if resp.status_code==401:
            print("Cannot download investments because of missing SCOPE_INVESTMENT_READ")
            return
        if resp.status_code==400:
            print("Investments: Token is invalid")
            return
        if not resp.status_code in [202,204]:
            raise ValueError(resp)
        if test:
            print("Investments are OK")
            return
        resp=requests.get(url,headers={'Authorization': f"Bearer {token}"})
        time.sleep(60)

    url=f"{zonky_url}/{endpoint}/data"
    print(url)
    resp=requests.get(url,headers={'Authorization': f"Bearer {token}"})
    if resp.status_code==302:
        l=resp.headers['Location']
        assert l
        download_file(url=resp.headers['Location'],local_filename = (l.split('?')[0].split('/')[-1] if filename is None else filename))
    elif resp.status_code==200:
        download_file(f"{zonky_url}/{endpoint}/data",headers={'Authorization': f"Bearer {token}"},local_filename = (l.split('?')[0].split('/')[-1] if filename is None else filename))

def download_notifications(filename='notification.json'):
    url=f"{zonky_url}/users/me/notifications"
    resp=requests.get(url,headers={'Authorization': f"Bearer {token}", 'X-Size': '1'})
    if resp.status_code!=200:
        if resp.status_code==401:
            print("Cannot download notifications because of missing SCOPE_NOTIFICATIONS_READ")
            return
        if resp.status_code==400:
            print("Notifications: Token is invalid")
            return
        else:
            raise ValueError(resp)

    if test:
        print("Notifications are OK")            
        return

    download_file(url,headers={'Authorization': f"Bearer {token}", 'X-Size': resp.headers['X-Total']},local_filename = filename)

def download_stats(filename='statistics.json'):   
    url=f"{zonky_url}/statistics/me/public-overview"
    resp=requests.get(url,headers={'Authorization': f"Bearer {token}"})
    if resp.status_code!=200:
        if resp.status_code==401 or resp.status_code==403:
            print("Cannot download statistics because of missing SCOPE_NOTIFICATIONS_READ")
            return
        if resp.status_code==400:
            print("statistics: Token is invalid")
            return
        else:
            raise ValueError(resp)

    if test:
        print("statistics are OK")            
        return

    download_file(url,headers={'Authorization': f"Bearer {token}"},local_filename = filename)

def check_token():
    global test
    test=True
    download_stats(None)
    download_notifications(None)
    download('users/me/investments/export', None)

def str2bool(v):
    if isinstance(v, bool):
       return v
    if v.lower() in ('yes', 'true', 't', 'y', '1'):
        return True
    elif v.lower() in ('no', 'false', 'f', 'n', '0'):
        return False
    else:
        raise argparse.ArgumentTypeError('Boolean value expected.')

if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument('-t', '--token', required=True,  help="authentication token")
    parser.add_argument('-i', '--investments', help="namefile for investments  (investments.xlsx)")
    parser.add_argument('-n', '--notifications', help="namefile for notifications  (notifications.json)")
    parser.add_argument('-o', '--overview', help="file for statistics overview (statistics.json)")
    parser.add_argument('-c', "--check", type=str2bool, nargs='?',
                        const=True, default=False,
                        help="Validate token")
    #parser.add_argument('-w', '--wallet', help="file for wallet (transactions.xlsx)")
    #parser.add_argument('-s', '--sms', default=None, help="sms code for transactions")

    args = parser.parse_args()
    
    token=args.token
    
    if args.check:
        check_token()        
    if args.investments:
        print(f"downloading investments to {args.investments}")
        download('users/me/investments/export', args.investments)
        print("done")
    if args.notifications:
        print(f"downloading notifications to {args.notifications}")
        download_notifications(args.notifications)
        print("done")
    if args.overview:
        print(f"downloading statistics overview to {args.overview}")
        download_stats(args.overview)
        print("done")
    # if args.wallet:
    #     print(f"downloading wallet to {args.wallet}")
    #     download('users/me/wallet/transactions/export',args.wallet, args.sms)
    #     print("done")

