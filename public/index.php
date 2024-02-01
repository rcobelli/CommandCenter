<?php

include '../init.php';

$samlHelper->processSamlInput();

if (!$samlHelper->isLoggedIn()) {
    header("Location: ?sso");
    die();
}

$config['type'] = Rybel\backbone\LogStream::console;

// Boilerplate
$page = new Rybel\backbone\page();
$page->addHeader("../includes/header.php");
$page->addFooter("../includes/footer.php");

$cronHelper = new CronHelper($config);
$systemHelper = new SystemHelper($config);

// Start rendering the content
ob_start();

?>
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
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Home</li>
    </ol>
</nav>
<div class="container">
    <?php

    if ($_GET['action'] == 'success') {
        if ($_GET['type'] == 'cron') {
            ?>
            <div class="jumbotron">
                <h1 class="display-4">Integration Instructions</h1>
                <p class="lead">In <code>crontab</code>, add <code> && curl <?php echo getUrl(); ?>cron?id=<?php echo $_GET['code']; ?></code></p>
                <hr class="my-4">
                <p>Before: <code>0 1 * * * /path/to/myscript.sh</code></p>
                <p>After: <code>0 1 * * * /path/to/myscript.sh && curl <?php echo getUrl(); ?>cron?id=<?php echo $_GET['code']; ?></code></p>
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
            $crons = $cronHelper->getAllCrons();
            if ($crons !== false) {
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
                        foreach ($crons as $row) {
                            echo '<tr class="clickable-row" data-href="manage_cron.php?id=' . $row['id'] . '"><td>' . $row['name'] . "</td><td class='text-center'>" . $row['frequency'] . "</td></tr>";
                        } ?>
                    </tbody>
                </table>
                <?php
            } else {
                echo '<p><i>You are not monitoring any cron jobs</i></p>';
            }
            // Create new cron job
            echo '<p><a href="manage_cron.php"><button type="button" class="btn btn-primary">Monitor New Cron Job</button></a></p>';
            ?>
        </div>
        <div class="col-sm">
            <h3>Systems</h3>
            <?php

            // List current system metrics
            $systems = $systemHelper->getAllSystems();
            if ($systems !== false) {
                ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>System Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($systems as $row) {
                            echo '<tr class="clickable-row" data-href="manage_metric.php?id=' . $row['id'] . '"><td>' . $row['name'] . " <small class='text-muted float-right'>" . $row['url'] . "</small></td></tr>";
                        } ?>
                    </tbody>
                </table>
                <?php
            } else {
                echo '<p><i>You are not monitoring any Monit systems</i></p>';
            }
            // Create new system metric
            echo '<p><a href="manage_metric.php"><button type="button" class="btn btn-primary">Monitor New System</button></a></p>';
            ?>
        </div>
    </div>
    <div class="alert alert-info" role="alert">
        Your display dashboard URL is <a href="<?php echo getUrl(); ?>display?api_token=13fbd79c3d390e5d6585a21e11ff5ec1970cff0c&errorOnly=false" target="_blank"><?php echo getURL(); ?>display?api_token=13fbd79c3d390e5d6585a21e11ff5ec1970cff0c&errorOnly=false</a>
    </div>

</div>

<?php
$content = ob_get_clean();
$page->render($content);
