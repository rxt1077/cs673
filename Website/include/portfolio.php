<?php

class Portfolio {
    private $id;
    private $name;
    private $stocks;
    private $conn;

    //takes the database connection
    function __construct($conn) {
        $this->conn = $conn;
    }

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

    public function getId() {
        return $this->id;
    }
}

?>
