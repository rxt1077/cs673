<?php
    
    include '../config.php';
    include 'include/check_session.php';
    include 'include/db.php';
    include 'include/post_params.php';
    include 'include/portfolio.php';

    // Make sure they specified an action
    $action = getparam('action');
    if (($action != 'buy') and ($action != 'sell')) {
        die("No action specified");
    }

    // Make sure there is a symbol and the quote is valid
    $symbol = getparam('symbol');
    if ($symbol == '') {
        die("No symbol specified");
    }
    $sql  = <<<'EOD'
SELECT price
FROM quote
WHERE email=? AND
      symbol=? AND
      ts >= DATE_SUB(NOW(), INTERVAL 60 SECOND)
ORDER BY ts DESC;
EOD;
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(1, $email);
    $stmt->bindParam(2, $symbol);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (! isset($row['price'])) {
        die("No valid quote found.");
    } else {
        $price = $row['price'];
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

    if ($action == 'buy') {
        // Make sure they have enough money
        if (($shares * $price) > $portfolio->getCash()) {
            die("Insufficient funds.");
        }

        // Put the stock in the portfolio and save it
        $portfolio->buyStock($symbol, $shares, $price);
    } else {
        // Make sure they have enough shares
        if ($shares > $portfolio->getShares($symbol)) {
            die("Not enough shares for trade.");
        }

        // Remove the stock from the portfolio and save it
        $portfolio->sellStock($symbol, $shares, $price);
    }
    $portfolio->save();

    // redirect back to main page
    header("Location: $basedir/index.php");

?>
