<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Drupal\core_metrics\Fetcher;
use Drupal\core_metrics\IssueQuery;
use Drupal\core_metrics\IssueRequest;

foreach (['bug', 'task', 'feature'] as $type) {
  $fetcher = new Fetcher(new IssueRequest(IssueQuery::getFixRelevantBranches('9.4.x'), $type), new Client());
  $fetcher->fetch();
}
