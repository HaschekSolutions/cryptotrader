<?php 

include_once(dirname(__FILE__).'/../gdax.php');

$g = new gdax(GDAX_KEY,GDAX_SECRET,GDAX_PASSPHRASE);
$g->loadAccounts();
$g->printAccountInfo();