<?php

include_once("init.php");

if (empty($_SESSION['id'])) {
    header("Location: index.php");
    die();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $url = steralizeString($_POST['url']);
    $username = steralizeString($_POST['username']);
    $password = steralizeString($_POST['password']);

    if (empty($url) || empty($username) || empty($password)) {
        echo "All fields required";
    } else {
        $sql = "INSERT INTO `systems` (url, username, password, userID) VALUES ('$url', '$username', '$password', " . $_SESSION['id'] . ")";
        if ($conn->query($sql) === false) {
            echo $conn->error;
        } else {
            $id = $conn->insert_id;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . ":2812/_status?format=xml");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $output = curl_exec($ch);
            $info = curl_getinfo($ch);

            if (curl_error($ch)) {
                header("Location: dashboard.php?action=error&type=metric&reason=system_unreachable");
                die();
            } else {
                $xml = simplexml_load_string($output);
                $json = json_encode($xml);
                $raw = json_decode($json, true);
                $temp = array();

                foreach ($raw['service'] as $service) {
                    $sql = "INSERT INTO metrics (systemID, name) VALUES ($id, '" . $service['name'] . "')";
                    if ($conn->query($sql) === false) {
                        exit($conn->error);
                    }
                }

                header("Location: dashboard.php?action=success&type=metric");
                die();
            }
        }
    }
}

if (empty($_GET['id'])) {
    $mode = MODE_NEW;
} else {
    $mode = MODE_EDIT;
    $id = steralizeString($_GET['id']);
    $sql = "SELECT * FROM `systems` WHERE id = $id";
    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
    } else {
        header("Location: dashboard.php?action=error&type=metric&reason=invalid_id");
        die();
    }
}

?>
<html>
<head>
    <title>Command Center | Metric</title>
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
            <li class="breadcrumb-item active" aria-current="page">System Monitoring</li>
        </ol>
    </nav>

    <div class="container">

        <?php
        if ($mode == MODE_EDIT):
            ?>
            <h1 class="text-center my-4">Edit System Monitoring</h1>
            <?php
        else:
            ?>
            <h1 class="text-center my-4">Create New System Monitoring</h1>
            <?php
        endif;
        ?>

        <form method="post">
            <div class="form-group">
                <label>URL: </label><input type="text" name="url" required value="<?php echo $row['url']; ?>" class="form-control" placeholder="http://google.com">
            </div>
            <div class="form-group">
                <label>Username: </label><input type="text" name="username" required value="<?php echo $row['username']; ?>" class="form-control" placeholder="admin">
            </div>
            <div class="form-group">
                <label>Password: </label><input type="password" name="password" required value="<?php echo $row['password']; ?>" class="form-control" placeholder="1234">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <?php
        if ($mode == MODE_EDIT) {
            echo '<h3>Metrics</h3>';
            $sql = "SELECT * FROM `metrics` WHERE systemID = $id";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                echo '<ul>';
                while ($row = $result->fetch_assoc()) {
                    echo '<li>' . $row['name'] . '</li>';
                }
                echo '</ul>';
            } else {
                echo '<h5>No Metrics Found</h5>';
            }
        }
        ?>
