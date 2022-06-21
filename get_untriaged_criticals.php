<?php

require_once __DIR__ . '/vendor/autoload.php';

use Drupal\core_metrics\IssueQuery;
use Drupal\core_metrics\QueryRunner;

$query = new IssueQuery();
$runner = new QueryRunner($query);
$results = $runner->getResults();
print_r($results);
