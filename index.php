<?php

include_once("init.php");

if (!empty($_SESSION['id'])) {
    header("Location: dashboard.php");
    die();
}

if (isset($_GET['code'])) {
    include_once("login.php");
} elseif (!empty($_COOKIE['commandcenter'])) {
    $data = json_decode($_COOKIE['commandcenter']);
    $_SESSION['name'] = $data->name;
    $_SESSION['email'] = $data->email;
    $_SESSION['id'] = $data->id;

    header("Location: dashboard.php");
    die();
} else {
    $client = new Google_Client();
    $client->setAuthConfig('../cc-client_secret.json');
    $client->setAccessType("offline");        // offline access
    $client->setIncludeGrantedScopes(true);
    $client->addScope("profile");
    if (isset($_GET['email'])) {
        $client->setLoginHint(urldecode($_GET['email']));
    }
    if (devEnv()) {
        $client->setRedirectUri('http://localhost/~ryan/cc/cc_backend/index.php');
    } else {
        $client->setRedirectUri('https://dev.rybel-llc.com/cc/index.php');
    }
    $auth_url = $client->createAuthUrl();
}

?>
<html lang="en">
<head>
    <title>Command Center</title>
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
    <style>
    body, html {
        background-color: #24273A;
        color: white;
        /* 43f8d4 */
    }
    </style>
</head>
<body class="text-center">
    <div class="container d-flex h-100 p-3 mx-auto flex-column">
        <header class="mb-auto">
            <div class="inner">
                <img src="assets/icon.png" height="100px" style="float: left;" class="mr-5">
                <h1 style="line-height: 100px; text-align: left">Command Center</h1>
            </div>
        </header>

        <main role="main" class="inner">
            <h1 class="cover-heading">Critical Information at your Fingertips 👌</h1>
            <p class="lead">Command Center is a utility that allows you to keep track of all your critical server metrics and cron jobs from a single page. Get started by logging in with Google</p>
            <p class="lead">
                <a href="<?php echo filter_var($auth_url, FILTER_SANITIZE_URL); ?>"><button type="button" class="btn btn-danger">Login With Google</button></a>
            </p>
        </main>
        <hr />
        <main role="main" class="inner mt-5">
            <h3 class="cover-heading">How It Works</h3>
            <p class="lead">First you have to setup <a href="https://mmonit.com/monit/" target="_blank">Monit</a> on any server that you want to monitor key metrics from. Configure Monit to track certain processes, CPU usage, disk space or any other metric they support. Link Command Center to your Monit instance to aggregate that data into your dashboard.</p>
            <p class="lead">To track cron jobs, simply add a bit of code to your <code>crontab</code> file by following the included instructions.</p>
            <p class="lead">Finally, check in often to your dashboard to see a color-coded overview of all your key business operations</p>
            <img src="assets/dashboard.png" height="200px;" class="mb-3">
        </main>
    </div>
</body>
</html>
