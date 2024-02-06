<?php

include '../init.php';

// TODO: Actually validate this
if (empty($_GET['api_token'])) {
    http_response_code(400);
    die();
}

$config['type'] = Rybel\backbone\LogStream::console;

$cronHelper = new CronHelper($config);
$systemHelper = new SystemHelper($config);

// Boilerplate
$page = new Rybel\backbone\page();
$page->addHeader("../includes/header.php");
$page->addFooter("../includes/footer.php");

?>
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
<div class="container">
    <h1 class="text-center my-4">System Status</h1>

    <?php

    $errorOnly = $_GET['errorOnly'] == 'true';
    $output = false;

    $items = array();

    $systems = $systemHelper->getAllSystems();
    foreach ($systems as $row) {
        // Don't check the expDate if it's null (no ssl cert to check)
        if (!is_null($row['expDate'])) {
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


        $metrics = $systemHelper->getMetricsForSystem($row['id']);
        foreach ($metrics as $row2) {
            $html = "";
            $row3 = $systemHelper->getMostRecentTimestamp($row2['name']);
            if (!empty($row3)) {
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

    $crons = $cronHelper->getAllCrons();
    foreach ($crons as $row) {
        $html = "";
        $timestamp = $cronHelper->getMostRecentTimestamp($row['id']);
        if (empty($timestamp)) {
            $output = true;
            $html .= '<span class="yellow-dot" title="No data"></span>';
        } else {
            $diff = (time() - strtotime($timestamp['timestamp'])) / 60 / 60;
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

<?php
$content = ob_get_clean();
$page->render($content);