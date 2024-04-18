<?php

namespace Drupal\openig_adhesion\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a block with the simulator form
 *
 * @Block(
 *  id = "openig_adhesion_block",
 *  admin_label = @Translation("Simulator adhesion block")
 * )
 */
class SimulatorBlock extends BlockBase implements BlockPluginInterface {

    /**
     * {@inheritDoc}
     */
    public function build() {
        return [
            '#theme' => 'openig_adhesion_simulator',
            '#simulator_form' => \Drupal::formBuilder()->getForm('Drupal\openig_adhesion\Form\SimulatorForm')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state) {
        $form = parent::blockForm($form, $form_state);

        $config = $this->getConfiguration();

        $form['confirmation'] = array(
            '#type' => 'details',
            '#title' => 'Mail de confirmation par type d\'organisation',
            'type_1_email_title' => [
                '#type' => 'textfield',
                '#title' => 'Sujet du mail de confirmation pour les organismes publics :',
                '#default_value' => isset($config['type_1_email_title']) ? $config['type_1_email_title'] : '',
            ],
            'type_1_email_content' => [
                '#type' => 'textarea',
                '#title' => 'Contenu du mail de confirmation pour les organismes publics :',
                '#default_value' => isset($config['type_1_email_content']) ? $config['type_1_email_content'] : '',
                '#description' => "Variables : @type, @population, @simulation_result",
            ],
            'type_2_email_title' => [
                '#type' => 'textfield',
                '#title' => 'Sujet du mail de confirmation pour les organismes privés :',
                '#default_value' => isset($config['type_2_email_title']) ? $config['type_2_email_title'] : '',
            ],
            'type_2_email_content' => [
                '#type' => 'textarea',
                '#title' => 'Contenu du mail de confirmation pour les organismes privés :',
                '#default_value' => isset($config['type_2_email_content']) ? $config['type_2_email_content'] : '',
                '#description' => "Variables : @type, @salaries, @simulation_result",
            ],
            'type_3_email_title' => [
                '#type' => 'textfield',
                '#title' => 'Sujet du mail de confirmation pour les organismes à vocation SIG :',
                '#default_value' => isset($config['type_3_email_title']) ? $config['type_3_email_title'] : '',
            ],
            'type_3_email_content' => [
                '#type' => 'textarea',
                '#title' => 'Contenu du mail de confirmation pour les organismes à vocation SIG :',
                '#default_value' => isset($config['type_3_email_content']) ? $config['type_3_email_content'] : '',
                '#description' => "Variables : @type, @budget, @simulation_result",
            ],
            'type_4_email_title' => [
                '#type' => 'textfield',
                '#title' => 'Sujet du mail de confirmation pour les personnes morales/physiques :',
                '#default_value' => isset($config['type_4_email_title']) ? $config['type_4_email_title'] : '',
            ],
            'type_4_email_content' => [
                '#type' => 'textarea',
                '#title' => 'Contenu du mail de confirmation pour les personnes morales/physiques :',
                '#default_value' => isset($config['type_4_email_content']) ? $config['type_4_email_content'] : '',
                '#description' => "Variables : @type",
            ],
        );

        $form['notification'] = array(
            '#type' => 'details',
            '#title' => 'Mail de notification',
            'notification_emails' => [
                '#type' => 'textfield',
                '#title' => 'Notification à ces adresses emails :',
                '#description' => "Séparer les adresses avec des virgules",
                '#default_value' => isset($config['notification_emails']) ? $config['notification_emails'] : '',
            ],
            'notification_email_content' => [
                '#type' => 'textarea',
                '#title' => 'Contenu du mail de notification :',
                '#description' => "Variables : @type, @email, @population, @salaries, @budget, @simulation_result",
                '#default_value' => isset($config['notification_email_content']) ? $config['notification_email_content'] : '',
            ]
        );

        $form['openig_adhesion_simulator_formula_population'] = [
            '#type' => 'textfield',
            '#title' => 'Population - Montant par habitant :',
            '#description' => "Cotisation estimée à nb habitant * montant par habitant",
            '#default_value' => isset($config['openig_adhesion_simulator_formula_population']) ? $config['openig_adhesion_simulator_formula_population'] : '0.07',
        ];

        $form['openig_adhesion_simulator_formula_budget'] = [
            '#type' => 'textfield',
            '#title' => 'Pourcentage du budget',
            '#default_value' => isset($config['openig_adhesion_simulator_formula_budget']) ? $config['openig_adhesion_simulator_formula_budget'] : '0.05',
            '#description' => "Cotisation estimée à budget * pourcentage",
        ];

        $form['openig_adhesion_simulator_formula_salaries'] = array(
            '#type' => 'details',
            '#title' => 'Tranches par taille de l\'organisme',
            'openig_adhesion_simulator_formula_salaries_1' => [
                '#type' => 'textfield',
                '#title' => 'Salariés - Tranche < 20',
                '#default_value' => isset($config['openig_adhesion_simulator_formula_salaries_1']) ? $config['openig_adhesion_simulator_formula_salaries_1'] : '500',
            ],
            'openig_adhesion_simulator_formula_salaries_2' => [
                '#type' => 'textfield',
                '#title' => 'Salariés - Tranche >= 20 & <= 50',
                '#default_value' => isset($config['openig_adhesion_simulator_formula_salaries_2']) ? $config['openig_adhesion_simulator_formula_salaries_2'] : '1000',
            ],
            'openig_adhesion_simulator_formula_salaries_3' => [
                '#type' => 'textfield',
                '#title' => 'Salariés - Tranche >= 51 & <= 250',
                '#default_value' => isset($config['openig_adhesion_simulator_formula_salaries_3']) ? $config['openig_adhesion_simulator_formula_salaries_3'] : '1500',
            ],
            'openig_adhesion_simulator_formula_salaries_4' => [
                '#type' => 'textfield',
                '#title' => 'Salariés - Tranche >= 251 & <= 500',
                '#default_value' => isset($config['openig_adhesion_simulator_formula_salaries_4']) ? $config['openig_adhesion_simulator_formula_salaries_4'] : '2500',
            ],
            'openig_adhesion_simulator_formula_salaries_5' => [
                '#type' => 'textfield',
                '#title' => 'Salariés - Tranche >= 501 & <= 1000',
                '#default_value' => isset($config['openig_adhesion_simulator_formula_salaries_5']) ? $config['openig_adhesion_simulator_formula_salaries_5'] : '5000',
            ],
            'openig_adhesion_simulator_formula_salaries_6' => [
                '#type' => 'textfield',
                '#title' => 'Salariés - Tranche >= 1001 & <= 9999',
                '#default_value' => isset($config['openig_adhesion_simulator_formula_salaries_6']) ? $config['openig_adhesion_simulator_formula_salaries_6'] : '10000',
            ],
            'openig_adhesion_simulator_formula_salaries_7' => [
                '#type' => 'textfield',
                '#title' => 'Salariés - Tranche >= 10000',
                '#default_value' => isset($config['openig_adhesion_simulator_formula_salaries_7']) ? $config['openig_adhesion_simulator_formula_salaries_7'] : '15000',
            ]
        );

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state) {
        parent::blockSubmit($form, $form_state);
        $values = $form_state->getValues();

        // Mail de notification
        $this->configuration['notification_emails'] = $values['notification']['notification_emails'];
        $this->configuration['notification_email_content'] = $values['notification']['notification_email_content'];

        // Mail de confirmation
        $this->configuration['type_1_email_title'] = $values['confirmation']['type_1_email_title'];
        $this->configuration['type_1_email_content'] = $values['confirmation']['type_1_email_content'];
        $this->configuration['type_2_email_title'] = $values['confirmation']['type_2_email_title'];
        $this->configuration['type_2_email_content'] = $values['confirmation']['type_2_email_content'];
        $this->configuration['type_3_email_title'] = $values['confirmation']['type_3_email_title'];
        $this->configuration['type_3_email_content'] = $values['confirmation']['type_3_email_content'];
        $this->configuration['type_4_email_title'] = $values['confirmation']['type_4_email_title'];
        $this->configuration['type_4_email_content'] = $values['confirmation']['type_4_email_content'];

        // Configuration des formules
        $this->configuration['openig_adhesion_simulator_formula_population'] = $values['openig_adhesion_simulator_formula_population'];
        $this->configuration['openig_adhesion_simulator_formula_budget'] = $values['openig_adhesion_simulator_formula_budget'];
        $this->configuration['openig_adhesion_simulator_formula_salaries_1'] = $values['openig_adhesion_simulator_formula_salaries']['openig_adhesion_simulator_formula_salaries_1'];
        $this->configuration['openig_adhesion_simulator_formula_salaries_2'] = $values['openig_adhesion_simulator_formula_salaries']['openig_adhesion_simulator_formula_salaries_2'];
        $this->configuration['openig_adhesion_simulator_formula_salaries_3'] = $values['openig_adhesion_simulator_formula_salaries']['openig_adhesion_simulator_formula_salaries_3'];
        $this->configuration['openig_adhesion_simulator_formula_salaries_4'] = $values['openig_adhesion_simulator_formula_salaries']['openig_adhesion_simulator_formula_salaries_4'];
        $this->configuration['openig_adhesion_simulator_formula_salaries_5'] = $values['openig_adhesion_simulator_formula_salaries']['openig_adhesion_simulator_formula_salaries_5'];
        $this->configuration['openig_adhesion_simulator_formula_salaries_6'] = $values['openig_adhesion_simulator_formula_salaries']['openig_adhesion_simulator_formula_salaries_6'];
        $this->configuration['openig_adhesion_simulator_formula_salaries_7'] = $values['openig_adhesion_simulator_formula_salaries']['openig_adhesion_simulator_formula_salaries_7'];
    }
}
