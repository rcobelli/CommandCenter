<?php

include_once("init.php");

if (empty($_SESSION['id'])) {
    header("Location: index.php");
    die();
}

?>
<html>
<head>
    <title>Command Center | Dashboard</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <link rel="manifest" href="/favicon/site.webmanifest">
    <link rel="mask-icon" href="/favicon/safari-pinned-tab.svg" color="#24273a">
    <link rel="shortcut icon" href="/favicon/favicon.ico">
    <meta name="msapplication-TileColor" content="#24273a">
    <meta name="msapplication-config" content="/favicon/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
    <script>
    jQuery(document).ready(function($) {
        $(".clickable-row").click(function() {
            window.location = $(this).data("href");
        });
    });
    </script>
    <style>
    .clickable-row {
        cursor: pointer;
    }
    </style>
</head>
<body>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="logout.php">Logout</a></li>
        </ol>
    </nav>

    <div class="container">
        <h1 class="text-center my-4">Welcome, <?php echo $_SESSION['name']; ?></h1>
        <hr/>
        <?php

        if ($_GET['action'] == 'success') {
            if ($_GET['type'] == 'cron') {
                ?>
                <div class="jumbotron">
                    <h1 class="display-4">Integration Instructions</h1>
                    <p class="lead">In <code>crontab</code>, add <code> && curl https://dev.rybel-llc.com/cc/cron/?id=<?php echo $_GET['code']; ?></code></p>
                    <hr class="my-4">
                    <p>Before: <code>0 1 * * * /path/to/myscript.sh</code></p>
                    <p>After: <code>0 1 * * * /path/to/myscript.sh && curl https://dev.rybel-llc.com/cc/cron/?id=<?php echo $_GET['code']; ?></code></p>
                </div>
                <?php
            } elseif ($_GET['type'] == 'delete') {
                ?>
                <div class="alert alert-success" role="alert">
                    Successfully deleted item!
                </div>
                <?php
            } else {
                ?>
                <div class="alert alert-success" role="alert">
                    Successfully added system and metrics!
                </div>
                <?php
            }
        } elseif ($_GET['action'] == 'error') {
            if ($_GET['type'] == 'cron') {
                if ($_GET['reason'] == 'invalid_id') {
                    ?>
                    <div class="alert alert-warning" role="alert">
                        Invalid cron ID
                    </div>
                    <?php
                }
            } elseif ($_GET['type'] == 'metric') {
                if ($_GET['reason'] == 'invalid_id') {
                    ?>
                    <div class="alert alert-warning" role="alert">
                        Invalid metric ID
                    </div>
                    <?php
                }
            }
        }

        ?>

        <div class="row">
            <div class="col-sm">
                <h3>Cron Jobs</h3>
                <?php

                // List current cron jobs
                $sql = "SELECT * FROM `cron` WHERE userID = '" . $_SESSION['id'] . "' ORDER BY name";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Job Name</th>
                                <th>Frequency <small>(hours)</small></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = $result->fetch_assoc()) {
                                echo '<tr class="clickable-row" data-href="cron.php?id=' . $row['id'] . '"><td>' . $row['name'] . "</td><td class='text-center'>" . $row['frequency'] . "</td></tr>";
                            } ?>
                        </tbody>
                    </table>
                    <?php
                } else {
                    echo '<p><i>You are not monitoring any cron jobs</i></p>';
                }
                // Create new cron job
                echo '<p><a href="cron.php"><button type="button" class="btn btn-primary">Monitor New Cron Job</button></a></p>';
                ?>
            </div>
            <div class="col-sm">
                <h3>Systems</h3>
                <?php

                // List current system metrics
                $sql = "SELECT * FROM `systems` WHERE userID = '" . $_SESSION['id'] . "' ORDER BY name";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>System Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = $result->fetch_assoc()) {
                                echo '<tr class="clickable-row" data-href="metric.php?id=' . $row['id'] . '"><td>' . $row['name'] . " <small class='text-muted float-right'>" . $row['url'] . "</small></td></tr>";
                            } ?>
                        </tbody>
                    </table>
                    <?php
                } else {
                    echo '<p><i>You are not monitoring any Monit systems</i></p>';
                }
                // Create new system metric
                echo '<p><a href="metric.php"><button type="button" class="btn btn-primary">Monitor New System</button></a></p>';
                ?>
            </div>
        </div>
        <div class="alert alert-info" role="alert">
            Your display dashboard URL is <a href="<?php echo getURL(); ?>display?user=<?php echo $_SESSION['id']; ?>&errorOnly=false" target="_blank"><?php echo getURL(); ?>display?user=<?php echo $_SESSION['id']; ?>&errorOnly=false</a>
        </div>

    </div>
</body>
</html>
