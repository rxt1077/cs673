<?php
    include 'config.php';
    include 'include/stockclient.php';
    $client = new StockClient($stockserver_address, $stockserver_port);    
    include 'templates/page_top.php';
?>

<?php
echo "<script src='$basedir/js/autocomplete.js'></script>";
echo "<script src='$basedir/js/symbols.js'></script>";
?>
<!-- Stock table and log -->
<div class="mdl-grid">
    <div class="mdl-cell
                mdl-cell--6-col">
        <h5>Stocks</h5>
        <table class="mdl-data-table
                      mdl-js-data-table
                      mdl-shadow--2dp
                      stock-table">
            <thead class="stock-table">
                <tr>
                    <th class="mdl-data-table__cell--non-numeric">Symbol</th>
                    <th>Shares</th>
                    <th>Price/Share</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $total = 0.00;
                foreach ($portfolio->getStocks() as $stock) {
                    $symbol = $stock['symbol'];
                    $shares = $stock['shares'];
                    $price = $client->getQuoteUSD($symbol);
                    $price_output = money_format("$%n", $price);
                    $value = $shares * $price;
                    $value_output = money_format("$%n", $value);
                    $total += $value;
                    echo '<tr>';
                    echo "    <td class='mdl-data-table__cell--non-numeric'>$symbol</td>";
                    echo "    <td>$shares</td>";
                    echo "    <td>$price_output</td>";
                    echo "    <td>$value_output</td>";
                    echo '</tr>';
                }                    
            ?>
            </tbody>
            <tfoot class="stock-table">
                <tr>
                    <th class="mdl-data-table__cell--non-numeric">Total</th>
                    <td></td>
                    <td></td>
                    <?php
                        $total_output = money_format("$%n", $total);
                        echo "<td>$total_output</td>";
                    ?>
                </tr>
            </tfoot>                   
        </table>
    </div>
    <div class="mdl-cell
                mdl-cell--6-col">
        <h5>Recent Actions</h5>
        <table class="mdl-data-table
                      mdl-js-data-table
                      mdl-shadow--2dp
                      stock-table">
            <tbody>
                <tr>
                <?php
                    foreach ($portfolio->getLogs() as $entry) {
                        echo "<td class='mdl-data-table__cell--non-numeric'>$entry</td>";
                    }
                ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="mdl-grid">
    <!-- Balance Deposit/Withdraw -->
    <div class="mdl-cell
                mdl-cell--12-col">
        <form>
            <span class="mdl-typography--title">Available Funds: <?php $portfolio->printBalance(); ?></span>
            <button class="mdl-button
                           mdl-js-button
                           mdl-button--icon
                           mdl-js-ripple-effect
                           mdl-button--colored"
                    id="add">
                <i class="material-icons">add</i>
            </button>
            <button class="mdl-button
                           mdl-js-button
                           mdl-button--icon
                           mdl-js-ripple-effect
                           mdl-button--colored">
                <i class="material-icons">remove</i>
            </button>
        </form>
    </div>
</div>
<!-- Portfolio actions -->
<div class="mdl-grid">
    <!-- Buy -->
    <div class="mdl-cell
                mdl-cell--4-col">
        <form autocomplete="off"
              action='<?php echo "$basedir/actions/get_quote.php"; ?>' 
              method="get">
            <input type="hidden"
                   name="action"
                   value="buy">
            <input type="hidden"
                   name="pid"
                   value="<?php echo $pid; ?>">
            <span class="mdl-typography--title">Buy</span>
            <div class="mdl-textfield
                        mdl-js-textfield
                        autocomplete">
                <input class="mdl-textfield__input"
                       type="text"
                       id="buy_symbol"
                       name="symbol">
                <label class="mdl-textfield__label"
                       for="buy_symbol">
                    Ticker symbol...
                </label>
            </div>
            <button class="mdl-button
                           mdl-js-button
                           mdl-button--icon
                           mdl-js-ripple-effect
                           mdl-button--colored">
                <i class="material-icons">add</i>
            </button>
        </form>
        <script>
            autocomplete(document.getElementById("buy_symbol"), symbols);
        </script>
    </div>
    <!-- Sell -->
    <div class="mdl-cell
                mdl-cell--4-col">
        <form autocomplete="off"
              action='<?php echo "$basedir/actions/get_quote.php"; ?>'
              method="get">
            <input type="hidden"
                   name="action"
                   value="sell">
            <input type="hidden"
                   name="pid"
                   value="<?php echo $pid; ?>">
            <span class="mdl-typography--title">Sell</span>
            <div class="mdl-textfield
                        mdl-js-textfield
                        autocomplete">
                <input class="mdl-textfield__input"
                       type="text"
                       id="sell_symbol"
                       name="symbol">
                <label class="mdl-textfield__label"
                       for="sell_symbol">
                    Ticker symbol...
                </label>
            </div>
            <button class="mdl-button
                           mdl-js-button
                           mdl-button--icon
                           mdl-js-ripple-effect
                           mdl-button--colored">
                <i class="material-icons">remove</i>
            </button>
        </form>
        <script>
            autocomplete(document.getElementById("sell_symbol"), <?php echo $portfolio->jsonStockSymbols(); ?>);
        </script>
    </div>
    <!-- Order -->
    <div class="mdl-cell
                mdl-cell-4-col">
        <form autocomplete="off"
              enctype="multipart/form-data"
              action='<?php echo "$basedir/actions/order.php"; ?>'
              method="post">
            <input type="hidden"
                   name="pid"
                   value="<?php echo $pid; ?>">
            <input type="hidden"
                   name="MAX_FILE_SIZE"
                   value="30000">
            <span class="mdl-typography--title">Order</span>
            <div class="mdl-textfield
                        mdl-js-textfield
                        mdl-textfield--file">
                <input class="mdl-textfield__input"
                       placeholder="No file chosen"
                       type="text"
                       id="file_text"
                       readonly>
            </div>
            <div class="mdl-button
                        mdl-js-button
                        mdl-button--icon
                        mdl-js-ripple-effect
                        mdl-button--colored
                        mdl-button--file">
                <i class="material-icons">attach_file</i>
                <input type="file"
                       name="file"
                       id="file"
                       onchange="document.getElementById('file_text').value=this.files[0].name;">
            </div>
            <button type="submit"
                    class="mdl-button
                           mdl-js-button
                           mdl-button--icon
                           mdl-js-ripple-effect
                           mdl-button--colored">
                <i class="material-icons">send</i>
            </button>
        </form>
    </div>
</div>

<?php include "templates/page_bottom.php"; ?>
