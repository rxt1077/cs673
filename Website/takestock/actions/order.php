<?php

include '../config.php';
include 'include/check_session.php';
include 'include/db.php';
include 'include/post_params.php';
include 'include/portfolio.php';
include 'include/stockclient.php';

$filename = $_FILES['file']['tmp_name'];

// Make sure a file was uploaded
if (! is_uploaded_file($filename)) {
    die('No file uploaded');
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

// Read the orders file
$handle = fopen($filename, "r");
if (! $handle) {
    die('Unable to read file');
}

$balance = $portfolio->balance();
$stocks = $portfolio->getStocks();
$orders = array();
$error = '';
$linenum = 1;
$newTotals = array();
while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $action = $data[0];
    if (! $action) {
        $error = 'Unable to load action';
        break;
    }
    if (($action != 'BUY') and ($action != 'SELL')) {
        $error = 'Action must be either BUY or SELL';
        break;
    }
    $symbol = $data[1];
    if (! $symbol) {
        $error = 'Unable to load symbol';
    }
    $shares = $data[2];
    if ((! $shares) or ($shares <= 0)) {
        $error = 'Unable to load shares';
    }
    // Use the 2019-01-02 price if it's our first time buying it
    if ($portfolio->firstBuy($symbol)) {
        $stmt = $conn->prepare("SELECT price FROM historic WHERE symbol=? AND date='2019-01-02'");
        $stmt->bindParam(1, $symbol);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $price = $result['price'];
    } else { // otherwise look it up
        $price = $client->getQuoteUSD($symbol);
    }
    if (! $price) {
        $error = 'Unable to get quote';
        break;
    }

    $numStocks = count($stocks);
    if ($action === 'SELL') {
        for ($i = 0; $i < $numStocks; $i++) {
            if ($stocks[$i]['symbol'] == $symbol) {
                if ($shares > $stocks[$i]['shares']) {
                    $error = 'Not enough shares to sell';
                    break;
                } else if (($shares == $stocks[$i]['shares']) and ($numStocks <= 7)) {
                    $error = 'Sale would drop the number of stocks below the minimum';
                    break;
                }
                $stocks[$i]['shares'] -= $shares;
                if ($stocks[$i]['shares'] <= 0) {
                    array_splice($stocks, $i, 1);
                }
                $balance += $price * $shares;
                break;
            }
        }
        if ($error != '') {
            break;
        }
    } else { // BUY
        $balance -= $price * $shares;
        if ($balance < 0.00) {
            $error = 'Purchase would drop balance below $0.00';
            break;
        }
        for ($i = 0; $i < $numStocks; $i++) {
            if ($stocks[$i]['symbol'] == $symbol) {
                $stocks[$i]['shares'] += $shares;
            }
        }
        if ($i == $numStocks) {
            if ($numStocks >= 10) {
                $error = 'Purchase would push number of stocks above maximum';
                break;
            }
            $stock = array();
            $stock['portfolio_id'] = $pid;
            $stock['symbol'] = $symbol;
            $stock['shares'] = $shares;
            array_push($stocks, $stock);
        }
    }

    $trade = array();
    $trade['action'] = $action;
    $trade['symbol'] = $symbol;
    $trade['shares'] = $shares;
    $trade['price'] = $price;
    array_push($orders, $trade);

    $linenum++;
}
fclose($handle);
if ($error != '') {
    echo "Line $linenum: $action,$symbol,$shares<br>";
    echo "$error";
    die();
}

// Make sure we are trending towards a better 70/30 ratio
$previousTotals = $portfolio->valueByGroup($client);
$previousTotal = $previousTotals['nifty50'] + $previousTotals['dow30'];
$previousNifty50Ratio = $previousTotals['nifty50'] / $previousTotal;
$previousDow30Ratio = $previousTotals['dow30'] / $previousTotal;
$previousDow30Diff = abs(0.7 - $previousDow30Ratio);
$previousNifty50Diff = abs(0.3 - $previousNifty50Ratio);

$newTotals = array();
$newTotals['nifty50'] = 0.00;
$newTotals['dow30'] = 0.00;
foreach ($stocks as $stock) {
    $value = $client->getQuoteUSD($stock['symbol']);
    $amount = $value * $stock['shares'];
    if (substr($stock['symbol'], -3) === '.NS') {
        $newTotals['nifty50'] += $amount;
    } else {
        $newTotals['dow30'] += $amount;
    }
}
$newTotal = $newTotals['nifty50'] + $newTotals['dow30'];
$newNifty50Ratio = $newTotals['nifty50'] / $newTotal;
$newDow30Ratio = $newTotals['dow30'] / $newTotal;

$newDow30Diff = abs(0.7 - $newDow30Ratio);
$newNifty50Diff = abs(0.3 - $newNifty50Ratio);

if ($newDow30Diff > $previousDow30Diff) {
    echo "Executing these orders would result in a worse Dow 30 percentage";
    die();
}
if ($newNifty50Diff > $previousNifty50Diff) {
    echo "Executing these orders would result in a worse Nifty 50 percentage";
    die();
}

// Perform the actions
foreach ($orders as $order) {
    if ($order['action'] === 'SELL') {
        $portfolio->sellStock($order['symbol'], $order['shares'], $order['price']);
    } else { // BUY
        $portfolio->buyStock($order['symbol'], $order['shares'], $order['price']);
    }
}
$portfolio->save();

header("Location: $basedir/index.php?pid=$pid");

?>
