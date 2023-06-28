<?php

namespace Drupal\g2;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\g2\Exception\RandomException;
use Drupal\node\NodeInterface;
use Psr\Log\LoggerInterface;

/**
 * Class WOTD provides data for the WOTD block and API.
 */
class WOTD {

  use StringTranslationTrait;

  /**
   * The format of the last WOTD timestamp in state.
   */
  const DATE_STORAGE_FORMAT = DATE_RFC3339;

  /**
   * The format under which dates are compared for WOTD rotation.
   */
  const DATE_COMPARISON_FORMAT = 'Y-m-d';

  /**
   * The Epoch in RFC3339 format.
   */
  const EPOCH_RFC3339 = '1970-01-01T00:00:00+00:00';

  /**
   * RX_TITLE matches the optional nid in an autocomplete entry like "foo (24)".
   */
  const RX_TITLE = '/\((\d+)\)\s*$/';

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
  protected EntityTypeManagerInterface $etm;

  /**
   * The g2.random service.
   *
   * @var \Drupal\g2\Random
   */
  protected Random $random;

  /**
   * The datetime.time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected TimeInterface $time;

  /**
   * The logger.channel.g2 service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * Random constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config.factory service.
   * @param \Drupal\Core\Database\Connection $db
   *   The database service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The entity_type.manager server.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.channel.g2 service.
   * @param \Drupal\g2\Random $random
   *   The g2.random service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The datetime.time service.
   */
  public function __construct(
    ConfigFactoryInterface $config,
    Connection $db,
    EntityTypeManagerInterface $etm,
    LoggerInterface $logger,
    Random $random,
    StateInterface $state,
    TimeInterface $time,
  ) {
    $this->config = $config;
    $this->db = $db;
    $this->etm = $etm;
    $this->logger = $logger;
    $this->random = $random;
    $this->state = $state;
    $this->time = $time;
  }

  /**
   * Helper function to read configuration.
   *
   * @return bool
   *   Is services.wotd.auth_change true ?
   */
  public function isAutoChangeEnabled(): bool {
    return (bool) $this->config
      ->get(G2::CONFIG_NAME)
      ->get(G2::VARWOTDAUTOCHANGE);
  }

  /**
   * Apply WOTD auto change if the current day change since it was last changed.
   */
  public function autoChange(): void {
    $previousStored = (string) $this->state->get(G2::VARWOTDDATE) ?: static::EPOCH_RFC3339;
    $previousDTI = \DateTimeImmutable::createFromFormat(static::DATE_STORAGE_FORMAT, $previousStored);
    $todayDTI = \DateTimeImmutable::createFromFormat("U", $this->time->getRequestTime());
    $previous = $previousDTI->format(static::DATE_COMPARISON_FORMAT);
    $today = $todayDTI->format(static::DATE_COMPARISON_FORMAT);
    if ($today !== $previous) {
      $todayStored = $todayDTI->format(static::DATE_STORAGE_FORMAT);
      try {
        $random = $this->random->get();
        $this->logger->info('WOTD auto-changed to "@title" (@nid)"', [
          '@title' => $random->label(),
          '@nid' => $random->id(),
          'link' => $random->toLink($this->t("view"))->toString(),
        ]);
        $this->config
          ->getEditable(G2::CONFIG_NAME)
          ->set(G2::VARWOTDENTRY, (int) $random->id())
          ->save();
        $this->state->set(G2::VARWOTDDATE, $todayStored);
      }
      catch (RandomException $e) {
        $link = Link::createFromRoute($this->t("check G2 entries"),
          G2::ROUTE_ADMIN_CONTENT,
          [],
          ['query' => ['type' => G2::BUNDLE]],
        )->toString();
        watchdog_exception(G2::NAME, $e, NULL, [], RfcLogLevel::ERROR, $link);
      }
    }
  }

  /**
   * Implements hook_cron().
   */
  public function cron() {
    if ($this->isAutoChangeEnabled()) {
      $this->autoChange();
    }
  }

  /**
   * Get the WOTD.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The daily WOTD node, or NULL if none is set.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function get(): ?NodeInterface {
    $nid = (int) $this->config
      ->get(G2::CONFIG_NAME)
      ->get(G2::VARWOTDENTRY);
    if (empty($nid)) {
      return NULL;
    }

    return $this->etm
      ->getStorage(G2::TYPE)
      ->load($nid);
  }

  /**
   * Return the title of the node completed by its nid in parentheses.
   *
   * This is meant to be used on input fields, not on displays, because it is
   * not escaped.
   *
   * @param \Drupal\node\NodeInterface|null $node
   *   The node to label.
   *
   * @return string
   *   The title like "Some title (62)".
   */
  public static function numberedTitleInput(?NodeInterface $node): string {
    if (empty($node) || $node->id() === 0) {
      return '';
    }

    // !title: we don't filter since this is input, not output,
    // and can contain normally escaped characters, to accommodate
    // entries like "<", "C#" or "AT&T"
    $title = strtr('!title (@nid)',
      ['!title' => $node->label(), '@nid' => $node->id()]
    );
    return $title;
  }

  /**
   * Return the nodes matching an autocomplete title pattern like "foo (62)".
   *
   * @param string $title
   *   The pattern to match.
   *
   * @return \Drupal\node\NodeInterface[]
   *   The matching nodes.
   */
  public function matchesFromTitle(string $title): array {
    [$qTitle, $qNid] = [$title, 0];
    // Capture the optional parenthesized nid and remove it if found.
    $ok = (bool) preg_match(static::RX_TITLE, $title, $matches, PREG_OFFSET_CAPTURE, 0);
    if ($ok && count($matches) === 2) {
      $qNid = (int) $matches[1][0];
      // We matched a non-captures opening parenthese, so exclude it.
      $qTitle = trim(mb_substr($title, 0, $matches[1][1] - 1));
    }
    $storage = $this->etm
      ->getStorage(G2::TYPE);
    if ($qNid !== 0) {
      $nids = [$qNid];
    }
    else {
      $nids = $storage
        ->getQuery()
        ->condition('title', "${qTitle}%", 'LIKE')
        ->condition('type', G2::BUNDLE)
        ->condition('status', NodeInterface::PUBLISHED)
        ->accessCheck()
        ->execute();
      $nids = array_map('intval', $nids);
    }
    /** @var \Drupal\node\NodeInterface[] $nodes */
    $nodes = $storage->loadMultiple($nids);
    return $nodes;
  }

}
