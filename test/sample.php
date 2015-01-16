<?php
require_once "autoload.php";

$api = new \BillComApi\BillCom("KEY","PASS","USER");

$api->list_orgs();