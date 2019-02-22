<!DOCTYPE html>
<html>
<head>
    <?php require 'include/head.php'; ?>
    <title>Sign Up</title>
</head>
<body>
    <div class="mdl-layout mdl-js-layout">
        <main class="mdl_layout__content">
            <div class="page-content">
                <div class="dialog-container">
                    <div class="dialog-card
                                mdl-card
                                mdl-shadow--2dp">
                        <div class="mdl-card__title">
                            <h2 class="mdl-card__title-text">
                                Sign Up
                            </h2>
                        </div>
                        <div class="mdl-card__supporting-text">
                        <?php
                            $first = $_POST['first'] ?? '';
                            $last = $_POST['last'] ?? '';
                            $email = $_POST['email'] ?? '';
                            $password = $_POST['password'] ?? '';
                            $agree = $_POST['agree'] ?? '';
                            $error = False;
                            if ($first == '') {
                                echo '<div>Invalid first name.</div>';
                                $error = True;
                            }
                            if ($last == '') {
                                echo '<div>Invalid last name.</div>';
                                $error = True;
                            }
                            if ($email == '') {
                                echo '<div>Invalid email address.</div>';
                                $error = True;
                            }
                            if ($password == '') {
                                echo '<div>Invalid password.</div>';
                                $error = True;
                            }
                            if ($agree == '') {
                                echo '<div>You must accept the license agreement to continue</div>';
                                $error = True;
                            }
                        ?>
                        </div>
                        <center>
                            <div class="mdl-card__actions">
                                <form method="post">
                                    <button class="mdl-button
                                                   mdl-js-button
                                                   mdl-js-ripple-effect
                                                   mdl-button--raised
                                                   mdl-button--colored"
                                            type="submit"
                                            formaction="<?php echo $error ? 'signup.php' : 'get_session.php' ?>">
                                        <?php echo $error ? 'Back' : 'Next' ?>
                                    </button>
                                </form>
                            </div>
                        </center>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>                                        
