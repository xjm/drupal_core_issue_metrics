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
   * Fetches one data set from the cache only.
   */
  public function fetchAllFromCache() {
    foreach ($this->issueRequest->getUrls() as $branch => $url) {
      $this->data[$branch] = $this->fetchFromCache($url);
      if (empty($this->data[$branch])) {
        throw new \Exception("Data for branch $branch is not available in the cache. Fetch it first.");
      }
    }
  }

  /**
   * Fetches one data set from the cache only.
   */
  public function fetchFromCache(string $url) {
    $path = static::getCacheFilePath(static::getCacheFileName($url));
    if (file_exists($path)) {
      return json_decode(file_get_contents($path));
    }
  }

  /**
   * Constructs the caching filename for the URL.
   *
   * @param string $url
   *   The URL of the issue request.
   */
  protected static function getCacheFileName(string $url) {
    return preg_replace('/[^A-Za-z0-9_\-]/', '_', $url) . '_' . date('Y-W') . '.txt';
  }

  /**
   * Constructs the path to the cache file.
   *
   * @param string $fileName
   *   The filename.
   */
  protected static function getCacheFilePath(string $fileName) {
    return __DIR__ . '/../cache/' . $fileName;
  }

  /**
   * Constructs the path to the partial cache file.
   *
   * @param string $fileName
   *   The filename.
   */
  protected static function getPartialFilePath(string $fileName) {
    return __DIR__ . '/../cache/partial/' . $fileName;
  }

  /**
   * Fetches all pages for a given query URL.
   *
   * @param string $url
   *   The URL of page 0.
   */
  protected function doFetch($url) {
    $cache = $this->fetchFromCache($url);
    if (!empty($cache)) {
      print "Loading data from cache.\n";
      return $cache;
    }
    // Cache results locally.
    $fileName = static::getCacheFileName($url);
    $filePath = static::getCacheFilePath($url);
    $partialPath = static::getPartialFilePath($url);
    $i = 0;

    if (file_exists($partialPath)) {
      print "Loading partial data from cache.\n";
      $data = json_decode(file_get_contents($partialPath));
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
          file_put_contents($partialPath, json_encode($data));
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

    file_put_contents($filePath, json_encode($data));
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
