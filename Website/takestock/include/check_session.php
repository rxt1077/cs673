<?php
    session_start();

    if(! isset($_SESSION['email'])) { // If session is not set then redirect to Sign In
        header("Location: $basedir/signin.php");
        die("Not signed in");
    }
    $email = $_SESSION['email'];
?>
