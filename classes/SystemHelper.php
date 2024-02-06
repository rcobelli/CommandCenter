<?php

use Rybel\backbone\Helper;

class SystemHelper extends Helper
{
    public function getAllSystems()
    {
        return $this->query("SELECT * FROM `systems` ORDER BY name");
    }

    public function getSystem($id)
    {
        return $this->query("SELECT * FROM `systems` WHERE id = ? LIMIT 1", $id);
    }

    public function getMetricsForSystem($id)
    {
        return $this->query("SELECT * FROM `metrics` WHERE systemID = ?", $id);
    }


    public function getMostRecentTimestamp($metric_id) 
    {
        return $this->query("SELECT * FROM `metric-log` WHERE metricID = ? ORDER BY timestamp DESC LIMIT 1", $metric_id);
    }

    public function createSystem($name, $url, $username, $password, $canaryURL)
    {
        $system = $this->query("INSERT INTO `systems` (name, url, username, password, canaryURL) VALUES (?, ?, ?, ?, ?)", $name, $url, $username, $password, $canaryURL);
        if ($system !== false) {
            $id = $this->getLastInsertID();
            if ($this->loadMetricsForSystem($id, $url, $username, $password, $canaryURL)) {
                return true;
            } else {
                $this->deleteSystem($id);
            }
        }
        return false;
    }

    public function updateMetric($id, $name, $url, $username, $password, $canaryURL) {
        $system = $this->query("UPDATE `systems` SET name = ?, url = ?, username = ?, password = ?, canaryURL = ? WHERE id = ?", $name, $url, $username, $password, $canaryURL, $id);
        if ($system !== false) {
            $id = $this->deleteMetricsForSystem($id);
            if ($this->loadMetricsForSystem($id, $url, $username, $password, $canaryURL)) {
                return true;
            } else {
                $this->deleteSystem($id);
            }
        }
        return false;
        
    }

    private function loadMetricsForSystem($id, $url, $username, $password, $canaryURL) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . ":2812/_status?format=xml");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $output = curl_exec($ch);

        if (curl_error($ch)) {
            return false;
        } else {
            $xml = simplexml_load_string($output);
            $json = json_encode($xml);
            $raw = json_decode($json, true);

            foreach ($raw['service'] as $service) {
                if ($this->createMetric($id, $service['name']) === false) {
                    return false;
                }

                // Check if Apache is a service
                if ($service['name'] == "apache") {
                    // Mark that the SSL cert needs to be checked
                    if ($this->updateSslExpirationDate($id, "0000-00-00 00:00:00") === false) {
                        return false;
                    }
                }
            }

            if ($canaryURL != null) {
                if ($this->createMetric($id, 'Canary') === false) {
                    return false;
                }
            }

            return true;
        }
    }

    private function createMetric($id, $name) {
        return $this->query("INSERT INTO metrics (systemID, name) VALUES (?, ?)", $id, $name);
    }

    private function deleteMetricsForSystem($id) {
        return $this->query("DELETE FROM `metrics` WHERE systemID = ?", $id);
    }

    public function updateSslExpirationDate($id, $date) {
        return $this->query("UPDATE `systems` SET expDate = ? WHERE id = ?", $date, $id);
    }

    public function deleteSystem($id) {
        return $this->deleteMetricsForSystem($id) && $this->query("DELETE FROM `systems` WHERE id = ?", $id);
    }

    public function recordMetric($id, $name, $status) {
        return $this->query("INSERT INTO `metric-log` (systemID, metricID, timestamp, status) VALUES (?, ?, NOW(), ?)", $id, $name, $status);
    }

    public function garbageCollect() {
        return $this->query("DELETE FROM `metric-log` WHERE timestamp < DATE_SUB(NOW(), INTERVAL 7 DAY)");
    }
}