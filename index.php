<?php

include_once("init.php");

if (!empty($_SESSION['id'])) {
    header("Location: dashboard.php");
    die();
}

$client = new Google_Client();
$client->setAuthConfig('client_secret.json');
$client->setAccessType("offline");        // offline access
$client->setIncludeGrantedScopes(true);
$client->addScope("email profile");
// $client->setRedirectUri('http://localhost/~ryan/cc/cc_backend/login.php');
$client->setRedirectUri('https://cc.rybel-llc.com/login.php');
$auth_url = $client->createAuthUrl();

?>
<html>
<head>
    <title>Command Center</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
</head>
<body>

    <div class="container">
        <h1 class="text-center my-4">Command Center</h1>
        <a href="<?php echo filter_var($auth_url, FILTER_SANITIZE_URL); ?>"><button type="button" class="btn btn-danger">Login With Google</button></a>
    </div>
</body>
</html>
