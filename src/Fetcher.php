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
  public function getData(): array {
    return $this->data;
  }

  /**
   * Fetches data from the URLs.
   */
  public function fetch(): void {
    foreach ($this->issueRequest->getUrls() as $branch => $url) {
      print "Fetching {$this->issueRequest->getType()} data for $branch.\n";
      $this->data[$branch] =  $this->doFetch($url);
    }
  }

  /**
   * Fetches all requested data set from the cache only.
   *
   * @throws \Exception
   *   When the branh data is unavailable.
   */
  public function fetchAllFromCache(): void {
    foreach ($this->issueRequest->getUrls() as $branch => $url) {
      $this->data[$branch] = $this->fetchFromCache($url);
      if (empty($this->data[$branch])) {
        throw new \Exception("Data for branch $branch is not available in the cache. Fetch it first.");
      }
    }
  }

  /**
   * Fetches one data set from the cache only.
   *
   * @param string $url
   *   The Drupal.org query URL.
   * @param bool $partial
   *   (optional) Whether to write to the partial data path. Defaults to FALSE.
   *
   * @return mixed
   *   JSON-decoded data.
   */
  public function fetchFromCache(string $url, bool $partial = FALSE) {
      $name = static::getCacheFileName($url);
      $path = $partial ? static::getPartialFilePath($name) : static::getCacheFilePath($name);
    if (file_exists($path)) {
      return json_decode(file_get_contents($path));
    }
  }

  /**
   * Writes one data set to the cache.
   *
   * @param string $url
   *   The Drupal.org query URL.
   * @param mixed $data
   *   The data to encode for storage.
   * @param bool $partial
   *   (optional) Whether to write to the partial data path. Defaults to FALSE.
   */
  public function writeToCache(string $url, $data, $partial = FALSE): void {
      $name = static::getCacheFileName($url);
      $path = $partial ? static::getPartialFilePath($name) : static::getCacheFilePath($name);
    file_put_contents($path, json_encode($data));
  }

  /**
   * Gets the pager from a partial data set and unsets it.
   *
   * @param mixed $data
   *   The data loaded from the partial cache.
   *
   * @return int $pager
   *   The pager value.
   */
  public function extractPager($data): int {
    $pager = $data->PAGER;
    unset($data->PAGER);
    return $pager;
  }

  /**
   * Inserts the pager for a partial data set.
   *
   * @param mixed $data
   *   The data loaded from the partial cache.
   * @param int pager
   *   The page of the result set that failed.
   */
  public function insertPager($data, int $pager): void {
    $data['PAGER'] = $i;
  }

  /**
   * Constructs the caching filename for the URL.
   *
   * @param string $url
   *   The URL of the issue request.
   *
   * @return string
   *   The file name to use for the cache.
   */
  protected static function getCacheFileName(string $url): string {
    return preg_replace('/[^A-Za-z0-9_\-]/', '_', $url) . '_' . date('Y-W') . '.txt';
  }

  /**
   * Constructs the path to the cache file.
   *
   * @param string $fileName
   *   The file name.
   *
   * @return string
   *   The file path.
   */
  protected static function getCacheFilePath(string $fileName): string {
    return __DIR__ . '/../cache/' . $fileName;
  }

  /**
   * Constructs the path to the partial cache file.
   *
   * @param string $fileName
   *   The file name.
   *
   * @return string
   *   The file path to the partial cache..
   */
  protected static function getPartialFilePath(string $fileName): string {
    return __DIR__ . '/../cache/partial/' . $fileName;
  }

  /**
   * Fetches all pages for a given query URL.
   *
   * @param string $url
   *   The URL of page 0.
   *
   * @return mixed
   *   JSON response data.
   */
  protected function doFetch(string $url) {
    // Return data from the cache if it is available.
    $cache = $this->fetchFromCache($url);
    if (!empty($cache)) {
      print "Loading data from cache.\n";
      return $cache;
    }
    // Otherwise, cache results locally.
    $i = 0;
    $data = [];

    // Load partial data from the cache if it is available, starting with the
    // first failed page
    if ($data = $this->fetchFromCache($url, TRUE)) {
      print "Loading partial data from cache.\n";
      $i = $this->extractPager($data);
    }

    // Fetch each page.
    do {
      print "Fetching page $i.\n";
      try {
        $page = $this->doFetchPage($url);
      }
      catch (BadResponseException $e) {
        // If a request failed all its retries, write the data with its pager
        // to the partial cache.
        if ($i > 0 && $e->getCode() === 503) {
          $this->insertPager($data, $i);
          print "Writing partial data...\n";
          $this->writeToCache($url, $data, TRUE);
        }
        die("Failed to fetch data on page $i.\n");
      }

      // Follow the pager to the next page of the results.
      if (!empty($page->next)) {
        // Note that due to a views or Drupal.org bug, the URI of the pager has
        // a typo.
        $url = str_replace('api-d7/node', 'api-d7/node.json', $page->next);
      }
      $data = array_merge($data, $page->list);
      $i++;
      // Cap the number of pages we fetch at 200 so Neil doesn't ban us.
    } while (($i < 200) && (!empty($page->next)));

    $this->writeToCache($url);
    return $data;
  }

  /**
   * Fetches a single page for a given query URL.
   *
   * @param string $url
   *   The URL to fetch.
   *
   * @return mixed
   *   The JSON-decoded response body.
   *
   * @throws \GuzzleHttp\Exception\BasResponseException
   *   If Guzzle gives us a bad response code other than a 503.
   */
  protected function doFetchPage(string $url) {
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
