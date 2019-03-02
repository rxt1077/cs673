<?php

    include '../config.php';
    include 'include/check_session.php';
    include 'include/db.php';
    include 'include/portfolio.php';

    if (isset($_GET['pid'])) {
        $pid = $_GET['pid'];
        $portfolio = new Portfolio($conn);
        $portfolio->load($pid);
        if (! $portfolio->isOwner($email)) {
            die("$email is not the owner of portfolio id $pid");
        }
        if (! $portfolio->isEmpty($email)) {
            die("Portfolio id $pid is not empty.");
        }
        if ($portfolio->isOnly()) {
            die("$email must have at least one portfolio");
        }
        $portfolio->delete();
    }
    header("Location: $basedir/index.php");
        
?>    
