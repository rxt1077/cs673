<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config.php');
include('include/check_session.php');
include('include/db.php');
include('include/portfolio.php');
include 'include/stockclient.php';

// Make sure they passed a valid portfolio
if (! isset($_GET['pid'])) {
    die("No portfolio specified");
}
$pid = $_GET['pid'];
$portfolio = new Portfolio($conn);
$portfolio->load($pid);
if (! $portfolio->isOwner($email)) {
    die("Invalid portfolio");
}

// Start up the stock client for fetching prices
$client = new StockClient($stockserver_address, $stockserver_port);

// Set up the filenames
$t = time();
$filename = "$t.csv";

// Dump the portfolio to a CSV file for R's input
$t = time();
$file = fopen("$upload_path/$filename", "w");
foreach ($portfolio->getStocks() as $stock) {
    $symbol = $stock['symbol'];
    $shares = $stock['shares'];
    $price = $client->getQuoteUSD($symbol);
    fputcsv($file, array($symbol, $shares, $price));
}
fclose($file);

system("$runner_path/Runner.sh hello_world.R $upload_path/$filename");

?>
