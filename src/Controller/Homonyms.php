<?php

/**
 * @file
 * Contains G2 homonyms controller.
 */

namespace Drupal\g2\Controller;

use Drupal\Component\Render\HtmlEscapedText;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Homonyms contains the controller for the entry list pages.
 *
 * Pages are:
 * - Entries by full name.
 * - Entries by initial.
 */
class Homonyms implements ContainerInjectionInterface {
  /**
   * Title of the G2 pages listing entries.
   */
  const ENTRIES_BY_NAME = 'G2 entries matching %entry';

  /**
   * Title of the G2 by-initial pages.
   */
  const ENTRIES_BY_INITIAL = 'G2 entries starting with initial %initial';

  const VIEW_MODE = 'g2_entry_list';

  public function __construct() {
  }

  public static function create(ContainerInterface $container) {
    return new static();
  }

  /**
   * Controller for g2.entries.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route
   *   The current route.
   * @param array $g2_match
   *   Unsafe. The entry for which to find matching G2 entries.
   *
   * @return array
   *   Render array.
   */
  public function indexAction(RouteMatchInterface $route, array $g2_match = NULL) {
    $raw_match = $route->getRawParameter('g2_match');
    $result = [
      '#theme' => 'g2_entries',
      '#raw_entry' => $raw_match,
      '#entries' => $g2_match,
    ];
    return $result;

  }

  /**
   * Title callback for g2.entries.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The page title.
   */
  public function indexTitle(RouteMatchInterface $route) {
    $raw_match = $route->getRawParameter('g2_match');
    return t(static::ENTRIES_BY_NAME, ['%entry' => $raw_match]);
  }

  /**
   * Return a homonyms disambiguation page for homonym entries.
   *
   * The page is built:
   * - either by this module
   * - either from a site node (typically in PHP input format)
   *
   * When examining the code to build $entry, remember that
   * we need to obtain slashes, which drupal pre-processes.
   *
   * Note that we query and use n.title instead of using $entry2
   * in the results to obtain mixed case results when they exist.
   *
   * @return string
   *   HTML: the themed "list of entries" page content.
   */
  protected function themeG2Entries($entries = array()) {

    $entry = filter_xss(arg(2));
    drupal_set_title(t('G2 Entries for %entry', array('%entry' => $entry)));

    // The nid for the disambiguation page.
    $page_nid = variable_get(G2VARHOMONYMS, G2DEFHOMONYMS);

    if ($page_nid) {
      $page_node = node_load($page_nid);
      $ret = node_view($page_node);
    }
    else {

      $count = count($entries);
      switch ($count) {
        case 0:
          $ret = t('<p>There are currently no entries for %entry.</p>',
            array('%entry' => $entry));
          if (node_access('create', G2NODETYPE)) {
            $ret .= t('<p>Would you like to <a href="!url" title="Create new entry for %entry">create</a> one ?</p>',
              array(
                '!url'   => url(str_replace('_', '-', G2PATHNODEADD) . '/' . $entry),
                '%entry' => $entry
              )
            );
          }
          break;

        case 1:
          $next = 'node/' . reset($entries)->nid;
          // Does the webmaster want us to jump ?
          if (variable_get(G2VARGOTOSINGLE, G2DEFGOTOSINGLE)) {
            $redirect_type = variable_get(G2VARHOMONYMSREDIRECT, G2DEFHOMONYMSREDIRECT);
            drupal_goto($next, NULL, NULL, $redirect_type);
            // Never returns
          }
        // Do not break: we continue with default processing in this case.

        default:
          // Style more-link specifically
          drupal_add_css(drupal_get_path('module', 'g2') . '/g2.css', 'module', 'all', FALSE);

          $vid = variable_get(G2VARHOMONYMSVID, G2DEFHOMONYMSVID);
          $rows = array();
          foreach ($entries as $nid => $node) {
            $path = 'node/' . $nid;
            $terms = array();
            foreach ($node->taxonomy as $tid => $term) {
              if ($vid && $term->vid == $vid) {
                $terms[] = l($term->name, taxonomy_term_path($term));
              }
              $taxonomy = empty($terms)
                ? NULL
                : ' <span class="inline">(' . implode(', ', $terms) . ')</span>';

            }
            $teaser = strip_tags(check_markup($node->teaser, $node->format));
            $rows[] = t('!link!taxonomy: !teaser!more', array(
              '!link'     => l($node->title, $path),
              '!taxonomy' => $taxonomy, // safe by construction
              '!teaser'   => $teaser,
              '!more'     => theme('more_link', url($path),
                t('Full definition for @name: !teaser',
                  array('@name' => $entry, '!teaser' => $teaser))),
            ));
          }
          $ret = theme('item_list', $rows, NULL, 'ul', array('class' => 'g2-entries'));
      }
      return $ret;
    }
  }

