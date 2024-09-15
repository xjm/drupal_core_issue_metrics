<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Drupal\core_metrics\MagicIntMetadata;
use Drupal\core_metrics\Fetcher\FixedIssueListFetcher;
use Drupal\core_metrics\Fetcher\SingleIssueFetcher;
use Drupal\core_metrics\Fetcher\UserRecentCommentFetcher;
use Drupal\core_metrics\Request\FixedIssueListRequest;
use Drupal\core_metrics\Request\SingleIssueRequest;
use Drupal\core_metrics\Request\UserRecentCommentRequest;

// Fetch recent data for the given d.o username.
if (empty($argv[1])) {
  die("A username is required. Script usage:\nphp fetch_recent_comments.php xjm\nor\nphp fetch_recent_comments.php xjm 2024-09-16\nor\nphp fetch_recent_comments.php xjm 2024-08-01 2024-08-31\n");
}
$username = $argv[1];

// Use the starrt date provided, or default to the last week.
if (!empty($argv[2])) {
  print "Using start date of {$argv[2]}.\n";
  $startDateToUse = strtotime($argv[2]);
}
else {
  $startDateToUse = strtotime('last Monday');
}

// Use the end date provided, or default to a single week.
if (!empty($argv[3])) {
  print "Using end date of {$argv[3]}.\n";
  $timeframeEndDate = date('F d, Y', strtotime($argv[3]));
  $timeframeStartDate = date('F d, Y', $startDateToUse);
}
else {
  // Fetch data between Mondays.
  $timeframeStartDate = date('F d, Y', $startDateToUse);

  // If today is Monday, fetch the recent week's data instead.
  if (date('N') === '1') {
    $timeframeEndDate = date('F d, Y');
  }
  // Otherwise, fetch data between the previous Mondays.
  else {
    $timeframeEndDate = $timeframeStartDate;
    $timeframeStartDate = date('F d, Y', strtotime('last Monday', strtotime('1 week ago')));
  }
}

// Get the data from d.o.
$uid = MagicIntMetadata::$uids[$username];
$fetcher = new UserRecentCommentFetcher(new UserRecentCommentRequest($uid), new Client());
$fetcher->fetch();

// Load the data in the object.
$fetcher->fetchAllFromCache();
$data = $fetcher->getData();

$timeframeStartDateTimestamp = strtotime($timeframeStartDate);
$timeframeEndDateTimestamp = strtotime($timeframeEndDate);

// Collect organization and issue data from the comments.
$dataByOrg = [];
$nodeIds = [];

// array_pop() because recent comment requests are a single type.
foreach (array_pop($data) as $comment) {
  if (!empty($comment->field_attribute_contribution_to)) {
    foreach ($comment->field_attribute_contribution_to as $org) {
        if ($comment->created >= $timeframeStartDateTimestamp && ($comment->created < $timeframeEndDateTimestamp)) {
        $nid = $comment->node->id;
        $dataByOrg[$org->id][$comment->node->id] = $nid;
        $nodeIds[$nid] = $nid;
      }
    }
  }
}

// If the issue database is available and up to date, use that to get data on
// all fixed issues during the timeframe.

// Fetch the issue status information from Drupal.org.
$issueFetcher = new SingleIssueFetcher(new SingleIssueRequest(array_values($nodeIds)), new Client());
$issueFetcher->fetch();
$issueFetcher->fetchAllFromCache();
$issueData = $issueFetcher->getData();

$fixed = [];
$open = [];
foreach ($issueData as $nodeId => $issue) {
  // array_pop because each single issue is an array of a single element.
  $issue = array_pop($issue);
  if (in_array($issue->field_issue_status, MagicIntMetadata::$fixed)) {
    $fixed[$nodeId] = $nodeId;
  }
  elseif (in_array($issue->field_issue_status, MagicIntMetadata::$open)) {
    $open[$nodeId] = $nodeId;
  }
}

print "\n\n";

$orgLabels = array_flip(MagicIntMetadata::$orgs);
foreach ($dataByOrg as $orgId => $issues) {
  print "# Issues attributed to " . $orgLabels[$orgId] . " by $username for $timeframeStartDate through $timeframeEndDate\n\n";
  foreach (['Fixed' => $fixed, 'Open' => $open] as $label => $list) {
    print "## $label issues\n";
    foreach ($issues as $nodeId) {
      if (in_array($nodeId, $list)) {
        $isCredited = FALSE;
        if ($label === 'Fixed') {
          foreach ($issueData[$nodeId][0]->field_issue_credit as $creditEntry) {
            if ($creditEntry->data->username == $username) {
              $isCredited = TRUE;
              break;
            }
          }
        }
        if ($isCredited || $label === 'Open') {
          print "- [" . $issueData[$nodeId][0]->title . "](" . $issueData[$nodeId][0]->url . ")\n";
        }
      }
    }
    print "\n";
  }
  print "\n\n";
}
