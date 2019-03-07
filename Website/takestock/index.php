<?php
    include 'config.php'; 
    include 'templates/page_top.php';
?>

<div>List all of the stocks and their current value</div>
<div>List all of the transactions</div>
<?php
echo "<script src='$basedir/js/autocomplete.js'></script>";
echo "<script src='$basedir/js/symbols.js'></script>";
?>
<div class="mdl-grid">
    <div class="mdl-cell
                mdl-cell--6-col">
        <h4>Portfolio Value:</h4>
    </div>
    <div class="mdl-cell
                mdl-cell--6-col">
        <h4>Available Funds: <?php $portfolio->printCash(); ?></h4>
    </div>
</div>
<div class="mdl-grid">
    <!-- Buy -->
    <div class="mdl-cell
                mdl-cell--4-col">
        <form autocomplete="off"
              action='<?php echo "$basedir/actions/confirm_purchase.php"; ?>' 
              method="get">
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
              action="actions/sell_stock.php"
              method="get">
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
              action="actions/order.php"
              method="post">
            <input type="hidden"
                   name="pid"
                   value="<?php echo $pid; ?>">
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
