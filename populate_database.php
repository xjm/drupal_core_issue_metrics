<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Drupal\core_metrics\Fetcher\IssueListFetcher;
use Drupal\core_metrics\IssueDatabaseUpdater;
use Drupal\core_metrics\IssueQuery;
use Drupal\core_metrics\Request\IssueListRequest;
use Drupal\core_metrics\MagicIntMetadata;

$magic = new MagicIntMetadata();

$branches = $magic::$activeBranches;
$types = ['bug', 'task', 'feature', 'plan'];
$data = [];

$updater = new IssueDatabaseUpdater();
$updater->dropTables();
$updater->createTables();

foreach ($types as $type) {
  $fetcher = new IssueListFetcher(new IssueListRequest($branches, $type), new Client());
  $fetcher->fetchAllFromCache();
  $data = $fetcher->getData();

  foreach ($branches as $branch) {
    print "\nPreparing to write $type data for $branch.\n";

    foreach ($data[$branch] as $index => $datum) {
      if ($index === 'PAGER') {
        unset($data[$branch][$index]);
      }
    }

    $updater->writeData($data[$branch]);
  }

  // Give back the memory so we don't OOM.
  unset($data);
}
