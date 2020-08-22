# CryptoTrader by [Haschek Solutions](https://haschek.solutions)

# What does it do?
The heart of this repo is the ```coinbase-pro.php``` file which is a simple implementation of the [Coinbase Pro](https://pro.coinbase.com/) API written in PHP.

The aim for this project is to create various tools and bots to make trading easier. It doesn't rely on any external APIs or classes rather than php-curl.

ONLY RUN THIS IF YOU KNOW WHAT YOU ARE DOING. IF YOU LOSE MONEY BECAUSE OF A PROGRAMMING ERROR, IT'S YOUR OWN FAULT. You have been warned.

# Install
- Clone this repo or [download the zip file](https://github.com/HaschekSolutions/cryptotrader/archive/master.zip)
- Rename ```example.config.inc.php``` to ```config.inc.php```
- Create an API key on https://pro.coinbase.com/profile/api and fill in the values in your ```config.inc.php``` file
- Install PHP
  - Windows: [Download from windows.php.net](http://windows.php.net/downloads/releases/php-7.2.2-Win32-VC15-x64.zip)
  - Linux: ```apt-get install php5``` or better yet ```apt-get install php7.1``` if available
  - MacOS: [Install homebrew](https://brew.sh/) and then ```brew update``` followed by ```brew upgrade php```. Apple is not including PHP in future versions of MacOS. If it is missing ```brew install php```. 
- Run a bot or the example scripts with php

![API key generation](https://www.pictshare.net/as17pqcsf8.jpg)

# Bots

## Bot 1: Uptrend Surfer
The first bot is the most simple one.

This bot will buy coins for USD/EUR, track the worth of these coins and if it made a profit, sells the profit and waits for more gain to sell.
So this bot will only make any money if the worth of the coin is rising steadily. No market analysis or else, just go with the flow.

```
# Example usage: Buy 100 USD worth of BTC and sell the profits when it gained 10% in value
php bots/uptrendsurfer.php -p BTC-USD -bw 100 -g 10
```

| Parameter     | What it does |
| ------------- |:-------------|
| -p product-string   |                      The product string in the format "CRYPTO-PAYMENT". eg: BTC-EUR ETH-USD ETH-EUR, etc..|
|-bw "buy worth in USD/EUR"      |          This amount will be bought in the crypto you specified. eg "-p BTC-USD -w 100" will buy you 100$ worth of Bitcoin|
|-g "gain in percent needed for selling" |  This is the percentage increase needed for the bot to sell its profits|
|-nib                 |  No initial buy. Means that the script won't buy the amount you specified when it's run. You can use this to manage coins you already have |
|-fip "crypto price in USD/EUR" |            Only in combination with -nib! Uses a crypto price you specify. Can be used to restore older sessions |
|-sim |                                     Simulate only (no sells or buys are done, but the script thinks they were)|

## Bot 2: Wave Rider
This is an advanced version of the uptrend surfer

This bot is the same as the Uptrend Surfer with the only difference that after selling the gains, the bot will wait for the crypto price to drop by a percentage you specified before re-buying.
This makes the bot a little bit more profitable in normal cases but it will miss steady uptrends. That's why this bot should be used in combination of the uptrend surfer so you have the best of both worlds.

```
# Example usage: Buy 100 USD worth of BTC, sell when it gained 10% in value and re-buy when the BTC price drops by 5%
php bots/waverider.php -p BTC-USD -bw 100 -g 10 -pv 5
```

| Parameter     | What it does |
| ------------- |:-------------|
| -p product-string   |                      The product string in the format "CRYPTO-PAYMENT". eg: BTC-EUR ETH-USD ETH-EUR, etc..|
|-bw "buy worth in USD/EUR"      |          This amount will be bought in the crypto you specified. eg "-p BTC-USD -w 100" will buy you 100$ worth of Bitcoin|
|-g "gain in percent needed for selling" |  This is the percentage increase needed for the bot to sell its coins|
|-pv "plummet value in percent for re-buy" |  This is the percentage the bot will wait for the crypto price to drop before re-buying|
|-nib                 |  No initial buy. Means that the script won't buy the amount you specified when it's run. You can use this to manage coins you already have |
|-fip "crypto price in USD/EUR" |            Only in combination with -nib! Uses a crypto price you specify. Can be used to restore older sessions |
|-sim |                                     Simulate only (no sells or buys are done, but the script thinks they were)|


# Example scripts for devs
There are multiple example scripts which do specific things.

## account_info.php
This script when executed displays the account details.

Example output:
```
[i] Account overview
-----------------
 [i] Currency: ETH
   [ETH] Total balance:                 5.0055172700000000 ETH
   [ETH] Currently in open orders:      3.8327153400000000 ETH
   [ETH] Available:                     1.1728019300000000 ETH

 [i] Currency: BTC
   [BTC] Total balance:                 0.1242994896243332 BTC
   [BTC] Currently in open orders:      0.1242994800000000 BTC
   [BTC] Available:                     0.0000000096243332 BTC

 [i] Currency: USD
   [BTC] Total balance:                 15.000000000000000 BTC
   [BTC] Currently in open orders:      0.000000000000000 BTC
   [BTC] Available:                     15.000000000000000 BTC
```

## price_check.php
This script prints out the market price from the last successful purchase

Example output:
```
[i] Price info for BTC-EUR
-----------
 [i] Ask price:         2090.98 EUR
 [i] Bid price:         2086.86 EUR
 [i] Spread:            4.1199999999999 EUR
```
