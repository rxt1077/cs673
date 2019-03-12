<?php

class Portfolio {
    private $conn;
    private $id;
    private $name;
    private $stocks;
    private $cash;
    private $email;
    private $log;

    // needs the database connection
    function __construct($conn) {
        $this->conn = $conn;
    }

    // creates and saves a new portfolio
    public function create($name, $email) {
        $stmt = $this->conn->prepare('INSERT INTO portfolio (name, email, cash) VALUES (?,?,0.00);');
        $stmt->bindParam(1, $name);
        $stmt->bindParam(2, $email);
        $stmt->execute();
        $stmt = $this->conn->prepare('SELECT LAST_INSERT_ID() AS id;');
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->id = $result['id'];
        $this->stocks = array();
        $this->cash = 0.00;
        $this->email = $email;
        $this->log('CREATED', '', 0, 0.00);
    }

    // adds an entry to the log table for this portfolio
    // log table isn't ORMed as it doesn't seem worth it
    public function log($action, $symbol, $shares, $price) {
        $stmt = $this->conn->prepare('INSERT INTO log (datetime, portfolio_id, action, symbol, shares, price) VALUES (?,?,?,?,?,?);');
        $date = date('Y-m-d H:i:s');
        $stmt->bindParam(1, $date);
        $stmt->bindParam(2, $this->id);
        $stmt->bindParam(3, $action);
        $stmt->bindParam(4, $symbol);
        $stmt->bindParam(5, $shares);
        $stmt->bindParam(6, $price);
        $stmt->execute();
    }

    // gets a pretty string array of log entries
    public function getLogs() {
        $stmt = $this->conn->prepare('SELECT * FROM log WHERE portfolio_id=? ORDER BY datetime DESC;');
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $logStrings = array();
        foreach ($results as $result) {
            $action = $result['action'];
            $symbol = $result['symbol'];
            $shares = $result['shares'];
            $datetime = $result['datetime'];
            $price = money_format('$%n', $result['price']);
            switch($action) {
                case 'CREATED':
                    $message = 'Portfolio created.';
                    break;
                case 'BUY':
                    $message = "Bought $shares shares of $symbol at $price.";
                    break;
                case 'SELL':
                    $message = "Sold $shares shares of $symbol at $price.";
                    break;
                case 'DEPOSIT':
                    $message = "Deposited $price.";
                    break;
                case 'WITHDRAW':
                    $message = "Withdrew $price.";
                    break;
                default:
                    $message = "Invalid log entry";
                    break;
            }
            array_push($logStrings, $datetime . ': ' . $message);
        }
        return $logStrings;
    }

    // loads the portfolio info for a pid 
    public function load($pid) {
        $stmt = $this->conn->prepare('SELECT * FROM portfolio WHERE id=?;');
        $stmt->bindParam(1, $pid);
        $stmt->execute();
        $results = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->id = $results['id'];
        $this->name = $results['name'];
        $this->cash = $results['cash'];
        $this->email = $results['email'];
        $stmt = $this->conn->prepare('SELECT * FROM stock WHERE portfolio_id=?;');
        $stmt->bindParam(1, $pid);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($results) {
            $this->stocks = $results;
        } else {
            $this->stocks = array();
        }
    }

    // save the portfolio to the DB
    public function save() {
        // save the general info
        $stmt = $this->conn->prepare('UPDATE portfolio SET name=?, cash=? WHERE id=?;');
        $stmt->bindParam(1, $this->name);
        $stmt->bindParam(2, $this->cash);
        $stmt->bindParam(3, $this->id);
        $stmt->execute();

        // save the stock info
        // drop all stocks 
        $stmt = $this->conn->prepare('DELETE FROM stock WHERE portfolio_id=?;');
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        foreach ($this->stocks as $stock) {
            // save the current stocks
            $stmt = $this->conn->prepare('INSERT INTO stock (portfolio_id, symbol, shares) VALUES (?,?,?);');
            $stmt->bindParam(1, $this->id);
            $stmt->bindParam(2, $stock['symbol']);
            $stmt->bindParam(3, $stock['shares']);
            $stmt->execute();
        }
    }

    // returns the id
    public function getId() {
        return $this->id;
    }

    // returns the name
    public function getName() {
        return $this->name;
    }

    // returns the stocks for this portfolio
    public function getStocks() {
        return $this->stocks;
    }

