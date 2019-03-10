<?php

include('../config.php');
include('include/check_session.php');
include('include/db.php');
include('include/portfolio.php');
include('include/stockclient.php');
include('include/post_params.php');

// Make sure the action is valid
$action = getparam('action');
if ($action == '') {
    die('Invalid action');
}

// Make sure the amount is valid
$amount = getparam('amount');
if ($amount <= 0.00) {
    die('Invalid transfer amount');
}

// Make sure the portfolio is valid
$pid = getparam('pid');
if ($pid == '') {
    die('Portfolio ID not specified');
}
$portfolio = new Portfolio($conn);
$portfolio->load($pid);
if (! $portfolio->isOwner($email)) {
    die('Invalid portfolio');
}

// Check to make sure they are within the maximum transfer amount
if ($action == 'deposit') {
    $client = new StockClient($stockserver_address, $stockserver_port);
    $max = $portfolio->maxDeposit($client);
} else {
    $max = $portfolio->balance();
}
if ($amount > $max) {
    die("Transfer amount above max");
}

// TODO: We should verify their bank credentials

// perform the transfer
if ($action == 'deposit') {
    $portfolio->deposit($amount);
} else {
    $portfolio->withdraw($amount);
}
$portfolio->save();

header("Location: $basedir/index.php");
    
?>
