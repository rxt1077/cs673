<!DOCTYPE html>
<html>
<head>
    <?php require 'include/head.php'; ?>
    <title>Sign In</title>
</head>
<body>
    <div class='mdl-layout mdl-js-layout'>
        <main class="mdl-layout__content">
            <div class="page-content">
                <div class="signin-container">
                    <div class="signin-card mdl-card mdl-shadow--2dp">
                        <div class="mdl-card__title">
                            <h2 class="mdl-card__title-text">Sign In</h2>
                        </div>
                        <div class="mdl-card__supporting-text">
                            <div>Please enter your email address, password, and click sign in.</div>
                            <div>If you do not have an account, <a href="signup.php">click here to sign up</a>.</div>
                            <form action="login.php" method="post">
                                <div class="signin-credentials">
                                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                        <input class="mdl-textfield__input" type="email" id="email">
                                        <label class="mdl-textfield__label" for="email">Email</label>
                                        <span class="mdl-textfield__error">Please enter a valid email address</span>
                                    </div>
                                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                        <input class="mdl-textfield__input" type="password" id="password">
                                        <label class="mdl-textfield__label" for="password">Password</label>
                                    </div>
                                </div>
                                <div class="signin-actions mdl-card__actions">
                                    <button class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--colored" type="submit">Next</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
