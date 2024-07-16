<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Drupal\core_metrics\IssueListFetcher;
use Drupal\core_metrics\IssueListRequest;
use Drupal\core_metrics\MagicIntMetadata;

$magic = new MagicIntMetadata();

$branches = $magic::$activeBranches;
$types = ['bug', 'task', 'feature', 'plan'];

foreach ($types as $type) {
  $fetcher = new IssueListFetcher(new IssueListRequest($branches, $type), new Client());
  $fetcher->fetch();
}
