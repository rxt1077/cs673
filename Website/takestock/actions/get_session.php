<?php
    include '../config.php';
    session_start();    
    $title='Sign In';
    include "include/post_params.php";
    include "include/db.php";
    include "templates/dialog_top.php";
?>

<!-- Messages -->
<div class="mdl-card__supporting-text">
<?php
    $email = getparam("email");
    $password = getparam("password");
    $error = False;
    if ($email == '') {
        echo '<div>Please enter an email.</div>';
        $error = True;
    }
    if ($password == '') {
        echo '<div>Please enter a password.</div>';
        $error = True;
    }

    if (! $error) {
        $upperEmail = strtoupper($email);
        $stmt = $conn->prepare("SELECT email, hash FROM user WHERE UPPER(email)=?");
        $stmt->bindParam(1, $upperEmail);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (! $row) {
            echo '<div>Invalid email address.</div>';
            $email = '';
            $error = True;
        } else if (password_verify($password, $row['hash'])) {
            // set session and redirect
            $_SESSION['email'] = $row['email'];
            echo "<div>Sign in successful! Redirecting to <a href='$basedir/index.php'>main page</a>.</div>";
            echo "<meta http-equiv='refresh' content='0;url=$basedir/index.php'>";
        } else {
            echo '<div>Invalid password.</div>';
            $password = '';
            $error = True;
        }
    } 
?>
</div>
<!--Form Actions -->
<center>
    <div class="mdl-card__actions">
        <form method="post">
            <!-- Hidden form fields to hold user input in case they need to go back -->
            <input type="hidden"
                   id="email"
                   name="email"
                   value="<?php echo $email; ?>">
            <input type="hidden"
                   id="password"
                   name="password"
                   value="<?php echo $password; ?>">
            <?php
                // Only show the back button if there is an error
                if ($error) {
                    echo "<button class='mdl-button
                                         mdl-js-button
                                         mdl-js-ripple-effect
                                         mdl-button--raised
                                         mdl-button--colored'
                                  formaction='$basedir/signin.php'
                                  type='submit'>
                              Back
                          </button>";
                }
            ?>
        </form>
    </div>
</center>

<?php include "templates/dialog_bottom.php"; ?>
