<?php

namespace Drupal\nrgi_frontend\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class NrgiSearchFrom.
 */
class NrgiSearchForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'nrgi_search_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['text_field'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'id' => 'nrgi-search-input',
        'class' => ['c-header-search__input js-header-search__input'],
      ],
      '#placeholder' => $this->t('Type to search'),
    ];

    $form['toggle_search'] = [
      '#type' => 'button',
      '#value' => '',
      '#attributes' => [
        'id' => 'nrgi-search-open',
        'class' => ['c-header-search__toggle js-header-search__toggle'],
      ],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('search'),
      '#attributes' => [
        'id' => 'nrgi-search-button',
        'class' => ['c-header-search__submit'],
        'data-twig-suggestion' => 'header_search',
      ],
    ];
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $search_value = $form_state->getValue('text_field');
    $route = 'view.search_view.search';
    $query = ['query' => $search_value];

    $url = Url::fromRoute($route, [], ['query' => $query]);
    $form_state->setRedirectUrl($url);
  }

}
