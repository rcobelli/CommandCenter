<?php

include_once("init.php");

if (empty($_GET['user'])) {
    http_response_code(400);
    die();
}

$id = steralizeString($_GET['user']);

?>
<html lang="en-US">
<head>
    <title>Command Center | System Status</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon/favicon-16x16.png">
    <link rel="manifest" href="../favicon/site.webmanifest">
    <link rel="mask-icon" href="../favicon/safari-pinned-tab.svg" color="#24273a">
    <link rel="shortcut icon" href="../favicon/favicon.ico">
    <meta name="msapplication-TileColor" content="#24273a">
    <meta name="msapplication-config" content="../favicon/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
    <meta http-equiv="refresh" content="180" >
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
    html, body {
        overflow-x: hidden;
    }
    h2 {
        text-align: center;
    }
    .red-dot {
        height: 20px;
        width: 20px;
        margin-right: 10px;
        background-color: #E0322E;
        border-radius: 50%;
        display: inline-block;
        float: left;
    }
    .green-dot {
        height: 20px;
        width: 20px;
        margin-right: 10px;
        background-color: #34c749;
        border-radius: 50%;
        display: inline-block;
        float: left;
    }
    .yellow-dot {
        height: 20px;
        width: 20px;
        margin-right: 10px;
        background-color: #D0D11D;
        border-radius: 50%;
        display: inline-block;
        float: left;
    }
    table {
        table-layout: fixed;
        word-wrap: break-word;
    }
    td {
        width: 33%;
        padding: .5rem !important;
        border-top: 1px solid #d3d3d3;
        border-bottom: 1px solid #d3d3d3;
        vertical-align: top;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    td:not(:last-child) {
        border-right: 1px solid #d3d3d3;
    }
    @media screen and (max-width: 480px) {
        h1 {
            font-size: 20px;
        }
    }

    </style>
</head>
<body>

    <div class="container">
        <h1 class="text-center my-4">System Status</h1>

        <?php

        $errorOnly = $_GET['errorOnly'] == 'true';
        $output = false;

        $items = array();

        $sql = "SELECT * FROM `systems` WHERE userID = '$id' ORDER BY name";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

                if (!is_null($row['expDate'])) {
                    // Don't check the expDate if it's null (no ssl cert to check)
                    if (strtotime($row['expDate']) <= time()) {
                        $output = true;
                        $html = '<span class="red-dot" title="Expired"></span>';
                        $html .= $row['name'] . " : SSL Cert";
                        array_push($items, $html);
                    } else if (strtotime($row['expDate']) <= strtotime('+7 days')) {
                        $output = true;
                        $html = '<span class="yellow-dot" title="Expiring Soon - ' . $row['expDate'] . '"></span>';
                        $html .= $row['name'] . " : SSL Cert";
                        array_push($items, $html);
                    } else {
                        if (!$errorOnly) {
                            $html = '<span class="green-dot" title="Expires ' . $row['expDate'] . '"></span>';
                            $html .= $row['name'] . " : SSL Cert";
                            array_push($items, $html);
                        }
                    }
                }


                $sql = "SELECT name FROM `metrics` WHERE systemID = " . $row['id'];
                $result2 = $conn->query($sql);
                if ($result2->num_rows > 0) {
                    while ($row2 = $result2->fetch_assoc()) {
                        $html = "";
                        $sql = "SELECT * FROM `metric-log` WHERE systemID = " . $row['id'] . " AND metricID = '" . $row2['name'] . "' ORDER BY timestamp DESC LIMIT 1";
                        $result3 = $conn->query($sql);
                        if ($result3->num_rows > 0) {
                            $row3 = $result3->fetch_assoc();
                            if (strtotime($row3['timestamp']) <= strtotime('-6 hours')) {
                                $output = true;
                                $html .= '<span class="yellow-dot" title="Old data"></span>';
                            } elseif ($row3['status'] == 1) {
                                if ($errorOnly) {
                                    continue;
                                }
                                $html .= '<span class="green-dot" title="Good"></span>';
                            } elseif ($row3['status'] == 0) {
                                $output = true;
                                $html .= '<span class="red-dot" title="Failed"></span>';
                            } elseif ($row3['status'] == 2) {
                                $output = true;
                                $html .= '<span class="yellow-dot" title="Monitoring Down"></span>';
                            } else {
                                $output = true;
                                $html .= '<span class="yellow-dot" title="Other"></span>';
                            }
                        } else {
                            $output = true;
                            $html .= '<span class="yellow-dot" title="Insufficient data"></span>';
                        }
                        $html .= $row['name'] . " : " . $row2['name'];
                        array_push($items, $html);
                    }
                }
            }
        }

        $sql = "SELECT * FROM `cron` WHERE userID = '$id' ORDER BY name";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $html = "";
                $sql = "SELECT * FROM `cron-log` WHERE cronID = " . $row['id'] . " ORDER BY timestamp DESC LIMIT 1";
                $result3 = $conn->query($sql);
                if ($result3->num_rows == 0) {
                    $output = true;
                    $html .= '<span class="yellow-dot" title="No data"></span>';
                } else {
                    $recent = $result3->fetch_assoc();
                    $diff = (time() - strtotime($recent['timestamp'])) / 60 / 60;
                    if ($diff <= $row['frequency']) {
                        if ($errorOnly) {
                            continue;
                        }
                        $html .= '<span class="green-dot" title="Good"></span>';
                    } else {
                        $output = true;
                        $html .= '<span class="red-dot" title="Bad"></span>';
                    }
                }
                $html .= $row['name'];
                array_push($items, $html);
            }
        }


        if ($errorOnly && !$output) {
            echo '<h2>👍</h2>';
        } else {
            ?>
            <table class="table">
                <tbody>
                    <?php
                    $count = 0;
            while ($count < count($items)) {
                if ($count % 3 == 0) {
                    echo '<tr>';
                }
                echo '<td>';
                echo $items[$count];
                echo '</td>';
                if ($count % 3 == 2) {
                    echo '</tr>';
                }
                $count++;
            }
            while ($count % 3 != 0) {
                echo '<td>';
                echo "&nbsp;";
                echo '</td>';
                $count++;
            } ?>
                </tbody>
            </table>
            <?php
        }
        ?>
    </div>
</body>
</html>
