<?php

    include('../config.php');
    include('include/check_session.php');
    include('include/db.php');
    include('include/portfolio.php');
    include('include/stockclient.php');

    // Make sure the action is valid
    if (! isset($_GET['action'])) {
        die('Action not specified');
    }
    $action = $_GET['action'];
    if (($action != 'deposit') and ($action != 'withdraw')) {
        die('Invalid action');
    }

    // Make sure the portfolio is valid
    if (! isset($_GET['pid'])) {
        die('Portfolio ID not specified');
    }
    $pid = $_GET['pid'];
    $portfolio = new Portfolio($conn);
    $portfolio->load($pid);
    if (! $portfolio->isOwner($email)) {
        die('Invalid portfolio');
    }

    $name = $portfolio->getName();
    $title = ($action == 'deposit') ? "Transfer funds into $name" : "Withdraw funds from $name";
    include('templates/dialog_top.php');

    // Configure the maximum amount to transfer
    if ($action == 'deposit') {
        $client = new StockClient($stockserver_address, $stockserver_port);
        $max = $portfolio->maxDeposit($client);
    } else {
        $max = $portfolio->balance();
    }
?>

<form method="post"
      action="transfer_funds.php"
      id="form">
    <!-- Hidden fields for portfolio_id and action -->
    <input type="hidden" name="pid" value="<?php echo $pid; ?>">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <div class="mdl-card__supporting-text">
        <div class="mdl-grid">
            Please note that the available funds in a portfolio may never be more than 10% of the value of the portfolio with the exception of when the portfolio is initially created. An empty portfolio may have an initial transfer of up to $5,000.00.
        </div>
        <div class="mdl-grid">
            <div class="mdl-cell
                        mdl-cell--6-col">
                <h5>Max: <?php echo money_format("$%n", $max); ?></h5>
            </div>
            <div class="mdl-cell
                        mdl-cell--6-col">
                <span class="mdl-typography--title">$</span>
                <div class="mdl-textfield
                            mdl-js-textfield
                            mdl-textfield--floating-label"
                      id="amount_textfield">
                    <input class="mdl-textfield__input"
                           type="text"
                           pattern="[0-9]+\.[0-9]{2}"
                           id="amount"
                           name="amount"
                           required>
                    <label class="mdl-textfield__label"
                           for="amount">
                        Amount...
                    </label>
                    <span class="mdl-textfield__error">
                        Please enter a valid amount in the form 1234.56
                    </span>
                </div>
            </div>
        </div>
        <div class="mdl-grid">
            <div class="mdl-cell
                        mdl-cell--6-col
                        mdl-textfield
                        mdl-js-textfield
                        mdl-textfield--floating-label">
                <input class="mdl-textfield__input"
                       type="text"
                       pattern="[0-9]{9}"
                       id="routing"
                       name="routing"
                       required>
                <label class="mdl-textfield__label"
                       for="routing">
                    Routing Number...
                </label>
                <span class="mdl-textfield__error">Please enter a number in the form 123456789</span>
            </div>
            <div class="mdl-cell
                        mdl-cell--6-col
                        mdl-textfield
                        mdl-js-textfield
                        mdl-textfield--floating-label">
                <input class="mdl-textfield__input"
                       type="text"
                       pattern="[0-9]+"
                       id="account"
                       name="account"
                       required>
                <label class="mdl-textfield__label"
                       for="account">
                    Account Number...
                </label>
                <span class="mdl-textfield__error">Please enter a number</span>
            </div>
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
                Next
            </button>
        </div>
    </center>
</form>
<script>
    // Validate the form amount
    document.getElementById('form').addEventListener("submit", function(e){
        amount = document.getElementById('amount').value;
        if (amount > <?php echo $max; ?>) {
            amount_textfield = document.getElementById('amount_textfield');
            amount_textfield.classList.add("is-invalid");
            e.preventDefault();
        }
    });
</script>

<?php include('templates/dialog_bottom.php'); ?>