    // returns the amount of shares for a specific stock
    public function getShares($symbol) {
        foreach ($this->stocks as $stock) {
            if ($stock['symbol'] == $symbol) {
                return $stock['shares'];
            }
        }
        return 0;
    }        

    // returns the cash in this portfolio
    public function balance() {
        return $this->cash;
    }

    // deposit money in this portfolio
    public function deposit($amount) {
        $this->log('DEPOSIT', '', 0, $amount);
        $this->cash += $amount;
    } 
       
    // withdraws money from this portfolio
    public function withdraw($amount) {
        $this->log('WITHDRAW', '', 0, $amount);
        $this->cash -= $amount;
    }        

    // prints a string representation of the cash in this portfolio
    public function printBalance() {
        echo money_format("$%n", $this->cash);
    }

    // returns a JSON list of just stock symbol names in this portfolio
    public function jsonStockSymbols() {
        $symbols = array();
        foreach ($this->stocks as $stock) {
            array_push($symbols, $stock['symbol']);
        }
        return json_encode($symbols);
    }

    // determines whether a portfolio is empty and returns a boolean
    public function isEmpty() {
        if ($this->cash == 0.00 AND empty($this->stocks)) {
            return true;
        } else {
            return false;
        }
    }

    // determines whether an email address owns a portfolio
    public function isOwner($email) {
        if ($this->email == $email) {
            return true;
        } else {
            return false;
        }
    }

    // deletes this portfolio from the db
    public function delete() {
        $stmt = $this->conn->prepare('DELETE FROM portfolio WHERE id=?;');
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
    }

    // returns true if this is a user's only portfolio
    public function isOnly() {
        $stmt = $this->conn->prepare('SELECT id FROM portfolio WHERE id<>? AND email=?;');
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $this->email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ! isset($result['id']); 
    }

    // adds a stock to the stock array and subtracts the cost from cash
    public function buyStock($symbol, $shares, $price) {
        $this->log('BUY', $symbol, $shares, $price);

        // pay for the stock
        $this->cash -= $price * $shares;
     
        // if they already have some of the stock, add to it 
        $size = count($this->stocks); 
        for ($i = 0; $i < $size; $i++) {
            if ($this->stocks[$i]['symbol'] == $symbol) {
                $this->stocks[$i]['shares'] += $shares;
                return;
            }
        }

        // otherwise add a new element to the stocks array
        $stock = array();
        $stock['portfolio_id'] = $this->id;
        $stock['symbol'] = $symbol;
        $stock['shares'] = $shares;
        array_push($this->stocks, $stock);
        sort($this->stocks);
    }

    // removes a stock from the stock array and adds the sale value to cash
    public function sellStock($symbol, $shares, $price) {
        $this->log('SELL', $symbol, $shares, $price);

        // Add the cash back into the portfolio
        $this->cash += $shares * $price;

        // Find the stock and adjust the shares
        $size = count($this->stocks); 
        for ($i = 0; $i < $size; $i++) {
            if ($this->stocks[$i]['symbol'] == $symbol) {
                $this->stocks[$i]['shares'] -= $shares;
                // if they don't have any shares left, pull it from the array
                if ($this->stocks[$i]['shares'] <= 0) {
                    array_splice($this->stocks, $i, 1);
                }
                return;
            }
        }
    }

    // calculates the maximum amount that can be deposited into this portfolio
    public function maxDeposit($client) {
        if ($this->isEmpty()) {
            return 5000.00;
        } else {
            $max = round($this->value($client)*0.10 - $this->cash, 2);
            if ($max < 0) {
                return 0.00;
            } else {
                return $max;
            }
        }
    }

    // returns an associative array with the portfolio value for each exchange
    public function valueByGroup($client) {
        $totals = array();
        $totals['nifty50'] = 0.00;
        $totals['dow30'] = 0.00;
        foreach ($this->stocks as $stock) {
            $value = $client->getQuoteUSD($stock['symbol']);
            $amount = $value * $stock['shares'];
            if (substr($stock['symbol'], -3) === '.NS') {
                $totals['nifty50'] += $amount;
            } else {
                $totals['dow30'] += $amount;
            }
        }
        return $totals;
    }

    // returns the total value of the portfolio
    public function value($client) {
        $totals = $this->valueByGroup($client);
        return $totals['nifty50'] + $totals['dow30'];
    }
}

?>
