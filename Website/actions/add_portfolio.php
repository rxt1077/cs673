<?php

include 'include/check_session.php';
include 'include/post_params.php';
include 'include/db.php';
include 'include/portfolio.php';

$redirect_url = 'index.php';
$name = getparam('new_portfolio');
if ($name != '') {
    $portfolio = new Portfolio($conn);
    $portfolio->create($name, $_SESSION['email']);
    $id = $portfolio->getId();
    print_r($id);
    die();
    $redirect_url .= "?pid=$id";
}
header("Location: $redirect_url");

?>
