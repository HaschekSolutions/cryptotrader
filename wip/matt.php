<?php 

/**
* Bot name: Matt (after the guy who wanted it implemented)
* Short Description: 
*       The Bot takes a percentage of your funds and invests automatically
* Author: Christian Haschek
*
* What it does in detail:
    - You have to have USD or EUR available in your funds
    - The bot takes a percentage (default 10) of the funds
    - The buy value is a certain percentage of the average value of the crypto currency over a defined period of time
    - The sell value is a percentage of the previously calculated buy value
*
* Parameters:
* -p <product string>                       The product string in the format "<CRYPTO>-<PAYMENT>". eg: BTC-EUR ETH-USD ETH-EUR, etc..
* -sp <percent value between 1 and 100>     Stake percentage. The bot will use this percentage of your EUR/USD money to buy in
* -sim                                      Simulate only
*
* Matts exact description:
* For example, when I run the bot I tell it to buy in chunks that equate to 10% of my total wallet amount,
*  use the average price for the last 12 hours, set the buy value to 20% below that average, and the sell
*  amount 10% above that average. 
*  (10% of wallet, use the average price over the last 12 hours in minutes, buy 20% below the average price,
*  sell 10% above the average price).
*/

include_once(dirname(__FILE__).'/../coinbase-pro.php');
$g = new coinbaseExchange(CB_KEY,CB_SECRET,CB_PASSPHRASE);

// check arguments and stuff
$args = getArgs(array('p','sp'));
if(!$args['p'])
    $args['p'] = 'BTC-USD';
if(!$args['sp'])
    $args['sp'] = 10;

// print details to user

echo " [i] Trading {$args['p']}\n";
echo " [i] Using {$args['sp']}% of the wallets money\n";

$product = productStringToArr($args['p']);
$crypto = $product[0];
$currency = $product[1];
$holding = 0;

//look up funds
$g->loadAccounts();
$balances = $g->getAccountInfo($currency);
//$g->printAccountInfo($currency);

//$balances['available']=100; //@TODO: TAKE THIS OUT

if(!$balances || $balances['available']<1)
    exit(" [x] Error: Not enough funds in your $currency wallet\n");

$amount = round(($balances['available']/100)*$args['sp'],2);

echo "[$currency] Will use $amount $currency to buy $crypto\n";

/*
//buy $args['p'] in $crypto
$buydata = $g->marketBuyCurrency($amount,$args['p']);
//exit();
$buy_id = $buydata['id'];
$orderinfo = $g->getOrderInfo($buy_id);

$holding = $orderinfo['filled_size'];
$buyprice = $orderinfo['executed_value']/$orderinfo['filled_size'];

var_dump($buyprice);
*/

//main loop
while(true)
{
    $pricedata = $g->updatePrices($args['p']);
    $bid = $pricedata['bid'];
    $average = $pricedata['24h_average'];

    $bid_percent = round(($bid/$average)*100,2);
    var_dump("Bid: $bid\nAverage: $average\nPercent: $bid_percent %\n\n------\n");
    //if($bid)
    
    $change = $g->lastbidprice-$buyprice;
    echo " [i] Current sell price: {$g->lastbidprice}. Change: $change $currency\n";
    sleep(10);
}
