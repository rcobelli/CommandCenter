<?php

include_once("init.php");

if (empty($_SESSION['id'])) {
    header("Location: index.php");
    die();
}

if ($_POST['action'] == MODE_NEW) {
    $name = steralizeString($_POST['name']);
    $url = steralizeString($_POST['url']);
    $canaryURL = steralizeString($_POST['canary']);
    $username = steralizeString($_POST['username']);
    $password = steralizeString($_POST['password']);

    if (empty($url) || empty($username) || empty($password) || empty($name)) {
        ?>
        <div class="alert alert-danger" role="alert">
            All Fields Are Required
        </div>
        <?php
    } else {
        $sql = "INSERT INTO `systems` (name, url, username, password, userID, canaryURL) VALUES ('$name', '$url', '$username', '$password', '" . $_SESSION['id'] . "', '$canaryURL')";
        if ($conn->query($sql) === false) {
            ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $conn->error; ?>
            </div>
            <?php
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
                $sql = "DELETE FROM `systems` WHERE id = $id";
                $conn->query($sql); ?>
                <div class="alert alert-danger" role="alert">
                    System is unreachable. <?php echo curl_error($ch); ?>
                </div>
                <?php
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

                if ($canaryURL != null) {
                    $sql = "INSERT INTO metrics (systemID, name) VALUES ($id, 'Canary')";
                    if ($conn->query($sql) === false) {
                        exit($conn->error);
                    }
                }

                header("Location: index.php?action=success&type=metric");
                die();
            }
        }
    }
} elseif ($_POST['action'] == MODE_EDIT) {
    $name = steralizeString($_POST['name']);
    $url = steralizeString($_POST['url']);
    $id = steralizeString($_POST['id']);
    $canaryURL = steralizeString($_POST['canary']);
    $username = steralizeString($_POST['username']);
    $password = steralizeString($_POST['password']);

    if (empty($url) || empty($username) || empty($password) || empty($name) || empty($id)) {
        ?>
        <div class="alert alert-danger" role="alert">
            All Fields Are Required
        </div>
        <?php
    } else {
        $sql = "UPDATE `systems` SET name = '$name', url = '$url', username = '$username', password = '$password', canaryURL = '$canaryURL' WHERE id = $id";
        if ($conn->query($sql) === false) {
            ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $conn->error; ?>
            </div>
            <?php
        } else {
            $sql = "DELETE FROM `metrics` WHERE systemID = $id";
            $conn->query($sql);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . ":2812/_status?format=xml");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $output = curl_exec($ch);
            $info = curl_getinfo($ch);

            if (curl_error($ch)) {
                ?>
                <div class="alert alert-danger" role="alert">
                    System is unreachable. <?php echo curl_error($ch); ?>
                </div>
                <?php
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

                if ($canaryURL != null) {
                    $sql = "INSERT INTO metrics (systemID, name) VALUES ($id, 'Canary')";
                    if ($conn->query($sql) === false) {
                        exit($conn->error);
                    }
                }


                header("Location: dashboard.php?action=success&type=metric");
                die();
            }
        }
    }
} elseif ($_GET['action'] == DELETE) {
    $id = steralizeString($_GET['id']);

    if (!empty($id)) {
        $sql = "DELETE FROM `metrics` WHERE systemID = $id";
        $conn->query($sql);

        $sql = "DELETE FROM `systems` WHERE id = $id";
        $conn->query($sql);

        header("Location: index.php?action=success&type=delete");
        die();
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
<html lang="en-US">
<head>
    <title>Command Center | Metric</title>
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
            <?php
            if ($mode == MODE_EDIT):
                ?>
                <input type="hidden" name="action" value="<?php echo MODE_EDIT; ?>">
                <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
                <?php
            else:
                ?>
                <input type="hidden" name="action" value="<?php echo MODE_NEW; ?>">
                <?php
            endif;
            ?>
            <div class="form-group">
                <label>Name: <input type="text" name="name" required value="<?php echo $row['name']; ?>" class="form-control" placeholder="Website"></label>
            </div>
            <div class="form-group">
                <label>URL: <input type="text" name="url" required value="<?php echo $row['url']; ?>" class="form-control" placeholder="http://google.com"></label>
            </div>
            <div class="form-group">
                <label>Canary URL: <input type="text" name="canary" value="<?php echo $row['canary']; ?>" class="form-control" placeholder="http://google.com/canary"></label>
            </div>
            <div class="form-group">
                <label>Username: <input type="text" name="username" required value="<?php echo $row['username']; ?>" class="form-control" placeholder="admin"></label>
            </div>
            <div class="form-group">
                <label>Password: <input type="password" name="password" required value="<?php echo $row['password']; ?>" class="form-control" placeholder="1234"></label>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <?php
        if ($mode == MODE_EDIT) {
            echo '<a href="?action=delete&id=' . $_GET['id'] . '"><button type="submit" class="btn btn-danger">Delete</button></a>';
            echo '<hr/><h3>Metrics</h3>';
            $sql = "SELECT * FROM `metrics` WHERE systemID = $id";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                echo '<ul>';
                while ($row = $result->fetch_assoc()) {
                    echo '<li>' . $row['name'] . '</li>';
                }
                echo '</ul>';
            } else {
                echo '<i>No Metrics Found</i>';
            }
        }
        ?>
