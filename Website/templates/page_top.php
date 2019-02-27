<?php
    include 'include/check_session.php';
    include 'include/db.php';

    if (isset($_GET['pid'])) {
        $pid = $_GET['pid'];
        $stmt = $conn->prepare('SELECT id, name FROM portfolio WHERE id=? AND email=?;');
        $stmt->bindParam(1, $pid);
        $stmt->bindParam(2, $email);
    } else {
        $stmt = $conn->prepare('SELECT id, name FROM portfolio WHERE email=?;');
        $stmt->bindParam(1, $email);
    }

    $stmt->execute();
    $results = $stmt->fetch(PDO::FETCH_ASSOC);
    $pid = $results['id'];
    if (! isset($pid)) {
        die("Unable to look up portfolio");
    }
    $title = $results['name'];
    $page = basename($_SERVER['PHP_SELF']);
?>
<html>
<head>
    <?php
        include 'include/head.php';
    ?>
</head>
<body>
    <!-- Always shows a header, even in smaller screens. -->
    <div class="mdl-layout
                mdl-js-layout
                mdl-layout--fixed-header">
        <!-- Header -->
        <header class="mdl-layout__header">
            <div class="mdl-layout__header-row">
                <!-- Title -->
                <span class="mdl-layout-title">
                    <?php echo $title; ?>
                </span>
                <!-- Add spacer, to align navigation to the right -->
                <div class="mdl-layout-spacer"></div>
                <!-- Navigation. We hide it in small screens. -->
                <nav class="mdl-navigation
                            mdl-layout--large-screen-only">
                    <span class="mdl-navigation__link">
                        <?php echo $email; ?> <a href="end_session.php">(Sign Out)</a>
                    </span>
                </nav>
            </div>
            <!-- Tabs -->
            <div class="mdl-layout__tab-bar
                        mdl-js-ripple-effect">
                <a href="overview.php?pid=<?php echo $pid; ?>"
                   class="mdl-layout__tab
                          <?php echo ($page == 'overview.php') ? 'is-active' : ''; ?>">
                    Overview
                </a>
                <a href="buy.php?pid=<?php echo $pid; ?>"
                   class="mdl-layout__tab
                          <?php echo ($page == 'buy.php') ? 'is-active' : ''; ?>">
                    Buy
                </a>
                <a href="sell.php?pid=<?php echo $pid; ?>"
                   class="mdl-layout__tab
                          <?php echo ($page == 'sell.php') ? 'is-active' : ''; ?>">
                    Sell
                </a>
                <a href="order.php?pid=<?php echo $pid; ?>"
                   class="mdl-layout__tab
                          <?php echo ($page == 'order.php') ? 'is-active' : ''; ?>">
                    Order
                </a>
            </div>
        </header>

        <!-- Drawer -->
        <div class="mdl-layout__drawer">
            <span class="mdl-layout-title">
                Portfolios
            </span>
            <nav class="mdl-navigation">
                <!-- Look up and put links to all the users portfolios in the drawer -->
                <?php
                
                $stmt = $conn->prepare('SELECT name, id FROM portfolio WHERE email=?;');
                $stmt->bindParam(1, $email);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($results as $portfolio) {
                    $name = $portfolio['name'];
                    $id = $portfolio['id'];
                    echo "<a class=mdl-navigation__link href='overview.php?p=$id'>$name</a>";
                }
                
                ?>
                <!-- Form to allow user to create a new portfolio -->
                <form class="mdl-navigation__link" method="post">
                    <div class="mdl-textfield
                                mdl-js-textfield">
                        <input class="mdl-textfield__input"
                               type="text"
                               id="new_portfolio"
                               name="new_portfolio">
                        <label class="mdl-textfield__label"
                               for="new_portfolio">
                            Name...
                        </label>
                    </div>
                    <button class="mdl-button
                                   mdl-js-button
                                   mdl-button--icon
                                   mdl-js-ripple-effect
                                   mdl-button--colored"
                            formaction="actions/add_portfolio.php"
                            type="submit">
                        <i class="material-icons">add</i>
                    </button>
                </form>
            </nav>
        </div>

        <!-- Content -->
        <main class="mdl-layout__content">
            <div class="page-content">
