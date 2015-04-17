<?php
$path = '/var/www/app';
include($path.'/config.php');
require $path.'/jsonRPCClient.php';
$coin = new jsonRPCClient("{$wallet['protocol']}://{$wallet['user']}:{$wallet['pass']}@{$wallet['host']}:{$wallet['port']}");
$staking = $coin->getstakinginfo();
$info = $coin->getinfo();

$newArray = array("averageweight" => $staking['averageweight'],
    "totalweight" =>$staking['totalweight'],
    "netstakeweight" => $staking['netstakeweight'],
    "interest" => $coin->getinterest(),
    "balance" => $info['balance'],
    "connections" => $info['connections'],
    "time" => time());

if (!file_exists($path.'/db/stats.dat')) {
    file_put_contents($path.'/db/stats.dat', serialize(array($newArray)));
} else {
    $array = unserialize(file_get_contents($path.'/db/stats.dat'));
    array_push($array, $newArray);
    file_put_contents($path.'/db/stats.dat', serialize($array));
}