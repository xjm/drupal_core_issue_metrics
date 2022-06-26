<?php

namespace Drupal\core_metrics;

class GitLogParser {

  use ValidateBranchTrait;

  /**
   * The file path to the parent directory of an up-to-date git clone of core.
   */
  const REPOSITORY_PATH = '../../';

  /**
   * Whether to parse by issue ID or git commit hash.
   */
  protected bool $byIssue = TRUE;

  /**
   * Directory names for up-to-date git clones, within static::REPOSITORY_PATH.
   */
  protected static array $repositoryNames = [
    'core' => 'drupal',
  ];

  /**
   * The string containing the git command to run.
   */
  protected string $command;

  /**
   * The raw git log output.
   */
  protected string $rawLog;

  /**
   * The array of parsed commit messages, keyed by node ID or hash.
   *
   * @var string[]
   */
  protected array $parsedCommits = [];

  /**
   * The "official" start dates for each core branch, in ISO 8601.
   *
   * @var string[]
   */
  private static array $branchDates = [
    '8.0.x' => '2011-03-08',
    '8.1.x' => '2015-12-11',
    '8.2.x' => '2016-03-02',
    '8.3.x' => '2016-08-02',
    '8.4.x' => '2017-01-27',
    '8.5.x' => '2017-07-28',
    '8.6.x' => '2018-01-12',
    '8.7.x' => '2018-07-13',
    '8.8.x' => '2019-03-07',
    '8.9.x' => '2019-10-10',
    '9.0.x' => '2019-10-10',
    '9.1.x' => '2020-04-01',
    '9.2.x' => '2020-10-16',
    '9.3.x' => '2021-05-01',
    '9.4.x' => '2021-10-29',
    '10.0.x' => '2021-11-30',
    '9.5.x' => '2022-04-29',
    '10.1.x' => '2022-06-27',
  ];

  /**
   * Constructs a new git log parser.
   *
   * @param string $branch
   *   The git branch name.
   * @param string $project
   *   (optional) The machine name for the project, or 'core' for Drupal core.
   *   Defaults to 'core'.
   * @param DateTime $after
   *   (optional) The start date for the data (only data after the date will be
   *   included.) If NULL, the date of the earliest active development on the
   *   branch is used.
   * @param DateTime $before
   *   (optional) The end date for the data (only data before the date will be
   *   included.) If NULL, all the most recent data will be included.
   */
  public function __construct(protected string $branch, protected string $project = 'core', protected \DateTime|null $after = NULL, protected \DateTime|null $before = NULL)
  {
    // Validate and sanitize the branch name.
    $this->branch = static::validateBranch($branch);

    // If no start date was passed, use the oldest data for the branch.
    if (empty($after)) {
      $after = new \DateTime(static::$branchDates[$branch]);
    }
    // If no end date was passed, use now.
    if (empty($before)) {
      $before = new \DateTime('now');
    }

    $this->command = "git log $branch --format='HASH:%H:MESSAGE:%s:ENDCOMMIT' "
      . " --after=" . $after->format('Y-m-d')
      . " --before=" . $before->format('Y-m-d');

    // Allow the repository either to be in a predefined directory relative
    // to static::REPOSITORY_PATH, as listed in static::$repositoryNames, or
    // an arbitrary, sanitized project directory name.
    $repositoryName = !empty(static::$repositoryNames[$project])
      ? static::$repositoryNames[$project]
      : static::sanitizeDirectoryName($project);

    chdir(__DIR__ . '/' . static::REPOSITORY_PATH . '/' . $repositoryName);
    $this->rawLog = shell_exec($this->command);
    $this->parseLog();
  }

  /**
   * Sanitizes a directory name string for a file path.
   *
   * @param string $directory
   *   The directory name of the directory containing a git repository. For
   *   example, 'drupal' or 'olivero'.
   */
  protected static function sanitizeDirectoryName($directory) {
    return preg_replace('/[^A-Za-z0-9_\-]/', '_', $directory);
  }

  /**
   * Returns the unique node IDs or git hashes for the branch.
   */
  public function getParsedCommits() {
    return $this->parsedCommits;
  }

  /**
   * Parses the git log messages into issues.
   */
  protected function parseLog() {
    if (empty($this->rawLog) || !strpos($this->rawLog,':ENDCOMMIT')) {
      throw new \UnexpectedValueException('The git log was empty or in an unexpected format.');
    }
    $commits = explode(":ENDCOMMIT\n", $this->rawLog);
    $parsedCommits = [];
    // For core or other repsitories that use the core standard git log
    // format, filter the git log to only commits referencing a node ID.
    if ($this->byIssue) {
      $regex = '/^HASH:([0-9a-fA-F]+):MESSAGE:Issue #([0-9]+)( by ([^:])+)?(:.*)?$/';
      $idMatchIndex = 2;
      $messageMatchIndex = 5;
    }
    // Otherwise, select all commits and index by commit hash.
    else {
      $regex = '/^HASH:([0-9a-fA-F]+):MESSAGE:(.*)/$';
      $idMatchIndex = 1;
      $messageMatchIndex = 2;
    }
    foreach ($commits as $commit) {
      $matches = [];
      if (preg_match($regex, $commit, $matches)) {
        $parsedCommits[$matches[$idMatchIndex]] = $matches[$messageMatchIndex];
      }
    }
    $this->parsedCommits = $parsedCommits;
  }

}
