<?php

include('../config.php');
include('include/check_session.php');
include('include/db.php');
include('include/portfolio.php');

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

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=expected_return.csv');

$output = fopen('php://output', 'w');

fputcsv($output, array(
    'Symbol',
    'Shares',
    'Last Trade',
    'Current Average Price',
    'Current Value',
    'Price in One Year',
    'Value in One Year',
    'Expected Return'
));

$totalER = 0.00;
$stocks = $portfolio->currentStockReport();
foreach ($stocks as $stock) {
    $symbol = $stock['symbol'];
    $shares = $stock['shares'];
    $lastTrade = $stock['lastTrade'];
    $currAvgPrice = $stock['totalPrice'] / $shares;
    $currValue = $currAvgPrice * $shares;

    // Get last year's price (as close to the date as we can)    
    $stmt = $conn->prepare('
SELECT price
FROM historic
WHERE symbol=?
ORDER BY ABS(DATEDIFF(date, DATE(NOW() - INTERVAL 1 YEAR)))
LIMIT 1;');
    $stmt->bindParam(1, $symbol);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $previousPrice = $result['price'];

    // Calculate the growth ratio and future price
    $growth = ($currAvgPrice - $previousPrice) / $previousPrice;
    $futurePrice = $currAvgPrice + $currAvgPrice * $growth;

    $futureValue = $futurePrice * $shares;
    $expectedReturn = $futureValue - $currValue;
    $totalER += $expectedReturn;
    fputcsv($output, array(
        $symbol,
        $shares,
        $lastTrade,
        money_format('$%n', $currAvgPrice),
        money_format('$%n', $currValue),
        money_format('$%n', $futurePrice),
        money_format('$%n', $futureValue),
        money_format('$%n', $expectedReturn)
    ));
}
fputcsv($output, array(
    'TOTAL',
    '',
    '',
    '',
    '',
    '',
    '',
    money_format('$%n', $totalER)
));

?>
