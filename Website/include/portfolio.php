<?php

class Portfolio {
    private $conn;
    private $id;
    private $name;
    private $stocks;

    // needs the database connection
    function __construct($conn) {
        $this->conn = $conn;
    }

    // creates and saves a new portfolio
    public function create($name, $email) {
        $stmt = $this->conn->prepare('INSERT INTO portfolio (name, email) VALUES (?,?);');
        $stmt->bindParam(1, $name);
        $stmt->bindParam(2, $email);
        $stmt->execute();
        $stmt = $this->conn->prepare('SELECT LAST_INSERT_ID() AS id;');
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->id = $result['id'];
        $this->stocks = array();
    }

    // loads the portfolio info from a pid
    public function load($pid) {
        $stmt = $this->conn->prepare('SELECT * FROM portfolio WHERE id=?;');
        $stmt->bindParam(1, $pid);
        $stmt->execute();
        $results = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->id = $results['id'];
        $this->name = $results['name'];
        $stmt = $this->conn->prepare('SELECT symbol FROM stock WHERE portfolio_id=?;');
        $stmt->bindParam(1, $pid);
        $stmt->execute();
        $results = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($results) {
            $this->stocks = $results;
        } else {
            $this->stocks = array();
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

    // returns a JSON list of just stock symbol names in this portfolio
    public function jsonStockSymbols() {
        $symbols = array();
        foreach ($this->stocks as $stock) {
            array_push($symbols, $stock['symbol']);
        }
        return json_encode($symbols);
    }
}

?>
