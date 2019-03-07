<?php
    include '../config.php';
    include 'include/check_session.php';
    include 'include/db.php';
    include 'include/portfolio.php';
    include 'include/stockclient.php';

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

    // Make sure a stock symbol was passed
    if (! isset($_GET['symbol'])) {
        die("No stock symbol specified");
    }
    $symbol = $_GET['symbol'];

    // Setup a connection to StockServer
    $client = new StockClient();

    $title = 'Confirm Purchase';
    include 'templates/dialog_top.php';
?>

<form method="post">
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
                $price = $client->getQuoteUSD($symbol);
                if ($portfolio->getCash() == 0.00) {
                    $max = 0;
                } else {
                    $max = floor($portfolio->getCash() / $price);
                }
            ?>
            <div class="mdl-cell
                        mdl-cell--6-col">
                <h5><?php echo "$symbol/share: \$$price"; ?></h5>
            </div>
            <div class="mdl-cell
                        mdl-cell--6-col">
                <h5>Available Funds: <?php $portfolio->printCash(); ?></h5>
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
                <h5>Cost: $<span id="shares_cost">0.00</span></h5>
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
                        value="1"
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
                    formaction="buy_stock.php">
                Confirm
            </button>
        </div>
    </center>
</form>

<?php include "templates/dialog_bottom.php"; ?>
