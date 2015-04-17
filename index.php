<?php
$time_start = microtime(true);
include('app/config.php');
require 'app/flight/Flight.php';
require 'app/jsonRPCClient.php';
require 'app/controller.php';

date_default_timezone_set(TIMEZONE);
session_start();

Flight::register('reddcoin', 'jsonRPCClient', array("{$wallet['protocol']}://{$wallet['user']}:{$wallet['pass']}@{$wallet['host']}:{$wallet['port']}"));
Flight::register('controller', 'Controller');

if (USE_AUTHENTICATION == 1 && !isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) || $_SERVER['PHP_AUTH_USER'] != USERNAME || $_SERVER['PHP_AUTH_PW'] != PASSWORD) {
    header('WWW-Authenticate: Basic realm="StakeUI"');   
    header('HTTP/1.0 401 Unauthorized');
    exit;
}

switch (CURRENCY) {
    case "BTC":
        Flight::set('currency', 'average');
        Flight::set('currencySym', 'BTC');
        break;
    case "USD":
        Flight::set('currency', 'averageUSD');
        Flight::set('currencySym', 'USD');
        break;
    case "LTC":
        Flight::set('currency', 'averageLTC');
        Flight::set('currencySym', 'LTC');
        break;
    case "DOGE":
        Flight::set('currency', 'averageDOGE');
        Flight::set('currencySym', 'DOGE');
        break;
    default:
        Flight::set('currency', 'average');
        Flight::set('currencySym', 'BTC');
}

Flight::set('backupPath', __DIR__.'/app/backups');
Flight::set('dbPath', __DIR__.'/app/db');
Flight::set('timestart', $time_start);
Flight::set('getinfo', Flight::reddcoin()->getinfo());
Flight::set('getstaking', Flight::reddcoin()->getstakinginfo());

Flight::route('/', function() {
    Flight::redirect("/overview");
});

Flight::route('/overview', function(){
    Flight::set('transactions', array_reverse(Flight::reddcoin()->listtransactions()));
    Flight::set('interest', Flight::reddcoin()->getinterest());
    Flight::set('expected', Flight::controller()->seconds2human(str_replace(".","", trim(sprintf('%f', Flight::get('getstaking')['expectedtime']), '0'))));
    Flight::set('totalweightproc', Flight::controller()->get_percentage(Flight::get('getstaking')['netstakeweight'], Flight::get('getstaking')['totalweight'],FORMATTING));
    Flight::set('averageweight', Flight::controller()->get_percentage(Flight::get('getstaking')['totalweight'], Flight::get('getstaking')['averageweight'],FORMATTING));
    Flight::set('moneysupplyproc', Flight::controller()->get_percentage(Flight::get('getinfo')['moneysupply'], Flight::get('getinfo')['balance'],FORMATTING));
    Flight::set('interestproc', Flight::controller()->get_percentage(Flight::get('getinfo')['balance'], Flight::get('interest'),FORMATTING));
    Flight::set('balancecalc', number_format(Flight::get('getinfo')['balance']*$_SESSION['reddex'][Flight::get('currency')],8) . ' ' . Flight::get('currencySym'));
    Flight::set('interestcalc', (Flight::get('interest')*$_SESSION['reddex'][Flight::get('currency')]) . ' ' . Flight::get('currencySym'));
    Flight::set('chart', NULL);
    Flight::set('txCount', count(Flight::get('transactions')));
    
    if (file_exists(Flight::get('dbPath').'/stats.dat') && SHOWCHART && Flight::get('getstaking')['staking']) {
        $array = unserialize(file_get_contents(Flight::get('dbPath').'/stats.dat'));
        if(!empty($array)) {
            flight::set('chart', $array);
        }
    } 
    
    if(isset(Flight::request()->data->chart)) {
        flight::set('chartSelect', Flight::request()->data->chart);
    } else {
        flight::set('chartSelect', CHART);
    }
    
    include ("tpl/header.phtml");
    include ("tpl/overview.phtml");
    include ("tpl/footer.phtml");
});

Flight::route('/myaccounts', function(){
    include ("tpl/header.phtml");
    include ("tpl/myaccounts.phtml");
    include ("tpl/footer.phtml");
});

