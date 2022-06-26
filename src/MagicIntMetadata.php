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
   // The current (stable) minor release branch.
   'stable' => '9.4.x',

   // The upcoming (alpha, beta, or RC) minor release branch in preparation.
   'prep' => '9.5.x',

   // The next (developmental) minor release branch.
   'dev' => '9.5.x',

   // The next major release branch.
   'major' => '10.0.x',
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
