<?php

namespace Drupal\g2\Controller;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\g2\G2;
use Laminas\Diactoros\Response\XmlResponse;
use Laminas\Feed\Writer\Feed as FeedWriter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Feed contains the RSS feed controller.
 */
class Feed implements ContainerInjectionInterface {

  /**
   * The default description for the feed. Translatable.
   *
   * The @site placeholder is replaced by the default link to the site root.
   *
   * @see _g2_wotd_feed()
   */
  const DEFAULT_DESCRIPTION = 'A daily definition from the G2 Glossary at @site';

  /**
   * The default G2 author in feeds.
   */
  const DEFAULT_AUTHOR = '@author';

  /**
   * The g2.settings configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $g2Config;

  /**
   * The system.site configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig|string
   */
  protected ImmutableConfig $systemConfig;

  /**
   * Autocomplete constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The entity.manager service.
   * @param \Drupal\Core\Config\ImmutableConfig $g2Config
   *   The g2.settings/controller.wotd configuration.
   * @param \Drupal\Core\Config\ImmutableConfig $systemConfig
   *   The system email.
   */
  public function __construct(
    EntityTypeManagerInterface $etm,
    ImmutableConfig $g2Config,
    ImmutableConfig $systemConfig
  ) {
    $this->etm = $etm;
    $this->g2Config = $g2Config;
    $this->systemConfig = $systemConfig;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get(G2::SVC_CONF);

    $g2Config = $config_factory->get(G2::CONFIG_NAME);
    $systemConfig = $config_factory->get('system.site');

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager */
    $entity_manager = $container->get(G2::SVC_ETM);
    return new static($entity_manager, $g2Config, $systemConfig);
  }

  /**
   * Generate an RSS feed containing the latest WOTD.
   *
   * @return string
   *   XML in UTF-8 encoding
   */
  public function wotdAction() {
    $feed = new FeedWriter();

    // Title: Drupal 6 defaults to site name.
    $feed->setTitle($this->g2Config->get('controller.wotd.title'));

    // Language: Drupal 6 defaults to to $language->language.
    $feed->setLanguage(\Drupal::languageManager()
      ->getCurrentLanguage()
      ->getId());

    // Link element: Drupal 4.7->6 defaults to $base url.
    $feed->setLink(Url::fromRoute('g2.main')->toString());

    // Description: Drupal defaults to $site_mission.
    $descr = $this->g2Config->get('controller.wotd.description')
      ?: static::DEFAULT_DESCRIPTION;
    $feed->setDescription(strtr($descr, ['!site' => $GLOBALS['base_url']]));

    $rc = new \ReflectionClass($feed);
    $rp = $rc->getProperty('data');
    $rp->setAccessible(TRUE);
    $data = $rp->getValue($feed);
    $data['managingEditor'] = $this->systemConfig->get('mail');
    $rp->setValue($feed, $data);

    $nid = $this->g2Config->get('services.wotd.entry');
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->etm
      ->getStorage(G2::TYPE)
      ->load($nid);
    $entry = $feed->createEntry();
    $entry->setTitle($node->label());
    $entry->setLink($node->toUrl()->toString());
    $feed->addEntry($entry);

    $rss = $feed->export('rss');
    $xml = new XmlResponse($rss);
    return $xml;
  }

}
