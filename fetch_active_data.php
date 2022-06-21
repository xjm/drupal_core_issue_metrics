<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Drupal\core_metrics\Fetcher;
use Drupal\core_metrics\DatabaseUpdater;
use Drupal\core_metrics\IssueQuery;
use Drupal\core_metrics\IssueRequest;
use Drupal\core_metrics\MagicIntMetadata;

$magic = new MagicIntMetadata();

$branches = $magic::$activeBranches;
$types = ['bug', 'task', 'feature', 'plan'];
$data = [];

foreach ($types as $type) {
  $fetcher = new Fetcher(new IssueRequest($branches, $type), new Client());
  $fetcher->fetch();
}
