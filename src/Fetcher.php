<?php

namespace Drupal\core_metrics;

use GuzzleHttp\Client;

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
    foreach ($this->issueRequest->urls as $branch => $url) {
      print "Fetching {$this->issueRequest->type} data for $branch.\n";
      $this->data[$branch] =  $this->doFetch($url);
    }
  }

  /**
   * Fetches all pages for a given query URL.
   *
   * @param string $url
   *   The URL to fetch.
   */
  protected function doFetch($url) {
    // Cache results locally.
    $filename = __DIR__ . '/../cache/' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $url) . '_' . date('Y-W') . '.txt';
    if (file_exists($filename)) {
      print "Loading data from cache.\n";
      return json_decode(file_get_contents($filename));
    }

    $i = 0;
    $data = [];
    do {
      print "Fetching page $i.\n";
      $page = $this->doFetchPage($url);
      if (!empty($page->next)) {
        $url = str_replace('api-d7/node', 'api-d7/node.json', $page->next);
      }
      $data = array_merge($data, $page->list);
      $i++;
      // Cap the number of pages we fetch at 200 so Neil doesn't ban us.
    } while (($i < 200) && (!empty($page->next)));

    file_put_contents($filename, json_encode($data));
    return $data;
  }

  /**
   * Fetches a single page for a given query URL.
   *
   * @param string $url
   *   The URL to fetch.
   * $param int $page
   *   The page to fetch. Defaults to 0.
   */
  protected function doFetchPage($url) {
    $response = $this->client->request('GET', $url);
    $response_body = json_decode($response->getBody());
    return $response_body;
  }

}
