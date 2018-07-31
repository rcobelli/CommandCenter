<?php

$urls = ["http://www.trac-time.com", "http://big-shanty.rybel-llc.com", "http://legacy-park.rybel-llc.com", "http://rybel-llc.com", "http://ballotline.com", "http://eaton-chiro.rybel-llc.com"];
$data = array();

foreach ($urls as $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . ":2812/_status?format=xml");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "admin:j5edrv2e7xz5");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $output = curl_exec($ch);
    $info = curl_getinfo($ch);

    if (curl_error($ch)) {
        $temp['url'] = $url;
        $temp['ip'] = $url;
        $temp['services'] = array();

        $tmp = array();
        $tmp['name'] = "System";
        $tmp['serviceStatus'] = "Offline";
        $tmp['monitoringStatus'] = "0";

        array_push($temp['services'], $tmp);


        array_push($data, $temp);
    } else {
        $xml = simplexml_load_string($output);
        $json = json_encode($xml);
        $raw = json_decode($json, true);
        $temp = array();

        $temp['url'] = $url;
        $temp['ip'] = $raw["server"]['httpd']['address'];
        $temp['services'] = array();

        foreach ($raw['service'] as $service) {
            $tmp = array();
            $tmp['name'] = $service['name'];
            $tmp['serviceStatus'] = $service['status'];
            $tmp['monitoringStatus'] = $service['monitor'];

            array_push($temp['services'], $tmp);
        }
        array_push($data, $temp);
    }
}

function showMonitoring()
{
    global $data;

    echo '<div class="item"><h1>Server Monitoring</h1><img src="../serviceIcons/monit.png" class="icon"><table>';
    foreach ($data as $server) {
        echo '<tr><td colspan=3 style="text-align: center">'.$server['url'].'</td></tr><tr><th style="width: 33%">Service Name</th><th>Monitoring Status</th><th>Service Status</th></tr>';
        foreach ($server['services'] as $service) {
            echo '<tr><td>'.$service['name'].'</td><td>';
            if ($service['monitoringStatus'] == "1") {
                echo "<span style='color: green'>No Issues</span>";
            } else {
                echo "<span style='color: red'>Not Monitoring</span>";
            }
            echo '</td><td>';
            if ($service['serviceStatus'] == "0") {
                echo "<span style='color: green'>No Issues</span>";
            } else {
                echo "<span style='color: red'>" . $service['serviceStatus'] . "</span>";
            }
            echo '</td></tr>';
        }
    }
    echo '</table></div>';
}

function showAlerts()
{
    global $data;

    $globalIssue = false;

    $globalHtml = '<div class="item"><h1>Server Issues</h1><img src="../serviceIcons/monit.png" class="icon"><table>';

    foreach ($data as $server) {
        $issue = false;
        $html = '<tr><td colspan=3 style="text-align: center">'.$server['url'].'</td></tr><tr><th style="width: 33%">Service Name</th><th>Monitoring Status</th><th>Service Status</th></tr>';
        foreach ($server['services'] as $service) {
            $temp = '<tr><td>'.$service['name'].'</td><td>';
            if ($service['monitoringStatus'] == "1") {
                $temp .= "<span style='color: green'>No Issues</span>";
            } else {
                $temp .= "<span style='color: red'>Not Monitoring</span>";
            }
            $temp .= '</td><td>';
            if ($service['serviceStatus'] == "0") {
                $temp .= "<span style='color: green'>No Issues</span>";
            } else {
                $temp .= "<span style='color: red'>" . $service['serviceStatus'] . "</span>";
            }
            $temp .= '</td></tr>';

            if ($service['monitoringStatus'] != "1" || $service['serviceStatus'] != "0") {
                $html .= $temp;
                $issue = true;
                $globalIssue = true;
            }
        }
        if ($issue) {
            $globalHtml .= $html;
        } else {
            $html = "";
        }
    }
    $globalHtml .= '</table></div>';

    if ($globalIssue) {
        echo $globalHtml;
    } else {
        echo '<div class="item"><h1>Server Alerts</h1><img src="../serviceIcons/monit.png" class="icon"><table>';
        echo '<tr><td colspan=3 style="text-align: center"><span style="color: green">None</span></td></tr>';
        echo '</table></div>';
    }
}

function checkForAlerts()
{
    global $data;

    $globalIssue = array();

    foreach ($data as $server) {
        $issue = false;
        foreach ($server['services'] as $service) {
            if ($service['monitoringStatus'] != "1") {
                $issue = true;
            }

            if ($service['serviceStatus'] != "0") {
                $issue = true;
            }

            if ($issue) {
                array_push($globalIssue, $service);
            }
        }
    }

    if (!empty($globalIssue)) {
        return json_encode($globalIssue);
    } else {
        return null;
    }
}
