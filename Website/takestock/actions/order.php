<?php

    include '../config.php';
    include 'include/check_session.php';
    include 'include/db.php';
    include 'include/post_params.php';
    include 'include/portfolio.php';
    include 'include/stockclient.php';

    $filename = $_FILES['file']['tmp_name'];

    // Make sure a file was uploaded
    if (! is_uploaded_file($filename)) {
        header("Location: $basedir/index.php");
    }

    // Make sure there is a portfolio and they own it
    $pid = getparam('pid');
    if ($pid == '') {
        die('No portfolio id specified');
    }
    $portfolio = new Portfolio($conn);
    $portfolio->load($pid);
    if (! $portfolio->isOwner($email)) {
        die('Invalid portfolio');
    }

    // Read and execute the orders file
    if (($handle = fopen($filename, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $num = count($data);
            for ($c=0; $c < $num; $c++) {
                echo $data[$c] . "<br />\n";
            }
        }
        fclose($handle);
    }

    header("Location: $basedir/index.php");
?>
