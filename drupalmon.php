<?php
/*
 Copyright (C) 2016 - Sam Hermans

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

define("SETTINGS_FILE", getenv("HOME") . "/.drupalmonrc");
define("API_URL", "https://app.lumturio.com/api/");
require_once('vendor/autoload.php');


$dm = new DrupalMon();
$dm->init();
$dm->run();


class DrupalMon
{

    private $cli;
    private $settings;

    public function __construct()
    {
        $this->cli = new League\CLImate\CLImate;
    }

    public function init()
    {
        if (!file_exists(realpath(SETTINGS_FILE)))
            $this->error("No settings file found; Please create " . SETTINGS_FILE . " first");

        $this->settings = parse_ini_file(SETTINGS_FILE, false);

        if (!isset($this->settings['token']))
            $this->error("No valid API token found in " . SETTINGS_FILE);
    }

    public function run()
    {
        $options = ['overview', 'detail'];
        $input = $this->cli->radio('Select your report:', $options);
        $response = $input->prompt();

        if ($response !== NULL) {
            $this->getReport($response);
            $this->run();
        }
    }

    public function getReport($report)
    {
        $sites = $this->getSites();
        switch ($report) {
            case "overview":
                $table = [];
                $table[] = ["Engine", "Site URL", "Updates", "Security", "Status"];
                foreach ($sites as $site) {
                    $table[] = [$site->engine_version, $site->site_url, $site->update_counter, $site->security_update_counter, $site->status_message];
                }
                break;
            case "detail":
                $sites_arr = [];
                foreach ($sites as $site) {
                    $sites_arr[] = $site->site_url;
                }
                $input = $this->cli->radio('Select your site:', $sites_arr);
                $response = $input->prompt();
                break;
        }

        $this->cli->table($table);
    }

    private function getSites()
    {
        $res = $this->callApi("site.getsites");
        return $res->items;
    }

    private function callApi($path)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, API_URL . $path);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // don't do this ! You should change this to 2
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // don't do this ! You should change this to 2
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-API-TOKEN: ' . $this->settings['token']));
        return json_decode(curl_exec($ch), false);
    }

    public function error($message)
    {
        $this->cli->red()->out($message);
        die();
    }
}