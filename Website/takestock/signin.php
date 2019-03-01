<?php
    include 'config.php';
    include 'include/post_params.php';
    $title='Sign In';
    include 'templates/dialog_top.php';
?>

<div class="mdl-card__supporting-text">
    <!-- Row one -->
    <div class="mdl-grid">
        Please enter your email address, password, and click next.
    </div>
    <!-- Row two -->
    <form method="post">
        <div class="mdl-grid">
            <!-- Email -->
            <div class="mdl-cell
                        mdl-cell--6-col
                        mdl-textfield
                        mdl-js-textfield
                        mdl-textfield--floating-label">
                <input class="mdl-textfield__input"
                       type="email"
                       id="email"
                       name="email"
                       value="<?php printparam("email"); ?>">
                <label class="mdl-textfield__label"
                       for="email">
                    Email
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
                       value="<?php printparam("password"); ?>">
                <label class="mdl-textfield__label"
                       for="password">
                    Password
                </label>
            </div>
        </div>
        <!-- Forgot password link -->
        <a href="forgot.php">
            Forgot Password?
        </a>
        <!-- Form actions -->
        <center>
            <div class="mdl-card__actions">
                <button class="mdl-button
                               mdl-js-button
                               mdl-js-ripple-effect"
                        formaction="signup.php"
                        type="submit">
                    Create Account
                </button>
                <button class="mdl-button
                               mdl-js-button
                               mdl-js-ripple-effect
                               mdl-button--raised
                               mdl-button--colored"
                        formaction="actions/get_session.php"
                        type="submit">
                    Next
                </button>
            </div>
        </center>
    </form>
</div>

<?php include 'templates/dialog_bottom.php'; ?>
