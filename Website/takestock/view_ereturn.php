<?php
	include 'config.php';
	include 'include/stockclient.php';
	$client = new StockClient($stockserver_address, $stockserver_port);
	include 'templates/page_top.php';
	include 'include/db.php';
	include 'include/portfolio.php';

	//Make sure they passed a valid portfolio
	if(!isset($_GET['pid'])){
		die("No portfolio specified");
	}
	$pid = $_GET['pid'];
	portfolio = new Portfolio($conn);
	$portfolio->load(pid);
	if(!$portfolio->isOwner($email)){
		die("Invalid portfolio");
	}

?>

<center>
	<div class="mdl-card__export">
		<button class="mdl-button
					   mdl-js-button
					   mdl-js-ripple-effect
					   mdl-button--raised
					   mdl-button--colored"
				formaction="$basedir/actions/expected_return.php"
				type="submit">
			Export Report
		</button>
	</div>
</center>

<div class = "mdl-grid">
	<div class = "mdl-cell
				  mdl-cell--6-col">
		<div class="ereturn-table-div">
			<table class="mdl-data-table
						  mdl-js-data-table
						  mdl-shadow--2dp
						  ereturn-table">
				<thead class = "ereturn">
					<tr>
						<th class = "mdl-data-table__cell--non-numeric">Symbol</th>
						<th>Shares</th>
						<th>Last Trade</th>
						<th>Current Average Price</th>
						<th>Current Value</th>
						<th>Price in One Year</th>
						<th>Value in One Year</th>
						<th>Expected Return</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$totalER = 0.00;
						$stocks = $portfolio->currentStockReport();
						foreach($stocks as $stock){
							$symbol = $stock['symbol'];
							$shares = $stock['shares'];
							$lastTrade = $stock['lastTrade'];
							$currAvgPrice = $stock['totalPrice']/$shares;
							$currAvgPrice_output = money_format("$%n", $currAvgPrice);
							$currValue = $currAvgPrice * $shares;
							$currValue_output = money_format("$%n", $currValue);

							//Last year's price
							$stmt = $conn->prepare('SELECT price FROM historical WHERE symbol=? ORDER BY ABS(DATEDIFF(date, DATE(NOW() - INTERVAL 1 YEAR))) LIMIT 1;');
							$stmt->bindParam(1, $symbol);
							$stmt->execute();
							$result = $stmt->fetch(PDO::FETCH_ASSSOC);
							$previousPrice = $result['price'];

							//Calculate the growth ratio and future price
							$growth = ($currAvgPrice - $previousPrice) / $previousPrice;
							$futurePrice = $currAvgPrice + $currAvgPrice * $growth;
							$futurePrice_output = money_format("$%n", $futurePrice);

							$futureValue = $futurePrice * $shares;
							$futureValue_output = money_format("$%n", $futureValue);
							$expectedReturn = $futureValue - $currValue;
							$expectedReturn_output = money_format("$%n", $expectedReturn);
							$totalER += $expectedReturn;

							echo '<tr>';
							echo "		<td class='mdl-data-table__cell--non-numeric'>$symbol</td>";
							echo "		<td>$shares</td>";
							echo "		<td>$lastTrade</td>";
							echo "		<td>$currAvgPrice_output</td>";
							echo "		<td>$currValue_output</td>";
							echo "		<td>$futurePrice_output</td>";
							echo "		<td>$futureValue_output</td>";
							echo "		<td>$expectedReturn_output</td>";
							echo '</tr>';
						}
					?>
				</tbody>
				<?php
					$totalER_output = money_format("$%n", $totalER);
				?>
				<tfoot class="ereturn">
					<tr>;
						<th class='mdl-data-table__cell--non-numeric'>Total</th>;
						<td></td>;
						<td></td>;
						<td></td>;
						<td></td>;
						<td></td>;
						<td></td>;
						<td>$totalER_output/td>";
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>


<?php include "templates/page_bottom.php"; ?>