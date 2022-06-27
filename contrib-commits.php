<?php

require_once __DIR__ . '/vendor/autoload.php';

use Drupal\core_metrics\GitLogParser;
use Drupal\core_metrics\IssueQuery;
use Drupal\core_metrics\QueryRunner;
use Drupal\core_metrics\MagicIntMetadata;

$magic = new MagicIntMetadata();
$startDate = new \DateTime($magic::$branchDates['9.4.x']);
$commits = [];

foreach ($magic::$contribBranches as $project => $branch) {
  $parser = new GitLogParser($branch, $project, $startDate);
  $projectCommits[$project] = $parser->getParsedCommits();
}

print '"Project","Commit ID","Message"' . "\n";
foreach ($projectCommits as $project => $commits) {
  foreach ($commits as $id => $message) {
    $issueRows[] = '"' . $project . '","' . $id . '","'
      . str_replace('"','',$message)
      . '"';
  }
}

print implode("\n", $issueRows);
