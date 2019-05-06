<?php

include '../config.php';
include 'include/check_session.php';
include 'include/db.php';
include 'include/post_params.php';
include 'include/portfolio.php';
include 'include/stockclient.php';

// Make sure the parameters were passed
$actions = getparam('actions');
if ($actions == '') {
    die('No actions specified');
}
$symbols = getparam('symbols');
if ($symbols == '') {
    die('No symbols specified');
}
$amounts = getparam('amounts');
if ($amounts == '') {
    die('No amounts specified');
}
if (! ((count($actions) == count($symbols)) && (count($symbols) == count($amounts)))) {
    die('All three arrays are not equal');
}

// Make sure there is a portfolio and they own it
$pid = getparam('pid');
if ($pid == '') {
    die('No portfolio id specified');
}
$portfolio = new Portfolio($conn);
$portfolio->load($pid);
if (! $portfolio->isOwner($email)) {
    die('Invalid portfolio');
}

// Connect to the stockclient
$client = new StockClient($stockserver_address, $stockserver_port);

// Perform the actions
for ($i = 0; $i < count($actions); $i++) {
    $action = $actions[$i];
    $symbol = $symbols[$i];
    $amount = $amounts[$i];
    $price = $client->getQuoteUSD($symbol);
    if (! $price) {
        die('Unable to get price');
    }
    if ($action == 'SELL') {
        $portfolio->sellStock($symbol, $amount, $price);
    } else {
        $portfolio->buyStock($symbol, $amount, $price);
    }
}
$portfolio->save();

header("Location: $basedir/index.php?pid=$pid");

?> 
