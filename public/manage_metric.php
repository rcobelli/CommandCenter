<?php

include '../init.php';

if (!$samlHelper->isLoggedIn()) {
    header("Location: index.php");
    die();
}

$config['type'] = Rybel\backbone\LogStream::console;

$helper = new SystemHelper($config);

// Boilerplate
$page = new Rybel\backbone\page();
$page->addHeader("../includes/header.php");
$page->addFooter("../includes/footer.php");

if ($_POST['action'] == "new") {
    $name = $_POST['name'];
    $url = $_POST['url'];
    $canaryURL = $_POST['canary'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($url) || empty($username) || empty($password) || empty($name)) {
        $page->addError("All Fields Are Required");
    } else {
        if ($systemHelper->createSystem($name, $url, $username, $password, $canaryURL)) {
            header("Location: index.php?action=success&type=metric");
            die();
        } else {
            $page->addError($helper->getErrorMessage());
        }
    }
} elseif ($_POST['action'] == "edit") {
    $name = $_POST['name'];
    $url = $_POST['url'];
    $id = $_POST['id'];
    $canaryURL = $_POST['canary'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($url) || empty($username) || empty($password) || empty($name) || empty($id)) {
        $page->addError("All Fields Are Required");
    } else {
        if ($systemHelper->createSystem($name, $url, $username, $password, $canaryURL)) {
            header("Location: index.php?action=success&type=metric");
            die();
        } else {
            $page->addError($helper->getErrorMessage());
        }
    }
} elseif ($_GET['action'] == "delete") {
    if ($systemHelper->deleteSystem($_GET['id']) === false) {
        $page->addError($cronHelper->getErrorMessage());
    } else {
        header("Location: index.php?action=success&type=delete");
        die();
    }
}

if (empty($_GET['id'])) {
    $mode = "new";
} else {
    $mode = "edit";
    $row = $helper->getSystem($_GET['id']);
    if ($row === false) {
        header("Location: index.php?action=error&type=system&reason=invalid_id");
        die();
    }
}


// Start rendering the content
ob_start();

?>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">System Monitoring</li>
        </ol>
    </nav>

    <div class="container">

        <?php
        if ($mode == "edit"):
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
            if ($mode == "edit"):
                ?>
                <input type="hidden" name="action" value="<?php echo "edit"; ?>">
                <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
                <?php
            else:
                ?>
                <input type="hidden" name="action" value="<?php echo "new"; ?>">
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
                <label>Username: <input type="text" name="username" required value="<?php echo $row['username']; ?>" class="form-control" placeholder="admin" autocomplete="off"></label>
            </div>
            <div class="form-group">
                <label>Password: <input type="password" name="password" required value="<?php echo $row['password']; ?>" class="form-control" placeholder="1234" autocomplete="off"></label>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <?php
        if ($mode == "edit") {
            echo '<a href="?action=delete&id=' . $_GET['id'] . '"><button type="submit" class="btn btn-danger">Delete</button></a>';
            echo '<hr/><h3>Metrics</h3>';
            $metrics = $helper->getMetricsForSystem($_GET['id']);
            if (!empty($metrics)) {
                echo '<ul>';
                foreach ($metrics as $row) {
                    echo '<li>' . $row['name'] . '</li>';
                }
                echo '</ul>';
            } else {
                echo '<i>No Metrics Found</i>';
            }
        }

$content = ob_get_clean();
$page->render($content);
