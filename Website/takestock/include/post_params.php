<?php

//Utility functions to make it easier to deal with post params in PHP5

//gets a parameter or ""
function getparam($name) {
    if (isset($_POST[$name])) {
        return $_POST[$name];
    } else {
        return "";
    }
}

//prints a paramter or ""
function printparam($name) {
    echo getparam($name);
}

?>
