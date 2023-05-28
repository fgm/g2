<?php

namespace Drupal\g2\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\g2\G2;

/**
 * Class RefererWipe contains the referer wipe confirmation form.
 *
 * It offers to clear referers for all entries or one entry.
 */
class RefererWipe extends ConfirmFormBase {
  /**
   * Title of the referer wipeout page, and associated submit button.
   */
  const TITLE = 'Wipe all G2 referer information';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    $match = $this->getRouteMatch();
    $variant = $match->getParameter('variant');
    $result = 'g2_wipe-' . $variant;
    return $result;
  }

  /**
   * Form builder for the "wipe all" case.
   *
   * @param array $form
   *   The initial form.
   *
   * @return array
   *   The enriched form.
   */
  public function wipeAll(array $form) {
    // ["g2_referer_wipe_confirm_form"]
    return $form;
  }

  /**
   * Form builder for the "wipe one" case.
   *
   * @param array $form
   *   The initial form.
   *
   * @return array
   *   The enriched form.
   */
  public function wipeOne(array $form) {
    // ['G2::referer_wipe_confirm_form', 2]
    $form['node'] = [
      '#markup' => '<p>'
      . $this->t('Wipe reference on single node')
      . '</p>',
    ];
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, RouteMatchInterface $route = NULL): array {
    switch ($route->getRouteName()) {
      case G2::ROUTE_WIPE_ALL:
        $form = $this->wipeAll($form);
        break;

      case G2::ROUTE_WIPE_ONE:
        $form = $this->wipeOne($form);
        break;

      default:
        throw new \DomainException("Unknown route for the RefererWipe form");
    }

    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // @fixme Implement submitForm() method.
    $this->messenger()->addStatus("Referers wiped out");
    $form_state->setRedirect(G2::ROUTE_MAIN);
  }

  /**
   * {@inheritDoc}
   */
  public function getCancelText(): TranslatableMarkup {
    // @todo Implement method.
    $pt = new TranslatableMarkup("Cancel erasure");
    return $pt;
  }

  /**
   * {@inheritDoc}
   */
  public function getConfirmText(): TranslatableMarkup {
    // @todo Implement method.
    $ct = new TranslatableMarkup("Confirm erasure");
    return $ct;
  }

  /**
   * {@inheritDoc}
   */
  public function getDescription(): TranslatableMarkup {
    // @todo Implement method.
    $d = parent::getDescription();
    $d = new TranslatableMarkup("Do you really want to erase referrers? This action cannot be undone.");
    return $d;
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestion(): TranslatableMarkup {
    // @todo Implement getQuestion() method.
    return $this->t('Question?');
  }

  /**
   * {@inheritDoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute(G2::ROUTE_MAIN);
  }

}
