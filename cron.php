<?php

include_once("init.php");

if (empty($_SESSION['id'])) {
    header("Location: index.php");
    die();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = steralizeString($_POST['name']);
    $frequency = steralizeString($_POST['frequency']);

    if (empty($name) || empty($frequency)) {
        ?>
        <div class="alert alert-danger" role="alert">
            All Fields Are Required
        </div>
        <?php
    } else {
        $sql = "INSERT INTO `cron` (name, frequency, userID) VALUES ('$name', $frequency, " . $_SESSION['id'] . ")";
        if ($conn->query($sql) === false) {
            echo $conn->error;
        } else {
            header("Location: dashboard.php?action=success&type=cron&code=" . $conn->insert_id);
            die();
        }
    }
}

if (empty($_GET['id'])) {
    $mode = MODE_NEW;
} else {
    $mode = MODE_EDIT;
    $id = steralizeString($_GET['id']);
    $sql = "SELECT * FROM `cron` WHERE id = $id";
    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
    } else {
        header("Location: dashboard.php?action=error&type=cron&reason=invalid_id");
        die();
    }
}

?>
<html>
<head>
    <title>Command Center | Cron</title>
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
            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cron Monitoring</li>
        </ol>
    </nav>

    <div class="container">

        <?php
        if ($mode == MODE_EDIT):
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
            if ($mode == MODE_EDIT):
                ?>
                <small style="float: right;">ID: <?php echo $id; ?></small>
                <?php
            endif;
            ?>
            <div class="form-group">
                <label>Name: </label><input type="text" name="name" required value="<?php echo $row['name']; ?>" class="form-control" placeholder="Backup Script">
            </div>
            <div class="form-group">
                <label>Frequency (hours): </label><input type="number" min="0" name="frequency" required value="<?php echo $row['frequency']; ?>" class="form-control" placeholder="750">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <?php
        if ($mode == MODE_EDIT) {
            echo '<hr/><h3>Log</h3>';
            $sql = "SELECT * FROM `cron-log` WHERE cronID = $id LIMIT 15";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                echo '<table>';
                while ($row = $result->fetch_assoc()) {
                    echo '<tr><td>' . $row['timestamp'] . '</td></tr>';
                }
                echo '</table>';
            } else {
                echo '<i>No Data</i>';
            }
        }
        ?>
    </div>
</body>
</html>
