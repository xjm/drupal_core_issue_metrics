<?php

namespace Drupal\core_metrics;

class GitLogParser {

  use ValidateBranchTrait;

  /**
   * The file path to the parent directory of an up-to-date git clone of core.
   */
  const REPOSITORY_PATH = '../../';

  /**
   * The static issue metadata IDs.
   */
  protected static MagicIntMetadata $magic;

  /**
   * Whether to parse by issue ID or git commit hash.
   */
  protected bool $byIssue = TRUE;

  /**
   * Directory names for up-to-date git clones, within static::REPOSITORY_PATH.
   */
  protected static array $repositoryNames = [
    'core' => 'drupal',
    'automatic_updates' => 'automatic_updates',
    'project_browser' => 'project_browser',
    'ckeditor5' => 'ckeditor5',
    'composer-stager' => 'composer-stager',
    'composer-integration' => 'composer-integration',
    'php-tuf' => 'php-tuf',
    'olivero' => 'olivero',
    'claro' => 'claro',
    'jsonapi' => 'jsonapi',
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
   *
   * @throws \Exception
   *   If the Git repository is not found at the expected path.
   */
  public function __construct(protected string $branch, protected string $project = 'core', protected \DateTime|null $after = NULL, protected \DateTime|null $before = NULL)
  {
    static::$magic = new MagicIntMetadata();

    if ($this->project !== 'core') {
      $this->byIssue = FALSE;
    }

    // Validate and sanitize the branch name.
    if ($this->project === 'core') {
      $this->branch = static::validateCoreBranch($branch);
    }
    else {
      $this->branch = static::sanitizeBranch($branch);
    }

    // If no start date was passed, use the oldest data for the branch.
    if (empty($after)) {
      $after = new \DateTime(static::$magic::$branchDates[$branch]);
    }
    // If no end date was passed, use the core commit date, or now for core.
    if (empty($before)) {
      if (($project === 'core') || empty(static::$magic::$coreAddDates[$project])) {
        $before = new \DateTime('now');
      }
      else {
        $before = new \DateTime(static::$magic::$coreAddDates[$project]);
      }
    }

    $this->command = "git log $branch --format=':::GIT_HASH:::%H:::GIT_DATE:::%as:::GIT_MESSAGE:::%s:::GIT_ENDCOMMIT:::'"
      . " --after=" . $after->format('Y-m-d')
      . " --before=" . $before->format('Y-m-d');

    // Allow the repository either to be in a predefined directory relative
    // to static::REPOSITORY_PATH, as listed in static::$repositoryNames, or
    // an arbitrary, sanitized project directory name.
    $repositoryName = !empty(static::$repositoryNames[$project])
      ? static::sanitizeDirectoryName(static::$repositoryNames[$project])
      : static::sanitizeDirectoryName($project);

    $path =  __DIR__ . '/' . static::REPOSITORY_PATH . $repositoryName;
    if (!chdir($path)) {
      throw new \Exception("Invalid path: $path\n");
    }
    $this->rawLog = shell_exec($this->command) ?? '';
    $this->parseLog();
  }

  /**
   * Sanitizes a directory name string for a file path.
   *
   * @param string $directory
   *   The directory name of the directory containing a git repository. For
   *   example, 'drupal' or 'olivero'.
   *
   * @return string
   *   The sanitized name.
   */
  protected static function sanitizeDirectoryName(string $directory): string {
    return preg_replace('/[^A-Za-z0-9_\-]/', '_', $directory);
  }

  /**
   * Returns the commits, indexed by unique node IDs or git hashes.
   */
  public function getParsedCommits(): array {
    return $this->parsedCommits;
  }

  /**
   * Parses the git log messages into issues.
   *
   * @throws \UnexpectedValueException
   *   Thrown if the git log was in an unexpected format.
   */
  protected function parseLog(): void {
    if (!empty($this->rawLog) && !strpos($this->rawLog,':::GIT_ENDCOMMIT:::')) {
      throw new \UnexpectedValueException('The git log was in an unexpected format.');
    }
    $commits = explode(":::GIT_ENDCOMMIT:::\n", $this->rawLog);
    $parsedCommits = [];
    // For core or other repsitories that use the core standard git log
    // format, filter the git log to only commits referencing a node ID.
    if ($this->byIssue) {
      $regex = '/^:::GIT_HASH:::([0-9a-fA-F]+):::GIT_DATE:::([0-9]{4}-[0-9]{2}-[0-9]{2}):::GIT_MESSAGE:::Issue #([0-9]+)( by ([^:])+)?(:(.*))?$/';
      $idMatchIndex = 3;
      $dateMatchIndex = 2;
      $messageMatchIndex = 7;
    }
    // Otherwise, select all commits and index by commit hash.
    else {
      $regex = '/^:::GIT_HASH:::([0-9a-fA-F]+):::GIT_DATE:::([0-9]{4}-[0-9]{2}-[0-9]{2}):::GIT_MESSAGE:::(.*)$/';
      $idMatchIndex = 1;
      $dateMatchIndex = 2;
      $messageMatchIndex = 3;
    }
    foreach ($commits as $commit) {
      $matches = [];
      if (preg_match($regex, $commit, $matches)) {
        $parsedCommits[$matches[$idMatchIndex]] = [
          'date' => $matches[$dateMatchIndex],
          'message' => $matches[$messageMatchIndex],
        ];
      }
    }
    $this->parsedCommits = $parsedCommits;
  }

}
