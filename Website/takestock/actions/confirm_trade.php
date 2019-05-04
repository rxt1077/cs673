<?php
    include '../config.php';
    include 'include/check_session.php';
    include 'include/db.php';
    include 'include/portfolio.php';
    include 'include/stockclient.php';

    // Make sure they specified buy or sell
    if (! isset($_GET['action'])) {
        die("No action specified");
    }
    $action = $_GET['action'];
    if (($action != 'buy') and ($action != 'sell')) {
        die("Invalid action");
    }

    // Make sure a portoflio was passed and the signed in user owns it
    if (! isset($_GET['pid'])) {
        die("Portfolio not specified");
    }
    $pid = $_GET['pid'];
    $portfolio = new Portfolio($conn);
    $portfolio->load($pid);
    if (! $portfolio->isOwner($email)) {
        die("Invalid portfolio");
    }

    // Make sure a valid stock symbol was passed
    if (! isset($_GET['symbol'])) {
        die("No stock symbol specified");
    }
    $symbol = $_GET['symbol'];

    // Set up a connection to StockServer 
    $client = new StockClient($stockserver_address, $stockserver_port);

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
        die("Unable to get quote");
    }

    $numStocks = count($portfolio->getStocks());

    // Determine what amount they can buy/sell
    if ($numStocks < 7) {
        // If they don't have 7 stocks yet, don't let them sell anything and
        // allow them to buy in any way they choose
        $buy_amount = $portfolio->balance();
        $sell_amount = 0;
    } else {
        // Once they do have 7 stocks, trades should always move towards the
        // 70 / 30 ratio
        $totals = $portfolio->valueByGroup($client); 
        $total = $totals['nifty50'] + $totals['dow30'];
        if (substr($symbol, -3) === '.NS') {
            $ratio = 0.30;
            $current_value = $totals['nifty50'];
        } else {
            $ratio = 0.70;
            $current_value = $totals['dow30'];
        }
        $max_value = $total * $ratio;
        $buy_amount = $max_value - $current_value;
        if ($buy_amount < 0) {
            $buy_amount = 0;
        }
        $sell_amount = $current_value - $max_value;
        if ($sell_amount < 0) {
            $sell_amount = 0;
        }
    }

    // Set the max value for the slider accordingly
    if ($action == 'buy') {
        $available = min($portfolio->balance(), $buy_amount);
        $title = 'Confirm Purchase';
    } else {
        $available = min($portfolio->getShares($symbol) * $price, $sell_amount);
        $title = 'Confirm Sale';
    }
    $max = floor($available / $price);
  
    // Don't allow people to have more than 10 stocks 
    if (($action == 'buy') and ($numStocks >= 10)) {
        if ($portfolio->getShares($symbol) == 0) {
            $max = 0;
        }
    }
    // Don't allow people to have less than 7 stocks
    if (($action == 'sell') and ($numStocks <= 7)) {
        $max = min($max, $portfolio->getShares($symbol) - 1);
    }

    // This allows us to confirm that the trade was offered without the user
    // tampering with it
    unset($_SESSION['trade']);
    $trade = array();
    $trade['action'] = $action;
    $trade['symbol'] = $symbol;
    $trade['price'] = $price;
    $trade['max'] = $max;
    $trade['time'] = time();
    $_SESSION['trade'] = $trade;

    include 'templates/dialog_top.php';
?>

<form method="post"
      action='<?php echo ($max > 0) ? "$basedir/actions/trade_stock.php" : "$basedir/index.php"; ?>'>
    <!-- Hidden fields for portfolio_id and stock symbol -->
    <input type="hidden" name="symbol" value="<?php echo $symbol; ?>">
    <input type="hidden" name="pid" value="<?php echo $pid; ?>">
    <div class="mdl-card__supporting-text">
        <!-- First row -->
        <div class="mdl-grid">
            <div>
                This quote remains valid for <span id="seconds">60</span> more seconds.
            </div>
            <script>
                seconds = 60;
                setInterval(function() {
                    document.getElementById("seconds").innerHTML = seconds;
                    seconds--;
                    if (seconds == 0) {
                        location.reload();
                    }
                }, 1000);
            </script>
        </div>
        <!-- Second row -->
        <div class="mdl-grid">
            <div class="mdl-cell
                        mdl-cell--6-col">
                <h5><?php echo "$symbol/share: " . money_format("$%n", $price); ?></h5>
            </div>
            <div class="mdl-cell
                        mdl-cell--6-col">
                <h5>
                <?php
                    if ($action == 'buy') {
                        echo "May buy up to: ";
                    } else {
                        echo "May sell up to: ";
                    }
                    echo money_format("$%n", $available);
                ?>
                </h5>
            </div>
        </div>
        <!-- Third row -->
        <div class="mdl-grid">
            <div class="mdl-cell
                        mdl-cell--6-col">
                <h5>Shares: <span id="shares_num">0</span></h5>
            </div>
            <div class="mdl-cell
                        mdl-cell--6-col">
                <h5>Value: $<span id="shares_cost">0.00</span></h5>
            </div>
            <script>
                function updateSlider(shares) {
                    var price = <?php echo $price; ?>;
                    var shares_num, shares_cost;

                    shares_num = document.getElementById("shares_num");
                    shares_num.innerHTML = shares;
                    shares_cost = document.getElementById("shares_cost");
                    shares_cost.innerHTML = (shares * price).toFixed(2);
                }
            </script>
        </div>
        <!-- Fourth row -->
        <div class="mdl-grid">
            <?php

            if ($max > 0) {
                echo '<p style="width: 100%;">';
                echo '  <input class="mdl-slider';
                echo '                mdl-js-slider"';
                echo '         type="range"';
                echo '         min="0"';
                echo "         max='$max'";
                echo '         value="0"';
                echo '         step="1"';
                echo '         tabindex="0"';
                echo '         oninput="updateSlider(this.value)"';
                echo '         name="shares"';
                echo '         id="shares_slider">';
                echo '</p>';
            } else {
                echo "No valid trade is available. Remember your portfolio must maintain between 7-10 stocks and its value must be 70% in the Dow 30 and 30% in the Nifty 50.";
            }
            
            ?>
        </div>
    </div>
    <center>
        <div class="mdl-card__actions">
            <button class="mdl-button
                           mdl-js-button
                           mdl-js-ripple-effect
                           mdl-button--raised
                           mdl-button--colored"
                    type="submit">
                <?php echo ($max > 0) ? 'Confirm' : 'Back'; ?>
            </button>
        </div>
    </center>
</form>

<?php include "templates/dialog_bottom.php"; ?>
