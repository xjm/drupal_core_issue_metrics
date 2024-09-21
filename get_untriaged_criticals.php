<?php

require_once __DIR__ . '/vendor/autoload.php';

use Drupal\core_metrics\IssueQuery;
use Drupal\core_metrics\IssueQueryRunner;

$query = new IssueQuery();
$query->findUntriagedCriticalBugs();
$runner = new IssueQueryRunner($query);
$results = $runner->getResults();

$now = new DateTime();

$csv = "nid, title, component, age, last_update\n";

foreach ($results as $row) {
  $created = new DateTime('@' . $row['created']);
  $changed = new DateTime('@' . $row['changed']);
  $csvRow = [
    $row['nid'],
    $row['title'],
    $row['component'],
    $created->diff($now)->days,
    $changed->diff($now)->days,
  ];

  $csv .= '"' . implode('","', $csvRow) . '"' . "\n";
}

print $csv;
