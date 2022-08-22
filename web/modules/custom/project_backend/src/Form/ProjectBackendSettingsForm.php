<?php

namespace Drupal\project_backend\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure settings for this site.
 */
class ProjectBackendSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'project_backend.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'project_backend_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config(static::SETTINGS);

    // Set the social media link fields
    $form['social']['facebook'] = [
      '#type' => 'url',
      '#default_value' => $config->get('project_backend.social.facebook'),
      '#title' => $this->t('Facebook link'),
    ];

    $form['social']['twitter'] = [
      '#type' => 'url',
      '#default_value' => $config->get('project_backend.social.twitter'),
      '#title' => $this->t('Twitter link'),
    ];

    $form['social']['linkedin'] = [
      '#type' => 'url',
      '#default_value' => $config->get('project_backend.social.linkedin'),
      '#title' => $this->t('Linked-in link'),
    ];

    $form['social']['youtube'] = [
      '#type' => 'url',
      '#default_value' => $config->get('project_backend.social.youtube'),
      '#title' => $this->t('Youtube link'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config(static::SETTINGS)
      ->set('project_backend.social.facebook', $form_state->getValue('facebook'))
      ->set('project_backend.social.twitter', $form_state->getValue('twitter'))
      ->set('project_backend.social.linkedin', $form_state->getValue('linkedin'))
      ->set('project_backend.social.youtube', $form_state->getValue('youtube'))
      ->save();
    // invalidate cache tags for the social media block
    Cache::invalidateTags(['social_media_block']);
    parent::submitForm($form, $form_state);
  }

}
