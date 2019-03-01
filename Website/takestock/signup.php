<?php
    include 'config.php';
    $title = 'Sign Up';
    include 'include/post_params.php';
    include 'templates/dialog_top.php';
?>

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
                       name="first"
                       value="<?php printparam("first"); ?>">
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
                       name="last"
                       value="<?php printparam("last"); ?>">
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
                       value="<?php printparam("email"); ?>">
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
                       value="<?php printparam("password"); ?>">
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
                       name="agree"
                       <?php echo (getparam("agree")) == 'on' ? 'checked' : ''; ?>>
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
                        formaction="actions/create_user.php"
                        type="submit">
                    Next
                </button>
            </div>
        </center>
    </form>
</div>

<?php include 'templates/dialog_bottom.php'; ?>
