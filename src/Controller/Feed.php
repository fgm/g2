<?php

declare(strict_types = 1);

namespace Drupal\g2\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\g2\G2;
use Drupal\node\NodeInterface;
use Laminas\Diactoros\Response\XmlResponse;
use Laminas\Feed\Writer\Feed as FeedWriter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Feed contains the RSS feed controller.
 */
class Feed implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The default G2 author in feeds.
   */
  const DEFAULT_AUTHOR = '@author';

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * The config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Autocomplete constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config.factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The entity_type.manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    EntityTypeManagerInterface $etm,
    LanguageManagerInterface $language_manager
  ) {
    $this->configFactory = $configFactory;
    $this->etm = $etm;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $configFactory */
    $configFactory = $container->get(G2::SVC_CONF);
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager */
    $entity_manager = $container->get(G2::SVC_ETM);
    $lm = $container->get('language_manager');

    return new static($configFactory, $entity_manager, $lm);
  }

  /**
   * Generate an RSS feed containing the latest WOTD.
   *
   * @return string
   *   XML in UTF-8 encoding
   */
  public function wotdAction() {
    $feed = new FeedWriter();
    $g2Config = $this->configFactory->get(G2::CONFIG_NAME);
    $sysConfig = $this->configFactory->get('system.site');

    $title = $g2Config->get(G2::VARWOTDFEEDTITLE);
    if (empty($title)) {
      $title = $sysConfig->get('name');
    }
    // setTitle throws an exception on empty titles.
    if (!empty($title)) {
      $feed->setTitle($title);
    }

    $feed->setLanguage($this->languageManager
      ->getCurrentLanguage()
      ->getId());

    // Link element: Drupal 4.7->7 default to $base url.
    // Drupal 8+ provides no default.
    $main = $g2Config->get(G2::VARMAINROUTE);
    $feed->setLink(Url::fromRoute($main)->toString());

    // Description: Drupal 4.7->7 defaults to $site_mission.
    // Drupal 8+ provides no default.
    $descr = $g2Config->get(G2::VARWOTDFEEDDESCR)
      ?: $this->t('One definition a day from the G2 Glossary on :site');
    $feed->setDescription(strtr($descr, [':site' => $GLOBALS['base_url']]));

    // Get the major Drupal version.
    [$coreVersion] = explode('.', \Drupal::VERSION);
    $feed->setGenerator(
      'Glossary 2 module for Drupal', $coreVersion,
      'https://www.drupal.org/project/g2',
    );

    // Laminas\Feed does not support setting the managingEditor,
    // not setting a property by name, so we need to hack it a bit.
    $rc = new \ReflectionClass($feed);
    $rp = $rc->getProperty('data');
    $rp->setAccessible(TRUE);
    $data = $rp->getValue($feed);
    $data['managingEditor'] = $sysConfig->get('mail');
    $rp->setValue($feed, $data);

    $nid = $g2Config->get(G2::VARWOTDENTRY);
    if (!empty($nid)) {
      /** @var \Drupal\node\Entity\Node $node */
      $node = $this->etm
        ->getStorage(G2::TYPE)
        ->load($nid);
      if ($node instanceof NodeInterface) {
        $entry = $feed->createEntry();
        $entry->setTitle($node->label());
        $entry->setLink($node->toUrl()->toString());
        $feed->addEntry($entry);
      }
    }

    $rss = $feed->export(G2::VM_RSS);
    $xml = new XmlResponse($rss);
    return $xml;
  }

}