Flight::route('/myaddresses/@account(/@address)', function($account, $address){
    if($account == 'Default') $account = '';
    
    Flight::set('addresses', Flight::reddcoin()->getaddressesbyaccount($account));
    Flight::set('transactions', array_reverse(Flight::reddcoin()->listtransactions($account, 10)));
    Flight::set('txCount', count(Flight::get('transactions')));  
    
    if(isset($address)) {
        Flight::set('address', $address);
    }
    
    include ("tpl/header.phtml");
    include ("tpl/myaddresses.phtml");
    include ("tpl/footer.phtml");
});

Flight::route('/peers', function(){
    include ("tpl/header.phtml");
    include ("tpl/peers.phtml");
    include ("tpl/footer.phtml");
});

Flight::route('/transactions', function(){
    Flight::set('transactions', array_reverse(Flight::reddcoin()->listtransactions('*', 100)));
    Flight::set('txCount', count(Flight::get('transactions')));    
            
    include ("tpl/header.phtml");
    include ("tpl/transactions.phtml");
    include ("tpl/footer.phtml");
});

Flight::route('/backup', function(){
    if (file_exists(Flight::get('backupPath').'/wallet.dat')) {
        unlink(Flight::get('backupPath').'/wallet.dat');
    }
    
    if ($dh = opendir(Flight::get('backupPath'))) {
        $i=0;
        $backups = array();
        while (($file = readdir($dh)) !== false) {
            if(!in_array($file, array('.','..','...'))) {
                $backups[$i]['name'] = $file;
                $backups[$i]['created'] = explode("-",$file)[0];
                $backups[$i++]['size'] = Flight::controller()->formatSizeUnits(filesize(Flight::get('backupPath')."/{$file}"));
            }
        }
        closedir($dh);
        rsort($backups);
    }
    
    include ("tpl/header.phtml");
    include ("tpl/backup.phtml");
    include ("tpl/footer.phtml");
});

Flight::route('/backup/current', function(){
    if (file_exists(Flight::get('backupPath').'/wallet.dat')) {
        unlink(Flight::get('backupPath').'/wallet.dat');
    }
    
    Flight::reddcoin()->backupwallet(Flight::get('backupPath'));
    
    rename(Flight::get('backupPath').'/wallet.dat', Flight::get('backupPath').'/'.time().'-wallet.dat');
    
    Flight::redirect("/backup");
});

Flight::route('/downloadBackup/@id', function($id){
    if($id == 0) {
        Flight::reddcoin()->backupwallet(Flight::get('backupPath'));
        $id = Flight::get('backupPath').'/wallet.dat';
    } else {
        $id = Flight::get('backupPath')."/{$id}";
    }
    
    header("Content-Disposition: attachment; filename=\"" . basename($id) . "\"");
    header("Content-Type: application/force-download");
    header("Content-Length: " . filesize($id));
    header("Connection: close");
});

Flight::route('/deleteBackup/@id', function($id){
    if($id == 0) {
        if ($dh = opendir(Flight::get('backupPath'))) {
            while (($file = readdir($dh)) !== false) {
                if(!in_array($file, array('.','..','...'))) {
                    unlink(Flight::get('backupPath')."/{$file}");
                }
            }
            closedir($dh);
        }
    } else {
        if (file_exists(Flight::get('backupPath')."/{$id}")) {
            unlink(Flight::get('backupPath')."/{$id}");
        }
    }

    Flight::redirect("/backup");
});

Flight::route('/address/@account', function($account){
    
    $newAddress = ($account == 'Default') ? Flight::reddcoin()->getnewaddress('') : Flight::reddcoin()->getnewaddress($account);

    Flight::redirect("/myaddresses/{$account}/{$newAddress}");
});

Flight::route('/account', function(){
    Flight::redirect("/address/".Flight::request()->data->account);
});

Flight::route('/stats', function(){
    $newArray = array("averageweight" => Flight::get('getstaking')['averageweight'], 
    "totalweight" =>Flight::get('getstaking')['totalweight'], 
    "netstakeweight" => Flight::get('getstaking')['netstakeweight'], 
    "interest" => Flight::reddcoin()->getinterest(), 
    "balance" => Flight::get('getinfo')['balance'],
    "connections" => Flight::get('getinfo')['connections'], 
    "time" => time());

    if (!file_exists(Flight::get('dbPath').'/stats.dat')) {
        file_put_contents(Flight::get('dbPath').'/stats.dat', serialize(array($newArray)));
    } else {
        $array = unserialize(file_get_contents(Flight::get('dbPath').'/stats.dat'));
        array_push($array, $newArray);
        file_put_contents(Flight::get('dbPath').'/stats.dat', serialize($array));
    }
    
    Flight::redirect("/overview");
});

Flight::start();