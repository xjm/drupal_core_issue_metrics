<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Drupal\core_metrics\IssueFetcher;
use Drupal\core_metrics\IssueRequest;
use Drupal\core_metrics\MagicIntMetadata;

$magic = new MagicIntMetadata();

$branches = $magic::$activeBranches;
$types = ['bug', 'task', 'feature', 'plan'];
$data = [];

foreach ($types as $type) {
  $fetcher = new IssueFetcher(new IssueRequest($branches, $type), new Client());
  $fetcher->fetch();
}
