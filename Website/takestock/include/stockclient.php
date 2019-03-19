<?php

//Basic client to interface with StockServer
class StockClient {
    private $socket;
    private $rate;

    function __construct($address, $port) {
        // connect to our backend
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (! socket_connect($this->socket, $address, $port)) {
            die("Unable to connect to StockServer");
        }

        // pull down the current exchange rate
        $contents = file_get_contents('https://api.exchangeratesapi.io/latest?base=INR&symbols=USD,GBP');
        if (! $contents) {
            die("Unable to get exchange rate");
        }
        $results = json_decode($contents, true);
        $this->rate = $results['rates']['USD'];
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
            return round(((float)$parts[1] * $this->rate), 2);
        }
    }
}

?> 
