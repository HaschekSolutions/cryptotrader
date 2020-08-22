<?php 

/**
* Bot name: Wave Rider
* Short Description: 
*       This bot is based on the uptrend surfer with the only difference that it
*       waits to re-buy for a small dip in the price
* Author: Christian Haschek
*
* What it does in detail:
    - You have to have USD or EUR available in your funds
    - The bot will buy coins on start (you specify the amount of money in USD/EUR)
    - If the worth of these coins rises by some level, it will sell. Leaving you with a profit
    - After selling it will wait for the price to sink a bit before finally re-buying and wait for the next spike
*
* Parameters:
* -p <product string>                       The product string in the format "<CRYPTO>-<PAYMENT>". eg: BTC-EUR ETH-USD ETH-EUR, etc..
* -bw <buy worth in USD/EUR>                This amount will be bought in the crypto you specified. eg "-p BTC-USD -w 100" will buy you 100$ worth of Bitcoin
* -g <gain in percent needed for selling>   This is the percentage increase needed for the bot to sell its coins
* -pv <plummet value in percent for re-buy> This is the percentage the bot will wait for the crypto price to drop before re-buying
* -nib                                      No initial buy. Means that the script won't buy the amount you specified when it's run. You can use this to manage coins you already have
* -fip <crypto price in USD/EUR>            Force initial Price. Only in combination with -nib! Uses a crypto price you specify for the first buy. Can be used to restore older sessions
* -sim                                      Simulate only
*
*/

include_once(dirname(__FILE__).'/../coinbase-pro.php');
$g = new coinbaseExchange(CB_KEY,CB_SECRET,CB_PASSPHRASE);

// check arguments and stuff
$args = getArgs(array('p','bw','g','sim','nib','pv','fip'));
if(!$args['p'] || !$args['bw'] || !$args['g'] || !$args['pv'] || ($args['fip'] && !$args['nib']))
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
echo " [i] After selling I will wait for $crypto to drop by {$args['pv']}% before re-buying\n";

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
            exit(" [x] Error: Not enough funds in your $currency wallet. You have: {$balances['available']} $currency but you need {$args['bw']}\n");
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
        echo "\n [!!] Coins gained {$args['g']}%, will sell now for $sellprice. Made $profit $currency profit!\n";
        if(!$args['sim'])
            $data = $g->marketSellCrypto($coins,$args['p']);
        
        $coins = round((1/$g->lastaskprice)*$args['bw'],7);

        $waitingforprice = round($g->lastbidprice-($g->lastbidprice*($args['pv']/100)),2);

        echo "  [!] Entering re-buy loop. Waiting for the crypto price to drop by {$args['pv']}% to $waitingforprice $currency per $crypto!\n";

        $starttime = time();
        while($g->lastbidprice > $waitingforprice)
        {
            $g->updatePrices($args['p']);
            echo " Waiting since ".translateSecondsToNiceString(time()-$starttime)."\t Current price: {$g->lastbidprice}\t Waiting for: $waitingforprice $currency per $crypto\t\t\r";
            sleep(120);
        }

        echo "\n  [!] Price reached! Buying now.\n";

        if(!$args['sim'])
        {
            //check if the user has enough cash to buy
            $g->loadAccounts();
            $balances = $g->getAccountInfo($currency);
            if(!$balances || $balances['available']<$args['bw'])
            {
                echo " [x] Error: Not enough funds in your $currency wallet. Will wait until there is enough.\n";
                while(1)
                {
                    $g->loadAccounts();
                    $balances = $g->getAccountInfo($currency);
                    if($balances['available']>=$args['bw']){
                        echo " [!] Finally got enough money. Will buy now\n";
                        break;
                    }
                    echo ".";
                    sleep(60);
                }
            }
            $data = $g->marketBuyCurrency($args['bw'],$args['p']);
            $coins = round((1/$g->lastaskprice)*$args['bw'],7);
        }
            
    }

    sleep(60);
}

function translateSecondsToNiceString($secs,$withseconds=false)
        {
            $units = array(
                    "Year"   => 365*24*3600,
                    "Month"   => 30*24*3600,
                    "Week"   => 7*24*3600,
                    "Day"    =>   24*3600,
                    "Hour"   =>      3600,
                    "Minute" =>        60,
                    "Second" =>        1,
            );
            
            if(!$withseconds)
                unset($units["Second"]);

            if ( $secs == 0 ) return "0 Seconds";

            $s = "";

            foreach ( $units as $name => $divisor ) {
                    if ( $quot = intval($secs / $divisor) ) {
                            $s .= "$quot $name";
                            $s .= (abs($quot) > 1 ? "s" : "") . ", ";
                            $secs -= $quot * $divisor;
                    }
            }

            return substr($s, 0, -2);
        }

function renderUsage()
{
    $command = 'php '.__FILE__;

    echo "Usage: $command [PARAMETERS]\n-------------\n";
    echo "Parameters:\n";
    echo "-p product-string:    The product string in the format <CRYPTO>-<PAYMENT>. eg: BTC-EUR ETH-USD ETH-EUR, etc..\n";
    echo "-bw <buy worth in USD/EUR>:   This amount will be bought in the crypto you specified. eg '-p BTC-USD -w 100' will buy you 100$ worth of Bitcoin\n";
    echo "-g <gain in percent needed for selling>:  This is the percentage increase needed for the bot to sell its coins\n";
    echo "-pv <plummet value in percent for re-buy>:    This is the percentage the bot will wait for the crypto price to drop before re-buying\n";
    echo "Optional:\n";
    echo "-nib: No initial buy. Means that the script won't buy the amount you specified when it's run. You can use this to manage coins you already have\n";
    echo "-fip <crypto price in USD/EUR>:   Force initial Price. Only in combination with -nib! Uses a crypto price you specify for the first buy. Can be used to restore older sessions\n";
    echo "-sim:     Simulate only\n";
}