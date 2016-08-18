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
        if(!file_exists(realpath(SETTINGS_FILE)))
            $this->error("No settings file found; Please create " . SETTINGS_FILE . " first");

        $this->settings = parse_ini_file(SETTINGS_FILE, false);

        if(!isset($this->settings['key']))
            $this->error("No valid API key found in " . SETTINGS_FILE);
    }

    public function run()
    {
        $options  = ['status', 'engine', 'list'];
        $input    = $this->cli->radio('Get the following report:', $options);
        $response = $input->prompt();

        if($response !== NULL) {
            $this->getReport($response);
            $this->run();
        }
    }

    public function getReport($report) {
        switch($report) {
            case "status":
                break;
            case "engine":
                break;
            case "list":
                break;
        }
    }

    public function error($message)
    {
        $this->cli->red()->out($message);
        die();
    }
}