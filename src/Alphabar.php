<?php

namespace Drupal\g2;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\node\NodeInterface;

/**
 * Class Alphabar provides a list of links to entries-by-initial pages.
 */
class Alphabar {
  /**
   * The config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $config;

  /**
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $etm;

  /**
   * The link generator service.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected LinkGeneratorInterface $linkGenerator;

  /**
   * The language.manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The entity_type.manager service.
   * @param \Drupal\Core\Utility\LinkGeneratorInterface $linkGenerator
   *   The link_generator service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language_manager service.
   */
  public function __construct(
    ConfigFactoryInterface $config,
    EntityTypeManagerInterface $etm,
    LinkGeneratorInterface $linkGenerator,
    LanguageManagerInterface $languageManager,
  ) {
    $this->config = $config;
    $this->etm = $etm;
    $this->languageManager = $languageManager;
    $this->linkGenerator = $linkGenerator;
  }

  /**
   * Return the alphabar.contents configuration.
   *
   * @return string
   *   The configured alphabar.contents
   */
  public function getContents(): string {
    $config = $this->config->get(G2::CONFIG_NAME);
    $contents = $config->get(G2::VARALPHABARCONTENTS);
    return $contents;
  }

  /**
   * Return the route configured for the "entries by initial" page.
   *
   * @return string
   *   The route name.
   */
  public function getInitialsRoute(): string {
    $config = $this->config->get(G2::CONFIG_NAME);
    $routeName = $config->get(G2::VARCONTROLLERINITIAL);
    return $routeName;
  }

  /**
   * Return an array of links to entries-by-initial pages.
   *
   * @return arraystring\Drupal\Core\GeneratedLink
   *   A hash of initials to entry pages.
   */
  public function getLinks(): array {
    $result = [];
    $options = [
      // So alphabar can be used outside site pages.
      'absolute' => TRUE,
      // To preserve the pre-encoded path.
      'html'     => TRUE,
    ];

    $initials = $this->getContents();
    $byInitial = $this->getInitialsRoute();

    for ($i = 0; $i < mb_strlen($initials); $i++) {
      $initial = mb_substr($initials, $i, 1);
      $path = G2::encodeTerminal($initial);
      $parameters = ['g2_initial' => $path];
      $url = Url::fromRoute($byInitial, $parameters, $options);
      $result[] = $this->linkGenerator->generate($initial, $url);
    }

    return $result;
  }

  /**
   * Build the alphabar configuration from the existing G2 entries.
   *
   * @return array
   *   A map of entry count by initial.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function fromEntries(): array {
    $storage = $this->etm
      ->getStorage(G2::TYPE);

    // @todo Replace by a paged query to avoid loading lots of nodes at once.
    $nids = $storage
      ->getQuery()
      ->condition('type', G2::BUNDLE)
      ->accessCheck()
      ->condition('status', NodeInterface::PUBLISHED)
      ->execute();
    if (empty($nids)) {
      return [];
    }
    $nodes = $storage->loadMultiple($nids);
    $initials = [];
    foreach ($nodes as $node) {
      $initial = mb_strtoupper(mb_substr($node->label(), 0, 1));
      $initials[$initial] = ($initials[$initial] ?? 0) + 1;
    }
    $langcode = $this->languageManager
      ->getCurrentLanguage(Language::TYPE_CONTENT)
      ->getId();
    $collator = new \Collator($langcode);
    uksort($initials, fn(string $a, string $b) => $collator->compare($a, $b));
    return $initials;
  }

}
