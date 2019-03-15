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

    $stocks = $portfolio->currentStockReport();
    echo "<pre>";
    var_dump($stocks);
    echo "</pre>";

?>
