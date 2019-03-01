<?php
    include '../config.php';
    include 'include/db.php';
    include 'include/post_params.php';
    include 'include/portfolio.php';
    $title = 'Create User';
    include 'templates/dialog_top.php';
?>

<div class="mdl-card__supporting-text">
<?php
    // Validate the parameters passed to us
    $first = getparam("first");
    $last = getparam("last");
    $email = getparam("email");
    $password = getparam("password");
    $agree = getparam("agree");
    $error = False;
    if ($first == '') {
        echo '<div>Please fill in your first name.</div>';
        $error = True;
    }
    if ($last == '') {
        echo '<div>Please fill in your last name.</div>';
        $error = True;
    }
    if ($email == '') {
        echo '<div>Please fill in your email address.</div>';
        $error = True;
    }
    if ($password == '') {
        echo '<div>Please create a password.</div>';
        $error = True;
    }
    if ($agree == '') {
        echo '<div>You must accept the license agreement to continue.</div>';
        $error = True;
    }

    // Check to see if the email is already in the db
    $stmt = $conn->prepare('SELECT * FROM user WHERE UPPER(email)=?');
    $upperEmail = strtoupper($email);
    $stmt->bindParam(1, $upperEmail);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row) {
        echo '<div>Email address already in use. Click <a href="../forgot.php">here</a> to reset password or click back to enter a different email.</div>';
        $error = True;
    }

    //If there isn't an error, go ahead and create the user
    if (! $error) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare('INSERT INTO user (first, last, email, hash, emailConfirmed) VALUES (?,?,?,?,FALSE);');
        $stmt->bindParam(1, $first);
        $stmt->bindParam(2, $last);
        $stmt->bindParam(3, $email);
        $stmt->bindParam(4, $hash);
        $stmt->execute();
        $portfolio = new Portfolio($conn);
        $portfolio->create("Default Portfolio", $email);
        echo '<div>User created successfully! Click next to continue.</div>';
    }
?>
</div>
<center>
    <div class="mdl-card__actions">
        <form method="post">
            <!-- Hidden form fields to hold user input incase they need to go back -->
            <input type="hidden"
                   id="first"
                   name="first"
                   value="<?php echo $first; ?>">
            <input type="hidden"
                   id="last"
                   name="last"
                   value="<?php echo $last; ?>">
            <input type="hidden"
                   id="email"
                   name="email"
                   value="<?php echo $email; ?>">
            <input type="hidden"
                   id="password"
                   name="password"
                   value="<?php echo $password; ?>">
            <input type="hidden"
                   id="agree"
                   name="agree"
                   value="<?php echo $agree; ?>">
            <!-- Either a Next or Back button -->
            <button class="mdl-button
                           mdl-js-button
                           mdl-js-ripple-effect
                           mdl-button--raised
                           mdl-button--colored"
                    type="submit"
                    formaction="<?php echo $error ? "$basedir/signup.php" : "$basedir/actions/get_session.php" ?>">
                <?php echo $error ? 'Back' : 'Next' ?>
            </button>
        </form>
    </div>
</center>

<?php include "templates/dialog_bottom.php"; ?>
