<?php

require_once __DIR__ . '/vendor/autoload.php';

use Drupal\core_metrics\GitLogParser;
use Drupal\core_metrics\IssueQuery;
use Drupal\core_metrics\QueryRunner;
use Drupal\core_metrics\MagicIntMetadata;

$metadata = new MagicIntMetadata();
$statuses = array_flip($metadata::$status);
$priorities = array_flip($metadata::$priority);
$types = array_flip($metadata::$type);

$query = new IssueQuery();
$query->findIssuesFixedIn('9.4.x');
$runner = new QueryRunner($query);
$results = $runner->getResults();

$parser = new GitLogParser('9.4.x');
$commits = array_keys($parser->getParsedCommits());

$fixed_issues = [];

foreach ($results as $row) {
  if (in_array($row['nid'], $commits)) {
    $fixed_issues[$row['nid']] = $row;
  }
}

print '"nid","Title","Type","Priority","Component"' . "\n";
foreach ($fixed_issues as $issue) {
  $issue_row = [
    $issue['nid'],
    str_replace('"','',$issue['title']),
    $types[$issue['category']],
    $priorities[$issue['priority']],
    $issue['component'],
  ];
  print '"' . implode('","', $issue_row) . "\"\n";
}