  /**
   * Alternative version.
   */
  protected function zThemeG2Entries($variables) {
    $entries = $variables['entries'];
    $entry = filter_xss(arg(2));

    drupal_set_title(t('G2 Entries for %entry', array('%entry' => $entry)), PASS_THROUGH);

    // The nid for the disambiguation page.
    $page_nid = variable_get(G2\VARHOMONYMS, G2\DEFHOMONYMS);

    if ($page_nid) {
      $page_node = node_load($page_nid);
      // Coder false positive: http://drupal.org/node/704010 .
      $ret = node_view($page_node);
    }
    else {

      $count = count($entries);
      switch ($count) {
        case 0:
          $ret = t('<p>There are currently no entries for %entry.</p>',
            array('%entry' => $entry));
          if (node_access('create', G2\NODETYPE)) {
            $ret .= t('<p>Would you like to <a href="!url" title="Create new entry for @entry">create</a> one ?</p>',
              array(
                '!url' => url(str_replace('_', '-', G2\PATHNODEADD) . '/' . $entry),
                '@entry' => strip_tags($entry),
              ));
          }
          break;

        case 1:
          $next = entity_uri('node', reset($entries));
          // Does the webmaster want us to jump ?
          if (variable_get(G2\VARGOTOSINGLE, G2\DEFGOTOSINGLE)) {
            $redirect_type = variable_get(G2\VARHOMONYMSREDIRECT, G2\DEFHOMONYMSREDIRECT);
            drupal_goto($next['path'], $next['options'], $redirect_type);
            // Never returns.
          }
        /* Do not break: we continue with default processing in this case. */

        default:
          $vid = variable_get(G2\VARHOMONYMSVID, G2\DEFHOMONYMSVID);
          $rows = array();
          foreach ($entries as $nid => $node) {
            $uri = entity_uri('node', $node);
            $terms = array();
            if (!isset($node->taxonomy)) {
              $node->taxonomy = array();
            }
            foreach ($node->taxonomy as $tid => $term) {
              if ($vid && $term->vid == $vid) {
                $terms[] = l($term->name, taxonomy_term_path($term));
              }
            }
            $taxonomy = empty($terms)
              ? NULL
              : ' <span class="inline">(' . implode(', ', $terms) . ')</span>';
            $teaser = isset($node->teaser)
              ? strip_tags(check_markup($node->teaser, $node->format))
              : NULL;
            $rows[] = t('!link!taxonomy: !teaser!more', array(
              '!link' => l($node->title, $uri['path'], $uri['options']),
              // Safe by construction.
              '!taxonomy' => $taxonomy,
              '!teaser' => $teaser,
              '!more' => theme('more_link', array(
                  'url' => $uri['path'],
                  'options' => $uri['options'],
                  'title' => t('Full definition for @name: !teaser', array(
                    '@name' => $entry,
                    '!teaser' => $teaser,
                  )),
                )
              ),
            ));
          }
          $ret = theme('item_list', array(
            'items' => $rows,
            'title' => NULL,
            'type' => 'ul',
            'attributes' => array('class' => 'g2-entries'),
          ));
          /* No break; in final default clause. */
      }
      return $ret;
    }
  }

}
