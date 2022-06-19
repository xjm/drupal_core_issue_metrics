<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Drupal\core_metrics\Fetcher;
use Drupal\core_metrics\IssueQuery;

$fetcher = new Fetcher(new IssueQuery(['9.4.x'], 'bug'), new Client());
$fetcher->fetch();
$data = $fetcher->getData();
print sizeof($data['9.4.x']);
