# CryptoTrader by [Haschek Solutions](https://haschek.solutions)

# What does it do?
The heart of this repo is the ```gdax.php``` file which is a simple implementation of the [GDAX](https://www.gdax.com) API written in PHP.

The aim for this project is to create various tools and bots to make trading easier. It doesn't rely on any external APIs or classes rather than php-curl.

# Install
- Clone this repo
- Rename ```example.config.inc.php``` to ```config.inc.php```
- Create an API key on https://www.gdax.com/settings/api and fill in the values in your ```config.inc.php``` file

# Usage
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