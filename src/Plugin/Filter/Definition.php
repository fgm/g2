<?php

declare(strict_types=1);

namespace Drupal\g2\Plugin\Filter;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\UnroutedUrlAssemblerInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\g2\G2;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter to expand <dfn> entries to G2 glossary links.
 *
 * @Filter(
 *   id = "g2:dfn",
 *   title = @Translation("Convert <code>&lt;dfn/&gt</code> elements into links
 *   to definitions in the G2 glossary."), type =
 *   Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 *   settings = {}, description=@Translation("Wrap <code>&lt;dfn&gt;</code>
 *   elements around the words for which you want a link to the matching G2
 *   entries, like this: <code>&lt;dfn&gt;UTF-8&lt;/dfn&gt;</code>. This filter
 *   <em>must</em> appear after the automatic <code>&lt;dfn/&gt;</code>
 *   wrapping filter."), description=@Translation("Converts
 *   <code>&lt;dfn&gt;some entry&lt;/dfn&gt;</code> elements into links to the
 *   matching G2 entry, or a homonyms disambiguation page if multiple entries
 *   match the given string. This filter <em>must</em> be applied after the
 *   automatic <code>&lt;dfn/&gt;</code> wrapping filter."), weight = 0,
 * )
 */
class Definition extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The unrouted_url_assembler service.
   *
   * @var \Drupal\Core\Utility\UnroutedUrlAssemblerInterface
   */
  protected UnroutedUrlAssemblerInterface $urlAssembler;

  /**
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $etm;

  /**
   * The core renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected RendererInterface $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $config = $container->get(G2::SVC_CONF);
    $etm = $container->get(G2::SVC_ETM);
    $renderer = $container->get('renderer');
    $uua = $container->get('unrouted_url_assembler');

    return new static($configuration, $plugin_id, $plugin_definition, $config, $etm, $renderer, $uua);
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    ConfigFactoryInterface $configFactory,
    EntityTypeManagerInterface $etm,
    RendererInterface $renderer,
    UnroutedUrlAssemblerInterface $urlAssembler,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->etm = $etm;
    $this->renderer = $renderer;
    $this->urlAssembler = $urlAssembler;
  }

  /**
   * Translate glossary linking elements (<dfn>) to local links)
   *
   * This function generates absolute links, for the benefit of the WOTD RSS
   * feed If this feed is not used, it is possible to use the (shorter)
   * relative URLs by swapping comments.
   *
   * @param array $entry
   *   A 2-entry array, the first being the complete prepared text,
   *   and the second being its content.
   *
   * @return string
   *   HTML.
   */
  protected function doLocalProcess(array $entry): string {
    $text = $entry[1];
    $tooltipsLevel = $this->configFactory->get(G2::CONFIG_NAME)
      ->get(G2::VARTOOLTIPS);

    if ($tooltipsLevel === G2::TOOLTIPS_NONE) {
      $tooltip = '';
    }
    else {
      $nodes = g2_entry_load($text);
      $count = count($nodes);
      switch ($count) {
        case 0:
          $tooltip = $this->t(
            'No entry found for @entry', [
              '@entry' => $text,
            ]
          );
          break;

        case 1:
          if ($tooltipsLevel == G2::TOOLTIPS_TITLES) {
            /** @var \Drupal\node\NodeInterface $node */
            $node = reset($nodes);
            $tooltip = $node->label();
          }
          else {
            $builder = $this->etm->getViewBuilder(G2::TYPE);
            /** @var \Drupal\node\NodeInterface $node */
            $node = reset($nodes);
            $tooltipRA = $builder->view($node, G2::VM_TOOLTIPS);
            $tooltipHTML = $this->renderer
              ->renderRoot($tooltipRA);
            $tooltip = preg_replace('/(\s)\s+/m', '$1',
              trim(strip_tags("$tooltipHTML")));
          }
          break;

        default:
          $tooltip = $this->formatPlural($count,
            '@entry', '@count entries for @entry', [
              '@count' => $count,
              '@entry' => $text,
            ]
          );
          break;
      }
    }

    $attributes = ['class' => 'g2-dfn-link'];
    if (!empty($tooltip)) {
      $attributes['title'] = $tooltip;
    }

    $link = Link::createFromRoute(
      $text,
      G2::ROUTE_HOMONYMS,
      ['g2_match' => $text],
      ['absolute' => TRUE, 'attributes' => $attributes]
    );
    return "{$link->toString()}";
  }

  /**
   * Translate glossary linking elements (<dfn>) to remote links)
   *
   * This function generates absolute links, for the benefit of the WOTD RSS
   * feed If this feed is not used, it is possible to use the (shorter)
   * relative URLs by swapping comments.
   *
   * @param array $entry
   *   A 2-entry array, the first being the complete prepared text,
   *   and the second being its content.
   *
   * @return string
   *   A <a href> string.
   */
  protected function doRemoteProcess(array $entry): string {
    $text = $entry[1];
    // When this method is called, we know this is not empty.
    $target = $this->configFactory->get(G2::CONFIG_NAME)->get(G2::VARREMOTEG2);

    $path = urlencode(G2::encodeTerminal($text));
    // We do not have access to the caching metadata on the remote site with
    // the current version of the API.
    $url = Url::fromUri("${target}/${path}", [
      'absolute' => TRUE,
      'attributes' => ['class' => 'g2-dfn-link'],
    ]);
    $ret = Link::fromTextAndUrl($text, $url)->toString();
    return $ret;
  }

  /**
   * {@inheritDoc}
   */
  public function prepare($text, $langcode): string {
    $text = parent::prepare($text, $langcode);
    $text = preg_replace('@<dfn>(.+?)</dfn>@s', "[g2-dfn]\\1[/g2-dfn]",
      $text);
    return $text;
  }

  /**
   * {@inheritDoc}
   */
  public function process($text, $langcode) {
    $settings = $this->configFactory->get(G2::CONFIG_NAME);
    $target = $settings->get(G2::VARREMOTEG2);
    $method = empty($target)
      ? [$this, 'doLocalProcess']
      : [$this, 'doRemoteProcess'];

    $text = preg_replace_callback('@\[g2-dfn\](.+?)\[/g2-dfn\]@s', $method, $text);
    $result = new FilterProcessResult($text);
    return $result;
  }

  /**
   * {@inheritDoc}
   */
  public function tips($long = FALSE) {
    $ret = $long
      ? $this->t('Wrap &lt;dfn&gt; elements around the terms for which you want a link to the available G2 definition(s).')
      : $this->t('You may link to G2 definitions using &lt;dfn&gt; elements.');
    return $ret;
  }

}
