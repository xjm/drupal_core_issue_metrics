<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Drupal\core_metrics\MagicIntMetadata;
use Drupal\core_metrics\SingleIssueFetcher;
use Drupal\core_metrics\SingleIssueRequest;
use Drupal\core_metrics\UserRecentCommentFetcher;
use Drupal\core_metrics\UserRecentCommentRequest;

// Fetch recent data for xjm.
if (empty($argv[1])) {
  die("A username is required. Script usage:\nphp fetch_recent_comments.php xjm\n");
}
$username = $argv[1];

// Get the data from d.o.
$uid = MagicIntMetadata::$uids[$username];
$fetcher = new UserRecentCommentFetcher(new UserRecentCommentRequest($uid), new Client());
$fetcher->fetch();

// Load the data in the object.
$fetcher->fetchAllFromCache();
$data = $fetcher->getData();

// Collect organization and issue data from the comments.
$dataByOrg = [];
$nodeIds = [];
$mondayLastWeek = date('F d, Y', strtotime('last Monday', strtotime('1 week ago')));
$mondayThisWeek = date('F d, Y', strtotime('last Monday'));
$mondayLastWeekTimestamp = strtotime($mondayLastWeek);
$mondayThisWeekTimestamp = strtotime($mondayThisWeek);

// array_pop() because recent comment requests are a single type.
foreach (array_pop($data) as $comment) {
  if (!empty($comment->field_attribute_contribution_to)) {
    foreach ($comment->field_attribute_contribution_to as $org) {
        if ($comment->created >= $mondayLastWeekTimestamp && ($comment->created < $mondayThisWeekTimestamp)) {
        $nid = $comment->node->id;
        $dataByOrg[$org->id][$comment->node->id] = $nid;
        $nodeIds[$nid] = $nid;
      }
    }
  }
}

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
  print "# Issues attributed to " . $orgLabels[$orgId] . " by $username for the week of $mondayLastWeek\n\n";
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
