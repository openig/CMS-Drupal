<?php

namespace Drupal\openig_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Our custom hero form.
 */
class SearchFilterForm extends FormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return "openig_search_filter";
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        // Full text search
        $fulltext = \Drupal::request()->query->get('fulltext');
        $form['fulltext'] = [
            '#type' => 'hidden',
            '#default_value' => $fulltext,
            '#attributes' => [
                'placeholder' => 'Recherchez ex: vue aérienne, PPRI...',
                'id'          => 'facet_fulltext_value'
            ]
        ];

        // Category search
        $category = \Drupal::request()->query->get('category');
        $form['category'] = [
            '#type' => 'hidden',
            '#maxlength' => 300,
            '#default_value' => $category,
            '#attributes' => [
                'id' => 'facet_category_value'
            ]
        ];

        // Source search
        $lineage = \Drupal::request()->query->get('lineage');
        $form['lineage'] = [
            '#type' => 'hidden',
            '#maxlength' => 300,
            '#default_value' => $lineage,
            '#attributes' => [
                'id' => 'facet_lineage_value'
            ]
        ];

        // Format search
        $resource_format = \Drupal::request()->query->get('resource_format');
        $form['resource_format'] = [
            '#type' => 'hidden',
            '#maxlength' => 300,
            '#default_value' => $resource_format,
            '#attributes' => [
                'id' => 'facet_resource_format_value'
            ]
        ];

        // Format search
//        $resource_data_type = \Drupal::request()->query->get('resource_data_type');
//        $form['resource_data_type'] = [
//            '#type' => 'hidden',
//            '#maxlength' => 300,
//            '#default_value' => $resource_data_type,
//            '#attributes' => [
//                'id' => 'facet_resource_data_type_value'
//            ]
//        ];


        $form['submit_desktop'] = [
          '#type' => 'submit',
          '#value' => $this->t('Valider'),
          '#attributes' => [
            'class' => ['site-search__desktop'],
          ],
        ];


        $form['reset'] = [
          '#type' => 'button',
          '#value' => $this->t('Réinitialiser'),
          '#attributes' => [
            'class'   => ['site-search__desktop'],
          ],
        ];


        $form['#cache']['contexts'][] = 'session';

        return $form;
    }


    public function reminderFormReset($form, &$form_state) {
      $form_state->setRebuild(FALSE);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $args = [ 'fulltext' => $form_state->getValue('fulltext'), 'page' => 0 ];

        // Bind category parameter if defined
        $category = $form_state->getValue('category');
        if ($category) { $args['category'] = $category; }

        // Bind source parameter if defined
        $lineage = $form_state->getValue('lineage');
        if ($lineage) {
            $args['lineage'] = $lineage;
        } else {
            $args['lineage'] = 'CKAN Datasets|OpenIG';
        }
        // Bind format parameter if defined
        $resource_format = $form_state->getValue('resource_format');
        if ($resource_format) { $args['resource_format'] = $resource_format; }

        // Bind content type parameter if defined
//        $resource_data_type = $form_state->getValue('resource_data_type');
//        if ($resource_data_type) { $args['resource_data_type'] = $resource_data_type; }

        $form_state->setRedirect('openig_search.results', $args);
    }

}
