<?php

include '../init.php';

if (!$samlHelper->isLoggedIn()) {
    header("Location: index.php");
    die();
}

$config['type'] = Rybel\backbone\LogStream::console;

$helper = new CronHelper($config);

// Boilerplate
$page = new Rybel\backbone\page();
$page->addHeader("../includes/header.php");
$page->addFooter("../includes/footer.php");

if ($_POST['action'] == "new") {
    $name = $_POST['name'];
    $frequency = $_POST['frequency'];

    if (empty($name) || empty($frequency)) {
        $page->addError("All Fields Are Required");
    } else {
        if ($helper->createCron($name, $frequency) === false) {
            $page->addError($helper->getErrorMessage());
        } else {
            header("Location: index.php?action=success&type=cron&code=" . $helper->getLastInsertID());
            die();
        }
    }
} elseif ($_POST['action'] == "edit") {
    $name = $_POST['name'];
    $frequency = $_POST['frequency'];
    $id = $_POST['id'];

    if (empty($name) || empty($frequency) || empty($id)) {
        $page->addError("All Fields Are Required");
    } else {
        if ($helper->updateCron($id, $name, $frequency) === false) {
            $page->addError($helper->getErrorMessage());
        } else {
            header("Location: index.php?action=success&type=cron&code=" . $id);
            die();
        }
    }
} elseif ($_GET['action'] == "delete") {
    if ($helper->deleteCron($_GET['id']) === false) {
        $page->addError($helper->getErrorMessage());
    } else {
        header("Location: index.php?action=success&type=delete");
        die();
    }
}

if (empty($_GET['id'])) {
    $mode = "new";
} else {
    $mode = "edit";
    $row = $helper->getCron($_GET['id']);
    if ($row === false) {
        header("Location: index.php?action=error&type=cron&reason=invalid_id");
        die();
    }
}

// Start rendering the content
ob_start();

?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Cron Monitoring</li>
    </ol>
</nav>

<div class="container">

    <?php
    if ($mode == "edit"):
        ?>
        <h1 class="text-center my-4">Edit Cron Job Monitoring</h1>
        <?php
    else:
        ?>
        <h1 class="text-center my-4">Create New Cron Job Monitoring</h1>
        <?php
    endif;
    ?>

    <form method="post">
        <?php
        if ($mode == "edit"):
            ?>
            <small style="float: right;">ID: <?php echo $_GET['id']; ?></small>
            <input type="hidden" name="action" value="<?php echo "edit"; ?>">
            <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
            <?php
        else:
            ?>
            <input type="hidden" name="action" value="<?php echo "edit"; ?>">
            <?php
        endif;
        ?>
        <div class="form-group">
            <label>Name: <input type="text" name="name" required value="<?php echo $row['name']; ?>" class="form-control" placeholder="Backup Script"></label>
        </div>
        <div class="form-group">
            <label>Frequency (hours): <input type="number" min="0" name="frequency" required value="<?php echo $row['frequency']; ?>" class="form-control" placeholder="750"></label>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>

    <?php
    if ($mode == "edit") {
        echo '<a href="?action=delete&id=' . $_GET['id'] . '"><button type="submit" class="btn btn-danger">Delete</button></a>';
        echo '<hr/><h3>Log</h3>';
        $timestamps = $helper->getMostRecentTimestamps($_GET['id']);
        if (!empty($timestamps)) {
            echo '<table>';
            foreach ($timestamps as $timestamp) {
                echo '<tr><td>' . $timestamp . '</td></tr>';
            }
            echo '</table>';
        } else {
            echo '<i>No Data</i>';
        }
    }
    ?>
</div>

<?php

// End rendering the content
$content = ob_get_clean();
$page->render($content);
