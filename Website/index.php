<!DOCTYPE HTML>

<?php include 'include/check_session.php'; ?>

<html>
<head>
    <?php
        $title = "Take Stock";
        include 'include/head.php';
    ?>
</head>
<body>
    <!-- Always shows a header, even in smaller screens. -->
    <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
      <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
          <!-- Title -->
          <span class="mdl-layout-title">
              <?php echo $title; ?>
          </span>
          <!-- Add spacer, to align navigation to the right -->
          <div class="mdl-layout-spacer"></div>
          <!-- Navigation. We hide it in small screens. -->
          <nav class="mdl-navigation mdl-layout--large-screen-only">
            <span class="mdl-navigation__link">
                <?php echo $email; ?> <a href="end_session.php">(Sign Out)</a>
            </span>
          </nav>
        </div>
      </header>
      <div class="mdl-layout__drawer">
        <span class="mdl-layout-title">Portfolios</span>
        <nav class="mdl-navigation">
          <a class="mdl-navigation__link" href="">Portfolio 1</a>
          <a class="mdl-navigation__link" href="">Portfolio 2</a>
          <a class="mdl-navigation__link" href="">Portfolio 3</a>
          <a class="mdl-navigation__link" href="">Add a New Portfolio</a>
        </nav>
      </div>
      <main class="mdl-layout__content">
        <div class="page-content">
            <!-- Your content goes here -->
        </div>
      </main>
    </div>
</body>
</html>
