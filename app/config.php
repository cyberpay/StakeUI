<?php
// Wallet RPC Information
$wallet = array(
    "user" => "rpcusername",
    "pass" => "rpcpassword",
    "host" => "localhost",
    "port" => 3451,
    "protocol" => "http");


// Secure your StakeUI interface here
define('USE_AUTHENTICATION', 1); // Put 0 to disable
define('USERNAME', 'username');
define('PASSWORD', 'password');

// Which currency to be used to calculate into
define('CURRENCY', 'BTC');

// How many decimals after dot (.) 
// Note: This setting is only for the percentage on overview page
define('FORMATTING', 5);

// Which weight to show on chart by default.
// OPTIONS:
// totalweight
// averageweight
// netstakeweight
// interest
// balance
// connections
define('CHART', 'totalweight');

// Show the chart on overview page or not (Default: 1)
define('SHOWCHART', 1);

// Which timezone times are reported in.
define('TIMEZONE', 'UTC');


// DO NOT CHANGE BELOW!
define('VERS', '1.0');