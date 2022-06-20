<?php

namespace Drupal\core_metrics;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Fetches results for a given IssueRequest object.
 *
 * @see https://www.drupal.org/drupalorg/docs/apis/rest-and-other-apis
 */
class Fetcher {

  /**
   * The result data.
   */
  protected array $data = [];

  /**
   * Constructs a new fetcher.
   */
  public function __construct(protected IssueRequest $issueRequest, protected Client $client) {}

  /**
   * Gets the stored data from the response.
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Fetches data from the URLs.
   */
  public function fetch() {
    foreach ($this->issueRequest->getUrls() as $branch => $url) {
      print "Fetching {$this->issueRequest->getType()} data for $branch.\n";
      $this->data[$branch] =  $this->doFetch($url);
    }
  }

  /**
   * Fetches all pages for a given query URL.
   *
   * @param string $url
   *   The URL of page 0.
   */
  protected function doFetch($url) {
    // Cache results locally.
    $file_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $url) . '_' . date('Y-W') . '.txt';
    $file_path = __DIR__ . '/../cache/' . $file_name;
    $partial_path = __DIR__ . '/../cache/partial/' . $file_name;
    $i = 0;

    print $file_path . "\n";
    die();

    if (file_exists($file_path)) {
      print "Loading data from cache.\n";
      return json_decode(file_get_contents($file_path));
    }
    elseif (file_exists($partial_path)) {
      print "Loading partial data from cache.\n";
      $data =  json_decode(file_get_contents($partial_path));
      $i = $data->PAGER;
      unset($data->PAGER);
    }

    $data = [];
    do {
      print "Fetching page $i.\n";
      try {
        $page = $this->doFetchPage($url);
      }
      catch (BadResponseException $e) {
        if ($i > 0 && $e->getCode() === 503) {
          $data['PAGER'] = $i;
          print "Writing partial data...\n";
          file_put_contents($partial_path, json_encode($data));
        }
        die("Failed to fetch data on page $i.\n");
      }

      if (!empty($page->next)) {
        $url = str_replace('api-d7/node', 'api-d7/node.json', $page->next);
      }
      $data = array_merge($data, $page->list);
      $i++;
      // Cap the number of pages we fetch at 200 so Neil doesn't ban us.
    } while (($i < 200) && (!empty($page->next)));

    file_put_contents($file_path, json_encode($data));
    return $data;
  }

  /**
   * Fetches a single page for a given query URL.
   *
   * @param string $url
   *   The URL to fetch.
   */
  protected function doFetchPage($url) {
    try {
      $response = $this->client->request('GET', $url);
    }
    catch (BadResponseException $e) {
      if ($e->getCode() === 503) {
        // If we get a 503, sleep and retry once.
        print "Got a 503; retrying...\n";
        sleep(10);
        $response = $this->client->request('GET', $url);
      }
      else {
        throw $e;
      }
    }

    $response_body = json_decode($response->getBody());
    return $response_body;
  }

}
