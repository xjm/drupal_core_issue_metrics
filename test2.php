<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Drupal\core_metrics\Fetcher;
use Drupal\core_metrics\DatabaseUpdater;
use Drupal\core_metrics\IssueQuery;
use Drupal\core_metrics\IssueRequest;

$branches = IssueQuery::getFixRelevantBranches('9.4.x');
$fetcher = new Fetcher(new IssueRequest($branches, 'bug'), new Client());
$fetcher->fetch();
$data = $fetcher->getData();

$updater = new DatabaseUpdater();
// $updater->dropTables();
// $updater->createTables();
foreach ($branches as $branch) {
  $updater->writeData($data[$branch]);
}
