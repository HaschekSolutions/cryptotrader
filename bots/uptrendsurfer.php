<?php 

/**
* Bot name: Uptrend Surfer
* Short Description: 
*       The bot buys some crypto and holds until it gains value, then sells and re-buys.
*       Bot will only make money if there is an uptrend.
* Author: Christian Haschek
*
* What it does in detail:
    - You have to have USD or EUR available in your funds
    - The bot will buy coins on start (you specify the amount of money in USD/EUR)
    - If the worth of these coins rises by some level, it will sell. Leaving you with a profit
    - After selling it will re-buy and wait for the next raise in worth
*
* Parameters:
* -p <product string>                       The product string in the format "<CRYPTO>-<PAYMENT>". eg: BTC-EUR ETH-USD ETH-EUR, etc..
* -bw <buy worth in USD/EUR>                This amount will be bought in the crypto you specified. eg "-p BTC-USD -w 100" will buy you 100$ worth of Bitcoin
* -g <gain in percent needed for selling>   This is the percentage increase needed for the bot to sell its coins
* -sim                                      Simulate only
*
*/

include_once(dirname(__FILE__).'/../gdax.php');
$g = new gdax(GDAX_KEY,GDAX_SECRET,GDAX_PASSPHRASE);

// check arguments and stuff
$args = getArgs(array('p','bw','g','sim'));
if(!$args['p'])
    $args['p'] = 'BTC-USD';
if(!$args['bw'])
    $args['bw'] = 100;
if(!$args['g'])
    $args['g'] = 10;

$a = explode('-',$args['p']);
$crypto=$a[0];
$currency=$a[1];

$sellworth = round(($args['bw']*($args['g']/100))+$args['bw'],2);

// print details to user

if($args['sim'])
    echo " =============\n SIMULATION MODE \n ============\n";

echo " [i] Trading {$args['p']}\n";
echo " [i] Will buy {$args['bw']} $currency in $crypto\n";
echo " [i] Will sell when $crypto will gain {$args['g']}%, meaning when it's worth $sellworth $currency\n";

$g->updatePrices($args['p']);
$coins = (1/$g->lastaskprice)*$args['bw'];
echo " [i] {$args['bw']} $currency currently is $coins $crypto\n";

echo "  [!] Buying $coins $crypto!\n";
if(!$args['sim'])
    $data = $g->marketBuyCrypto($coins,$args['p']);

while(1)
{
    $g->updatePrices($args['p']);
    $sellprice = $g->lastbidprice*$coins;
    echo "New worth: $sellprice\n";
    if($sellprice >= $sellworth)
    {
        $profit = $sellprice - $args['bw'];
        echo " [!!] Coins gained {$args['g']}%, will sell now for $sellprice. Made $profit $currency profit!\n";
        if(!$args['sim'])
            $data = $g->marketSellCrypto($coins,$args['p']);
        
        $coins = (1/$g->lastaskprice)*$args['bw'];
        
        echo "  [!] Re-Buying $coins $crypto!\n";
        if(!$args['sim'])
            $data = $g->marketBuyCrypto($coins,$args['p']);
    }

    sleep(10);
}