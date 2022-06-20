<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Drupal\core_metrics\Fetcher;
use Drupal\core_metrics\DatabaseUpdater;
use Drupal\core_metrics\IssueQuery;
use Drupal\core_metrics\IssueRequest;

$fetcher = new Fetcher(new IssueRequest(['9.4.x'], 'bug'), new Client());
$fetcher->fetch();
$data = $fetcher->getData();

$updater = new DatabaseUpdater();
$updater->dropTables();
$updater->createTables();
$updater->writeData($data['9.4.x']);
