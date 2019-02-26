 <?php
$servername = "127.0.0.1";
$username = "rxt1077";
$password = "5E5YTaRZW";
$dbname = "rxt1077";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e) {
    $error = $e->getMessage();
    die("Database Error: $error");
}
?>
