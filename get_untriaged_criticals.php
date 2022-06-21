<?php

require_once __DIR__ . '/vendor/autoload.php';

use Drupal\core_metrics\IssueQuery;
use Drupal\core_metrics\QueryRunner;

$query = new IssueQuery();
$query->findUntriagedCriticalBugs();
$runner = new QueryRunner($query);
$results = $runner->getResults();

$now = new DateTime();

$csv = "nid, title, component, age, last_update\n";

foreach ($results as $row) {
  $created = new DateTime('@' . $row['created']);
  $changed = new DateTime('@' . $row['changed']);
  $csv_row = [
    $row['nid'],
    $row['title'],
    $row['component'],
    $created->diff($now)->days,
    $changed->diff($now)->days,
  ];

  $csv .= '"' . implode('","', $csv_row) . '"' . "\n";
}

print $csv;
