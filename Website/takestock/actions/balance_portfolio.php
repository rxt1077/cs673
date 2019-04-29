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
$input_file = "$uploads_path/input-$t.csv";
$output_file = "$uploads_path/output-$t.csv";

// Dump the portfolio to a CSV file for R's input
$t = time();
$file = fopen($input_file, "w");
fputcsv($file, array('symbol', 'shares', 'price'));
foreach ($portfolio->getStocks() as $stock) {
    $symbol = $stock['symbol'];
    $shares = $stock['shares'];
    $price = $client->getQuoteUSD($symbol);
    fputcsv($file, array($symbol, $shares, $price));
}
fclose($file);

// Run the test script, first arg is input, second arg is output
exec("$runner_path/Runner.sh $runner_path/scripts/test.R $input_file $output_file");

// Print out the output file
$file = fopen($output_file, "r");
print_r(fgetcsv($file));
fclose($file);

?>
