<?php

require_once __DIR__ . '/vendor/autoload.php';

use Drupal\core_metrics\StringQueryRunner;

$queryRunner = new StringQueryRunner('SELECT MAX(changed) FROM issue_data;');
$result = $queryRunner->getResults();
print date('d M, Y', $result[0][0]);
