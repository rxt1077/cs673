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
    if ($symbol == '') {
        header("Location: $basedir/index.php?pid=$pid");
    }

    // Set up a connection to StockServer
    $client = new StockClient($stockserver_address, $stockserver_port);

    // Delete all previous quotes for this user
    $stmt = $conn->prepare('DELETE FROM quote WHERE email=?;');
    $stmt->bindParam(1, $email);
    $stmt->execute();

    // Get our quote and store it in the DB so we can confirm later on if they
    // choose to buy (it's auto-timestamped). You can't trust the client to
    // pass this correctly
    $price = $client->getQuoteUSD($symbol);
    $stmt = $conn->prepare('INSERT INTO quote (email, symbol, price) VALUES (?, ?, ?);');
    $stmt->bindParam(1, $email);
    $stmt->bindParam(2, $symbol);
    $stmt->bindParam(3, $price);
    $stmt->execute();

    if ($action == 'buy') {
        $title = 'Confirm Purchase';
    } else {
        $title = 'Confirm Sale';
    }

    include 'templates/dialog_top.php';
?>

<form method="post">
    <!-- Hidden fields for portfolio_id and stock symbol -->
    <input type="hidden" name="symbol" value="<?php echo $symbol; ?>">
    <input type="hidden" name="pid" value="<?php echo $pid; ?>">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
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
            <?php
                if ($action == 'buy') {
                    if ($portfolio->balance() == 0.00) {
                        $max = 0;
                    } else {
                        $max = floor($portfolio->balance() / $price);
                    }
                } else {
                    $max = $portfolio->getShares($symbol);
                }
            ?>
            <div class="mdl-cell
                        mdl-cell--6-col">
                <h5><?php echo "$symbol/share: " . money_format("$%n", $price); ?></h5>
            </div>
            <div class="mdl-cell
                        mdl-cell--6-col">
                <h5>Available Funds: <?php $portfolio->printBalance(); ?></h5>
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
            <p style="width: 100%;">
                <input class="mdl-slider
                              mdl-js-slider"
                        type="range"
                        min="0"
                        max="<?php echo $max; ?>"
                        value="0"
                        step="1"
                        tabindex="0"
                        oninput="updateSlider(this.value)"
                        name="shares"
                        id="shares_slider">
            </p>
        </div>
    </div>
    <center>
        <div class="mdl-card__actions">
            <button class="mdl-button
                           mdl-js-button
                           mdl-js-ripple-effect
                           mdl-button--raised
                           mdl-button--colored"
                    type="submit"
                    formaction="trade_stock.php">
                Confirm
            </button>
        </div>
    </center>
</form>

<?php include "templates/dialog_bottom.php"; ?>
