<?php

namespace Drupal\openig_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Module settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openig_search_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'openig_search.settings',
    ];
  }

 /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);
    // Default settings.
    $config = $this->config('openig_search.settings');

    $form['external_search_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable external search'),
      '#description' => $this->t('Uncheck this if you disable search on external data (IDGO).'),
      '#default_value' => $config->get('external_search_enabled', TRUE),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('openig_search.settings');
    $config->set('external_search_enabled', $form_state->getValue('external_search_enabled'));
    $config->save();
    return parent::submitForm($form, $form_state);
  }
}
