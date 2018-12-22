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
                        $html = "";
                        $sql = "SELECT * FROM `metric-log` WHERE systemID = " . $row['id'] . " AND metricID = '" . $row2['name'] . "' ORDER BY timestamp DESC LIMIT 1";
                        $result3 = $conn->query($sql);
                        if ($result3->num_rows > 0) {
                            $row3 = $result3->fetch_assoc();
                            if ($row3['status'] == 1) {
                                $html .= '<span class="green-dot" title="Good"></span>';
                            } else {
                                $html .= '<span class="red-dot" title="Failed"></span>';
                            }
                        } else {
                            $html .= '<span class="yellow-dot" title="Insufficient data"></span>';
                        }
                        $html .= $row['name'] . " : " . $row2['name'];
                        array_push($items, $html);
                    }
                }
            }
        }

        $sql = "SELECT * FROM `cron` WHERE userID = '$id'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $html = "";
                $sql = "SELECT * FROM `cron-log` WHERE cronID = " . $row['id'] . " ORDER BY timestamp DESC LIMIT 2";
                $result3 = $conn->query($sql);
                if ($result3->num_rows != 2) {
                    if ($result3->num_rows == 0) {
                        $html .= '<span class="red-dot" title="No data"></span>';
                    } else {
                        $html .= '<span class="yellow-dot" title="Insufficient data"></span>';
                    }
                } else {
                    $recent = $result3->fetch_assoc();
                    $older = $result3->fetch_assoc();
                    $diff = (strtotime($recent['timestamp']) - strtotime($older['timestamp'])) / 60 / 60;
                    if ($diff <= $row['frequency']) {
                        $html .= '<span class="green-dot" title="Good"></span>';
                    } else {
                        $html .= '<span class="red-dot" title="Failed"></span>';
                    }
                }
                $html .= $row['name'];
                array_push($items, $html);
            }
        }
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
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
