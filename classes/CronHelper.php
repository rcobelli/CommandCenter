<?php

use Rybel\backbone\Helper;

class CronHelper extends Helper
{
    public function getAllCrons()
    {
        return $this->query("SELECT * FROM `cron` ORDER BY name");
    }

    public function getCron($id)
    {
        return $this->query("SELECT * FROM `cron` WHERE id = ? LIMIT 1", $id);
    }

    public function createCron($name, $frequency)
    {
        return $this->query("INSERT INTO `cron` (name, frequency) VALUES (?, ?)", $name, $frequency);
    }

    public function updateCron($id, $name, $frequency)
    {
        return $this->query("UPDATE `cron` SET name = ?, frequency = ? WHERE id = ?", $name, $frequency, $id);
    }

    public function deleteCron($id)
    {
        return $this->query("DELETE FROM `cron` WHERE id = ?", $id);
    }

    public function recordHeartbeat($id)
    {
        return $this->query("INSERT INTO `cron-log` (cronID, timestamp) VALUES (?, NOW())", $id);
    }

    public function getMostRecentTimestamp($cron_id) 
    {
        return $this->query("SELECT timestamp FROM `cron-log` WHERE cronID = ? ORDER BY timestamp DESC LIMIT 1", $cron_id);
    }

    public function getMostRecentTimestamps($cron_id) 
    {
        return $this->query("SELECT timestamp FROM `cron-log` WHERE cronID = ? ORDER BY timestamp DESC LIMIT 10", $cron_id);
    }

    public function garbageCollect() {
        return $this->query("DELETE FROM `cron-log` WHERE timestamp < DATE_SUB(NOW(), INTERVAL 120 DAY)");
    }
}