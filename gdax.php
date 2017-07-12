<?php 
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));

if(!file_exists(ROOT.DS.'config.inc.php')) die('Rename example.config.inc.php to config.inc.php before running');
include_once(ROOT.DS.'config.inc.php');

/* Written in accordance to https://docs.gdax.com/ 
 Author: Christian Haschek <christian@haschek.at>
 Github repo: https://github.com/HaschekSolutions/cryptotrader
 June 2017
*/
class gdax 
{
    private $apiurl = "https://api.gdax.com";
    private $key;
    private $secret;
    private $passphrase;
    public $accounts;
    
    private $bidsprices;
    public $lastbidprice=0;
    private $lowestbids=99999;
    
    private $askprices;
    public $lastaskprice=0;
    private $highestask=0;

    public function __construct($key, $secret, $passphrase, $sandbox=false) {
        $this->key = $key;
        $this->secret = $secret;
        $this->passphrase = $passphrase;

        if($sandbox===true)
            $this->apiurl = "https://api-public.sandbox.gdax.com";
    }

    function updatePrices($product='BTC-USD')
    {
        $data = $this->makeRequest('/products/'.$product.'/ticker');
        if($data===false){ echo " [X] Error getting products\n";return false;}
        $crypto=substr($product,0,3);
        $currency=substr($product,4);
        $ask = $data['ask'];
        $bid = $data['bid'];
        $this->askpricese[$product][] = $ask;
        $this->bidprices[$product][] = $bid;

        $out['ask'] = $ask;
        $out['bid'] = $bid;

        if($this->lowestask>$ask)
            $this->lowestask = $ask;
        if($this->highestbid<$bid)
            $this->highestbid = $bid;

        $this->lastaskprice = $ask;
        $this->lastbidprice = $bid;

        $data = $this->makeRequest('/products/'.$product.'/stats');

        $out['24h_low'] = $data['low'];
        $out['24h_high'] = $data['high'];
        $out['24h_open'] = $data['open'];

        return $out;
    }

    function printPrices($product='BTC-USD')
    {
        $crypto=substr($product,0,3);
        $currency=substr($product,4);

        echo "[i] Price info for $product\n-----------\n";
        echo " [i] Ask price: \t$this->lastaskprice $currency\n";
        echo " [i] Bid price: \t$this->lastbidprice $currency\n";
        echo " [i] Spread: \t\t".($this->lastaskprice-$this->lastbidprice)." $currency\n";
    }

    function printAccountInfo()
    {
        echo "[i] Account overview\n-----------------\n";
        foreach($this->accounts as $currency=>$data)
        {
            if(floatval($data['balance'])<0.1) continue;
            echo " [i] Currency: $currency\n";
            echo "   [$currency] Total balance: \t\t".$data['balance'].' '.$currency."\n";
            echo "   [$currency] Currently in open orders: \t".$data['hold'].' '.$currency."\n";
            echo "   [$currency] Available: \t\t\t".$data['available'].' '.$currency."\n";
            echo "\n";
        }
    }

    function getAccountInfo($product)
    {
        if(!$this->accounts[$product]) return false;
        return $this->accounts[$product];
    }

    // https://docs.gdax.com/#orders
    function makeOrder($type,$amount,$product='BTC-USD')
    {
        $result = $this->makeRequest('/orders',array(   'size'=>$amount,
                                                        'price'=>1890,
                                                        'side'=>$type,
                                                        'product_id'=>$product
                                                    ));

        return $result;
    }

    function marketSellCrypto($amount,$product='BTC-USD')
    {
        $result = $this->makeRequest('/orders',array(   'size'=>$amount,
                                                        'side'=>'sell',
                                                        'type'=>'market',
                                                        'product_id'=>$product
                                                    ));

        return $result;
    }

    function marketSellCurrency($amount,$product='BTC-USD')
    {
        $result = $this->makeRequest('/orders',array(   'funds'=>$amount,
                                                        'side'=>'sell',
                                                        'type'=>'market',
                                                        'product_id'=>$product
                                                    ));

        return $result;
    }

    function marketBuyCrypto($amount,$product='BTC-USD')
    {
        $result = $this->makeRequest('/orders',array(   'size'=>$amount,
                                                        'side'=>'buy',
                                                        'type'=>'market',
                                                        'product_id'=>$product
                                                    ));

        return $result;
    }

    function marketBuyCurrency($amount,$product='BTC-USD')
    {
        $result = $this->makeRequest('/orders',array(   'funds'=>$amount,
                                                        'side'=>'buy',
                                                        'type'=>'market',
                                                        'product_id'=>$product
                                                    ));

        return $result;
    }

    function isOrderDone($orderID)
    {
        $data = $this->makeRequest('/orders/'.$orderID);
        if($data)
        {
            if($data['status']=='done') return true;
            else return false;
        }
        else return true;

    }

    function loadAccounts()
    {
        $data = $this->makeRequest('/accounts');
        if($data===false) exit('Error getting accounts');
        foreach($data as $d)
        {
            $this->accounts[$d['currency']] = $d;
        }
    }

    function getHolds($id)
    {
        return $this->makeRequest('/accounts/'.$id.'/holds');
    }

    function makeRequest($path,$postdata='')
    {
        $curl = curl_init();
        if($postdata!='')
        {
            $elements = array();
            foreach($postdata as $key=>$pd)
            {
                $elements[] = $key.'='.$pd;
            }
            $compiledpostdata = implode('&',$elements);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($postdata));
        }
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->apiurl.$path,
            CURLOPT_USERAGENT => 'PHPtrader',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array('CB-ACCESS-KEY: '.$this->key,
                                        'CB-ACCESS-SIGN: '.$this->signature($path,($postdata==''?'':json_encode($postdata)),time(),(($postdata==''?'GET':'POST'))),
                                        'CB-ACCESS-TIMESTAMP: '.time(),
                                        'CB-ACCESS-PASSPHRASE: '.$this->passphrase,
                                        'Content-Type: application/json'
                                        )
        ));
        $resp = curl_exec($curl);
        if(curl_errno($curl)) return false;

        curl_close($curl);

        if(startsWith($resp,'Cannot')) return false;;


        $json = json_decode($resp,true);
        if($json['message'])
        {
            echo " [X] Error while making a call. Message: ".$json['message']."\n";
            return false;
        }
        else return $json;
    }

    /*taken from https://docs.gdax.com/#signing-a-message*/
    public function signature($request_path='', $body='', $timestamp=false, $method='GET') {
        $body = is_array($body) ? json_encode($body) : $body;
        $timestamp = $timestamp ? $timestamp : time();
        return base64_encode(hash_hmac("sha256", $timestamp.$method.$request_path.$body, base64_decode($this->secret), true));
    }
}

function getArgs($lookingfor)
{
    global $argv;
    foreach($argv as $key=>$argument)
    {
        if(!substr($argument,0,1)=='-') continue;
        $arg = trim(substr($argument,1));
        if(in_array($arg,$lookingfor))
            $args[$arg] = (substr($argv[($key+1)],0,1)=='-'?true:$argv[($key+1)]);
    }

    return $args;
}

function productStringToArr($string)
{
    return explode('-',$string);
}

function startsWith($a, $b) { 
    return strpos($a, $b) === 0;
}