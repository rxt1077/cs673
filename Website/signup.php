<!DOCTYPE html>
<html>
<head>
    <?php require 'include/head.php'; ?>
    <title>Sign Up</title>
</head>
<body>
    <div class='mdl-layout
                mdl-js-layout'>
        <main class="mdl-layout__content">
            <div class="page-content">
                <!-- Dialog card setup -->
                <div class="dialog-container">
                    <div class="dialog-card
                                mdl-card mdl-shadow--2dp">
                        <div class="mdl-card__title">
                            <h2 class="mdl-card__title-text">
                                Sign Up
                            </h2>
                        </div>
                        <div class="mdl-card__supporting-text">
                            <div>
                                Please enter your information and click next.
                            </div>
                            <form method="post">
                                <!-- First row -->
                                <div class="mdl-grid">
                                    <!-- First Name -->
                                    <div class="mdl-cell
                                                mdl-cell--6-col
                                                mdl-textfield
                                                mdl-js-textfield
                                                mdl-textfield--floating-label">
                                        <input class="mdl-textfield__input"
                                               type="text"
                                               id="first"
                                               name="first">
                                        <label class="mdl-textfield__label"
                                               for="first">
                                            First Name
                                        </label>
                                    </div>
                                    <!-- Last Name -->
                                    <div class="mdl-cell
                                                mdl-cell--6-col
                                                mdl-textfield
                                                mdl-js-textfield
                                                mdl-textfield--floating-label">
                                        <input class="mdl-textfield__input"
                                               type="text"
                                               id="last"
                                               name="last">
                                        <label class="mdl-textfield__label"
                                               for="last">
                                            Last Name
                                        </label>
                                    </div>
                                </div>
                                <!-- Second Row -->
                                <div class="mdl-grid">
                                    <!-- Email Address -->
                                    <div class="mdl-cell
                                                mdl-cell--6-col
                                                mdl-textfield
                                                mdl-js-textfield
                                                mdl-textfield--floating-label">
                                        <input class="mdl-textfield__input"
                                               type="email"
                                               id="email"
                                               name="email"
                                               value="<?php echo $_POST['email'] ?? ''; ?>">
                                        <label class="mdl-textfield__label"
                                               for="email">
                                            Email Address
                                        </label>
                                        <span class="mdl-textfield__error">
                                            Please enter a valid email address
                                        </span>
                                    </div>
                                    <!-- Password -->
                                    <div class="mdl-cell
                                                mdl-cell--6-col
                                                mdl-textfield
                                                mdl-js-textfield
                                                mdl-textfield--floating-label">
                                        <input class="mdl-textfield__input"
                                               type="password"
                                               id="password"
                                               name="password"
                                               value="<?php echo $_POST['password'] ?? ''; ?>">
                                        <label class="mdl-textfield__label"
                                               for="password">
                                            Password
                                        </label>
                                    </div>
                                </div>
                                <!-- Third row -->
                                <div class="mdl-grid">
                                    <label class="mdl-cell
                                                  mdl-cell--12-col
                                                  mdl-checkbox
                                                  mdl-js-checkbox
                                                  mdl-js-ripple-effect"
                                           for="agree">
                                        <input type="checkbox"
                                               id="agree"
                                               class="mdl-checkbox__input"
                                               name="agree">
                                        <span class="mdl-checkbox__label">
                                            <small>
                                                I have read and agree to the <a href="terms.html">terms and conditions</a>
                                            </small>
                                        </span>
                                    </label>
                                </div>
                                <!-- Card Actions (submit buttons) -->
                                <center>
                                    <div class="mdl-card__actions">
                                        <button class="mdl-button
                                                       mdl-js-button
                                                       mdl-js-ripple-effect
                                                       mdl-button--raised
                                                       mdl-button--colored"
                                                formaction="create_user.php"
                                                type="submit">
                                            Next
                                        </button>
                                    </div>
                                </center>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
