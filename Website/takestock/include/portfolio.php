<?php

class Portfolio {
    private $conn;
    private $id;
    private $name;
    private $stocks;
    private $cash;
    private $email;

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
        foreach ($this->stocks as $stock) {
            // drop the previous row if we have one
            $stmt = $this->conn->prepare('DELETE FROM stock WHERE portfolio_id=? AND symbol=?;');
            $stmt->bindParam(1, $this->id);
            $stmt->bindParam(2, $stock['symbol']);
            $stmt->execute();
            // save the new row
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
    }        

    // returns the cash in this portfolio
    public function getCash() {
        return $this->cash;
    }

    // prints a string representation of the cash in this portfolio
    public function printCash() {
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
        // Add the cash back into the portfolio
        $this->cash += $shares * $price;

        // Find the stock and adjust the shares
        $size = count($this->stocks); 
        for ($i = 0; $i < $size; $i++) {
            if ($this->stocks[$i]['symbol'] == $symbol) {
                $this->stocks[$i]['shares'] -= $shares;
                // if they don't have any shares left, pull it from the array
                if ($this->stocks[$i]['shares'] <= 0) {
                    unset($this->stocks[$i]);
                }
                return;
            }
        }
    }
}

?>
