<?php

namespace Drupal\core_metrics;

/**
 * Static value object of potential issue metadata values for various criteria.
 *
 * @see https://www.drupal.org/drupalorg/docs/apis/rest-and-other-apis
 */
class MagicIntMetadata {

  /**
   * Project repos.
   */
  public static $project = [
    'core' => 3060,
    'automatic_updates' => 2997874,
    'project_browser' => 1143512,
    'olivero' => 3083133,
    'claro' => 3020054,
    'ckeditor5' => 3159840,
    'jsonapi' => 2723491,
    'decoupled_menus' => 3181806,
    'a11y_autocomplete' => 3196355,
    'once' => 3195030,
  ];

  /**
   * Dates projects were added to core, in ISO 8601.
   *
   * This data can be used to select historical data for core projects
   * developed in contrib.
   */
  public static $coreAddDates = [
    'ckeditor5' => '2021-11-11',
    'olivero' => '2020-10-16',
    'claro' => '2019-10-13',
    'jsonapi' => '2019-03-20',
  ];

  /**
   * Core-targeted git branches of contrib projects.
   */
  public static $contribBranches = [
    'automatic_updates' => '8.x-2.x',
    'project_browser' => '1.0.x',
    'ckeditor5' => '1.0.x',
    'composer-stager' => 'develop',
    'composer-integration' => 'main',
    'php-tuf' => 'main',
    'olivero' => 'core-patch',
    'claro' => '8.x-2.x',
    'jsonapi' => '8.x-2.x',
  ];

  /**
   * The "official" start dates for each core branch, in ISO 8601.
   *
   * @var string[]
   */
  public static array $branchDates = [
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
    '11.x' => '2023-05-09',
    '10.2.x' => '2023-10-10',
    '10.3.x' => '2024-02-21',
    '10.4.x' => '2022-06-27',
  ];

  /**
   * Issue statuses.
   */
  public static $status = [
    'active' => 1,
    'nw' => 13,
    'nr'  => 8,
    'rtbc' => 14,
    'postponed' => 4,
    'fixed' => 2,
    'closed_fixed' => 7,
  ];

  /**
   * The fixed statuses.
   */
  public static $fixed = [2, 7];

  /**
   * The relevant open statuses.
   */
  public static $open = [1, 13, 8, 14, 4];

  /**
   * Issue priorities.
   */
  public static $priority = [
   'critical' => 400,
   'major' => 300,
   'normal' => 200,
   'minor' => 100,
  ];

  /**
   * Issue types.
   */
  public static $type = [
    'bug' => 1,
    'task' => 2,
    'plan' => 5,
    'feature' => 3,
  ];

  /**
   * Current issue branches.
   */
  public static $activeBranches = [
    // The main development branch.
    'main' => '11.x',

    // The current (stable) minor release branch.
   'stable' => '10.3.x',

   // The upcoming (alpha, beta, or RC) minor release branch in preparation.
   // 'prep' => '11.1.x',

   // The next (developmental) minor release branch.
   'dev' => '11.1.x',

   // The next maintenance minor release branch.
   'maintenance' => '10.4.x',

   // The next major release branch.
   'major' => '11.0.x',
  ];

  /**
   * Issue tag term IDs.
   *
   * These term IDs will be found in taxonomy_vocabularly_9.
   */
  public static $tids = [
    'triaged_critical' => 197921,
    'triaged_major' => 174642,
    'critical_triage_deferred' => 197925,
    'major_triage_deferred' => 197926,
    'major_current_state' => 197923,
    'needs_major_current_state' => 180003,
    'fm_review' => 169963,
    'fefm_review' => 186449,
    'pm_review' => 170004,
    'rm_review' => 171496,
    'js_review' => 7488,
    'needs_rn' => 187468,
    'vdc' => 36416,
    'twig' => 36330,
    'entity' => 38578,
    'blocker' => 38080,
    'api_first' => 177096,
  ];

  public static $uids = [
    'dries' => 1,
    'alexpott' => 157725,
    'catch' => 35733,
    'cilefen' => 1850070,
    'cottser' => 1167326,
    'devin' => 290182,
    'alwaysworking' => 1602706,
    'effulgentsia' => 78040,
    'gabor' => 4166,
    'jessebeach' => 748566,
    'larowlan' => 395439,
    'lauriii' => 1078742,
    'moshe' => 23,
    'plach' => 183211,
    'timplunkett' => 241634,
    'webchick' => 24967,
    'wim' => 99777,
    'xjm' => 65776,
    'yoroy' => 41502,
    'system_message' => 180064,
  ];

  public static $fm = ['alexpott', 'effulgentsia', 'catch', 'larowlan'];
  public static $fefm = ['lauriii', 'bnjmnm', 'ckrina'];
  public static $rm = ['catch', 'xjm', 'quietone'];
  public static $pm = ['dries', 'webchick', 'yoroy', 'gabor'];
  public static $js = ['nod_', 'justafish'];

  public static $dat = [
    'gabor',
    'effulgentsia',
    'wim',
    'xjm',
    'timplunkett',
    'tedbow',
    // ...
  ];

}
