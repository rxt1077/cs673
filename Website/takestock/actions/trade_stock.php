<?php
    
    include '../config.php';
    include 'include/check_session.php';
    include 'include/db.php';
    include 'include/post_params.php';
    include 'include/portfolio.php';

    // Make sure the trade is valid
    if (! isset($_SESSION['trade'])) {
        die("Invalid trade");
    }
    $trade = $_SESSION['trade'];

    if ((time() - $trade['time']) > 60) {
        die("Time limit expired");
    }
    $price = $trade['price']; 
    $action = $trade['action'];
    $max = $trade['max'];

    // Make sure there is a symbol 
    $symbol = getparam('symbol');
    if ($symbol == '') {
        die("No symbol specified");
    }

    // Make sure there is a portfolio and they own it
    $pid = getparam('pid');
    if ($pid == '') {
        die("No portofolio id specified");
    }
    $portfolio = new Portfolio($conn);
    $portfolio->load($pid);
    if (! $portfolio->isOwner($email)) {
        die("Invalid portfolio");
    }

    // Make sure they specified how many shares
    $shares= getparam('shares');
    if ($shares == '') {
        die("Shares amount not specified.");
    }
    if ($shares > $max) {
        die("Shares greater than max");
    }

    // perform the action
    if ($action == 'buy') {
        $portfolio->buyStock($symbol, $shares, $price);
    } else {
        $portfolio->sellStock($symbol, $shares, $price);
    }
    $portfolio->save();

    // redirect back to main page
    header("Location: $basedir/index.php?pid=$pid");

?>
