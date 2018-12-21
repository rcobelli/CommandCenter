<?php

include_once("init.php");

if (empty($_GET['user'])) {
    http_response_code(400);
    die();
}

$id = steralizeString($_GET['user']);

?>
<html>
<head>
    <title>Command Center | Status Display</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
    .red-dot {
        height: 20px;
        width: 20px;
        background-color: #C73629;
        border-radius: 50%;
        display: inline-block;
        float: left;
    }
    .green-dot {
        height: 20px;
        width: 20px;
        background-color: #34c749;
        border-radius: 50%;
        display: inline-block;
        float: left;
    }

    td {
        width: 33%;
        padding: .5rem !important;
        border-top: 1px solid #d3d3d3;
        border-bottom: 1px solid #d3d3d3;
        vertical-align: top;
    }
    td:not(:last-child) {
        border-right: 1px solid #d3d3d3;
    }
    </style>
</head>
<body>

    <div class="container">
        <h1 class="text-center my-4">System Status</h1>

        <?php

        $items = array();

        $sql = "SELECT * FROM `systems` WHERE userID = '$id'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $sql = "SELECT name FROM `metrics` WHERE systemID = " . $row['id'];
                $result2 = $conn->query($sql);
                if ($result2->num_rows > 0) {
                    while ($row2 = $result2->fetch_assoc()) {
                        $row['url'] = preg_replace('#^https?://#', '', rtrim($row['url'], '/'));
                        $row['url'] = strlen($row['url']) > 20 ? substr($row['url'], 0, 20)."..." : $row['url'];


                        $html = $row['url'] . " <strong>-</strong> " . (strlen($row2['name']) > 10 ? substr($row2['name'], 0, 10)."..." : $row2['name']);
                        $sql = "SELECT * FROM `metric-log` WHERE systemID = " . $row['id'] . " AND metricID = '" . $row2['name'] . "' ORDER BY timestamp DESC LIMIT 1";
                        $result3 = $conn->query($sql);
                        if ($result3->num_rows > 0) {
                            $row3 = $result3->fetch_assoc();
                            if ($row3['status'] == 1) {
                                $html .= '<span class="green-dot"></span>';
                            } else {
                                $html .= '<span class="red-dot"></span>'; // Bad
                            }
                        } else {
                            $html .= '<span class="red-dot"></span>'; // Insufficient data
                        }
                        array_push($items, $html);
                    }
                }
            }
        }

        $sql = "SELECT * FROM `cron` WHERE userID = '$id'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $html = strlen($row['name']) > 25 ? substr($row['name'], 0, 25)."..." : $row['name'];
                $sql = "SELECT * FROM `cron-log` WHERE cronID = " . $row['id'] . " ORDER BY timestamp DESC LIMIT 2";
                $result3 = $conn->query($sql);
                if ($result3->num_rows != 2) {
                    $html .= '<span class="red-dot"></span>'; // Insufficient data
                } else {
                    $recent = $result3->fetch_assoc();
                    $older = $result3->fetch_assoc();
                    $diff = (strtotime($recent['timestamp']) - strtotime($older['timestamp'])) / 60 / 60;
                    if ($diff <= $row['frequency']) {
                        $html .= '<span class="green-dot"></span>';
                    } else {
                        $html .= '<span class="red-dot"></span>'; // Bad
                    }
                }
                array_push($items, $html);
            }
        }
        ?>

        <table class="table text-right">
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
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
