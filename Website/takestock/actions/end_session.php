<?php
    include '../config.php';
    session_start();
    $_SESSION = array();
    session_destroy();
    header("Location: $basedir/signin.php");
    die();
?>
