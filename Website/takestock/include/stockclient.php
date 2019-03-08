<?php

//Basic client to interface with StockServer
class StockClient {
    private $socket;

    function __construct($address, $port) {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($this->socket, $address, $port);
    }

    // gets a quote for a single symbol in USD
    public function getQuoteUSD($symbol) {
        $date = date("Y-m-d");
        $query = "$symbol,$date\n";
        socket_write($this->socket, $query, strlen($query));
        $response = socket_read($this->socket, 32);
        $parts = explode(',', $response);
        if ($parts[0] == 'USD') {
            return (float)$parts[1];
        }
        if ($parts[0] == 'INR') {
            // TODO: convert and return
            return;
        }
    }
}

?> 
