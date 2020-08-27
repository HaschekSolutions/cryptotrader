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
    - If the worth of these coins rises by some level, it will sell the profit
*
* Parameters:
* -p <product string>                       The product string in the format "<CRYPTO>-<PAYMENT>". eg: BTC-EUR ETH-USD ETH-EUR, etc..
* -bw <buy worth in USD/EUR>                This amount will be bought in the crypto you specified. eg "-p BTC-USD -w 100" will buy you 100$ worth of Bitcoin
* -g <gain in percent needed for selling>   This is the percentage increase needed for the bot to sell its coins
* -nib                                      No initial buy. Means that the script won't buy the amount you specified when it's run. You can use this to manage coins you already have
* -fip <crypto price in USD/EUR>            Force initial Price. Only in combination with -nib! Uses a crypto price you specify for the first buy. Can be used to restore older sessions
* -sim                                      Simulate only
*
*/

include_once(dirname(__FILE__).'/../coinbase-pro.php');
$g = new coinbaseExchange(CB_KEY,CB_SECRET,CB_PASSPHRASE);

// check arguments and stuff
$args = getArgs(array('p','bw','g','sim','nib','fip'));
if(!$args['p'] || !$args['bw'] || !$args['g'] || ($args['fip'] && !$args['nib']))
    exit(renderUsage());

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

echo "\n ====== BOT STARTING ====== \n\n";

$g->updatePrices($args['p']);
$buyprice = $args['fip']?$args['fip']:$g->lastaskprice;
$coins = round((1/$buyprice)*$args['bw'],7);
echo " [i] {$args['bw']} $currency currently is $coins $crypto\n";

if(!$args['nib'])
{    
    echo "  [!] Buying $coins $crypto!\n";
    if(!$args['sim'])
    {
        //check if the user has enough cash to buy
        $g->loadAccounts();
        $balances = $g->getAccountInfo($currency);
        if(!$balances || $balances['available']<$args['bw'])
            exit(" [x] Error: Not enough funds in your $currency wallet\n");
        $data = $g->marketBuyCrypto($coins,$args['p']);
    }
}

while(1)
{
    $g->updatePrices($args['p']);
    $sellprice = $g->lastbidprice*$coins;
    $profit = round($sellprice - $args['bw'],2);
    echo " Current worth: $sellprice\t Change: ".($profit > 0?'+':'')."$profit $currency\t\t\t\r";
    if($sellprice >= $sellworth)
    {
        echo "\n [!!] Coins gained {$args['g']}%, will sell the profits of $profit $currency now!\n";
        $coinsforsale = round((1/$g->lastaskprice)*$profit,7);
        if(!$args['sim'])
            $data = $g->marketSellCrypto($coinsforsale,$args['p']);
        $coins-= $coinsforsale;
        echo "  [$crypto] Now still holding $coins $crypto (sold $coinsforsale)\n";
        echo "  [i] Will sell profits again when it's worth $sellworth $currency\n";
    }

    sleep(60);
}

function renderUsage()
{
    $command = 'php '.__FILE__;

    echo "Usage: $command [PARAMETERS]\n-------------\n";
    echo "Parameters:\n";
    echo "-p product-string:    The product string in the format <CRYPTO>-<PAYMENT>. eg: BTC-EUR ETH-USD ETH-EUR, etc..\n";
    echo "-bw <buy worth in USD/EUR>:   This amount will be bought in the crypto you specified. eg '-p BTC-USD -w 100' will buy you 100$ worth of Bitcoin\n";
    echo "-g <gain in percent needed for selling>:  This is the percentage increase needed for the bot to sell its coins\n";
    echo "Optional:\n";
    echo "-nib: No initial buy. Means that the script won't buy the amount you specified when it's run. You can use this to manage coins you already have\n";
    echo "-fip <crypto price in USD/EUR>:   Force initial Price. Only in combination with -nib! Uses a crypto price you specify for the first buy. Can be used to restore older sessions\n";
    echo "-sim:     Simulate only\n";
}
