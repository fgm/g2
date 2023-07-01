<?php

declare(strict_types = 1);

namespace Drupal\g2\Plugin\Filter;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\g2\G2;
use Drupal\g2\Matcher;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Automatic' filter.
 *
 * @Filter(
 *   id = "g2:automatic",
 *   title = @Translation("Automatically wrap G2 entries found in content with
 *   <code>&lt;dfn/&gt;</code> elements"), type =
 *   Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 *   settings = {
 *     "stop_list" = "",
 *   },
 *   description=@Translation("Automatically wrap recognized G2 entries in
 *   <code>&lt;dfn/&gt;</code> elements, except for those in your configured
 *   stop list. This filter is only useful along with the
 *   <code>&lt;dfn/&gt;</code> conversion filter, and <em>must</em> be applied
 *   before it."), weight = -1,
 * )
 */
class Automatic extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The name of the single settings for this filter.
   */
  const STOP = 'stop_list';

  /**
   * The logger.channel.g2 service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * The g2.matcher service.
   *
   * @var \Drupal\g2\Matcher
   */
  protected Matcher $matcher;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $logger = $container->get(G2::SVC_LOGGER);
    $matcher = $container->get(G2::SVC_MATCHER);

    return new static($configuration, $plugin_id, $plugin_definition, $matcher, $logger);
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    Matcher $matcher,
    LoggerInterface $logger,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger;
    $this->matcher = $matcher;
  }

  /**
   * {@inheritDoc}
   */
  public function prepare($text, $langcode): string {
    if (!Unicode::validateUtf8($text)) {
      $this->logger
        ->error($this->t('The text is apparently not valid UTF-8 charset: @text.', [
          '@text' => $text,
        ]));
      return $text;
    }
    $stopList = explode("\n",
      $this->getConfiguration()['settings'][self::STOP] ?? "");
    return Matcher::handleSource($text,
      $this->matcher->getMultiStringMatcher(),
      $stopList);
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    return new FilterProcessResult($text);
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('Some filter tips here.');
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $formState) {
    $form[static::STOP] = [
      '#type' => 'textarea',
      '#title' => $this->t('Stop list'),
      '#default_value' => $this->settings[static::STOP] ?? '',
      '#description' => $this->t('Enter G2 entries that must never be automatically wrapped in a &lt;dfn/&gt; element, one per line. You will still be able to define them by adding the &lt;dfn/&gt; element manually.'),
      '#element_validate' => [[$this, 'validateStopList']],
    ];
    return $form;
  }

  /**
   * Form element validation handler.
   *
   * Deduplicates and sorts entries.
   *
   * @param array $element
   *   The stop_list form element.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   */
  public function validateStopList(array &$element, FormStateInterface $formState) {
    // Web submissions add CR, just ignore them.
    $nocr = str_replace("\r", "", $element['#value']);
    $array = explode("\n", $nocr);
    $filtered = array_map("trim", $array);
    $nonEmpty = array_filter($filtered);
    sort($nonEmpty);
    $values = array_unique($nonEmpty);
    $value = implode("\n", $values);
    $formState->setValueForElement($element, $value);
    if ($value !== $nocr) {
      $this->messenger()
        ->addStatus("Your G2 stop list has been filtered and reordered.");
    }
  }

}
