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
$query->findIssuesFixedIn('10.0.x');
$runner = new QueryRunner($query);
$results = $runner->getResults();

$parser = new GitLogParser('10.0.x');
$commits = $parser->getParsedCommits();
$nodeIds = array_keys($commits);

$fixedIssues = [];

// Strip commits not tied to an issue.
foreach ($results as $row) {
  if (in_array($row['nid'], $nodeIds)) {
    $fixedIssues[$row['nid']] = $row;
  }
}

print '"nid","Date","Title","Type","Priority","Component"' . "\n";
foreach ($fixedIssues as $issue) {
  $issueRow = [
    $issue['nid'],
    $commits[$issue['nid']]['date'],
    str_replace('"','',$issue['title']),
    $types[$issue['category']],
    $priorities[$issue['priority']],
    $issue['component'],
  ];
  print '"' . implode('","', $issueRow) . "\"\n";
}
