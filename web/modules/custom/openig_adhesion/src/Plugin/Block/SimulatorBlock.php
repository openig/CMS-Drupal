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
     * Options par défaut "Quel est le type de votre organisme" (avec montants par défaut).
     */
    private $default_options = [
        '1' => ['label' => 'Une commune', 'amount' => 100],
        '2' => ['label' => 'Un département', 'amount' => 500],
        '3' => ['label' => 'Une région', 'amount' => 500],
        '4' => ['label' => 'Une communauté de communes', 'amount' => 200],
        '5' => ['label' => 'Une communauté d\'agglomération', 'amount' => 400],
        '6' => ['label' => 'Une communauté urbaine', 'amount' => 200],
        '7' => ['label' => 'Une métropole', 'amount' => 400],
        '8' => ['label' => 'Un service déconcentré de l\'Etat', 'amount' => 500],
        '9' => ['label' => 'Un autre EPCI', 'amount' => 0],
        '10' => ['label' => 'Un autre organisme public', 'amount' => 0],
        '11' => ['label' => 'Un organisme privé', 'amount' => 0],
        '12' => ['label' => 'Une association', 'amount' => 0],
        '13' => ['label' => 'Un organisme à vocation SIG', 'amount' => 0],
        '14' => ['label' => 'Une personne morale [Adhésion de soutien]', 'amount' => 0],
        '15' => ['label' => 'Une personne physique [Adhésion de soutien à titre individuel]', 'amount' => 0],
    ];


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
                '#description' => "Variables : @type, @part_fixe_organisme, @population, @simulation_result, @part_variable_organisme_public",
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
                '#description' => "Variables : @type, @budget, @simulation_result, @part_variable_organisme_SIG",
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
                '#description' => "Variables : @type, @part_fixe",
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

        // Valeur parametrable => plafond et part fixe
        $form['openig_adhesion_simulator_formula_population_valeur_plafond'] = [
            '#type' => 'details',
            '#title' => 'Valeurs paramètrables',
            'openig_adhesion_simulator_formula_population_part_variable' => [
              '#type' => 'number',
              '#title' => 'Plafond de la part variable :',
              '#description' => "Montant du plafond de la part variable catégorie 1 (Etat, collectivités et EPCI à fiscalité propre).",
              '#default_value' => isset($config['openig_adhesion_simulator_formula_population_part_variable']) ? $config['openig_adhesion_simulator_formula_population_part_variable'] : '20 000',
            ],
            'openig_adhesion_simulator_formula_organisme_valeur_fixe' => [
              '#type' => 'number',
              '#title' => 'Montant de la part fixe :',
              '#description' => "Montant de la part fixe catégorie 3 (Personnes physiques).",
              '#default_value' => isset($config['openig_adhesion_simulator_formula_organisme_valeur_fixe']) ? $config['openig_adhesion_simulator_formula_organisme_valeur_fixe'] : '25',
            ],
            'openig_adhesion_simulator_formula_organisme_part_variable' => [
              '#type' => 'number',
              '#title' => 'Plafond de la part variable :',
              '#description' => "Montant du plafond de la part variable catégorie 4 (Organismes à « vocation » SI).",
              '#default_value' => isset($config['openig_adhesion_simulator_formula_organisme_part_variable']) ? $config['openig_adhesion_simulator_formula_organisme_part_variable'] : '25 000',
            ],
        ];

        // Ajout dynamique des options selecteur Type d'organisme (label + montant associé)
        $form['openig_adhesion_simulator_formula_type_organisme_options'] = [
            '#type' => 'details',
            '#title' => $this->t('Options du sélecteur Type d\'organisme'),
            '#tree' => TRUE,
            '#prefix' => '<div id="options-wrapper">',
            '#suffix' => '</div>',
        ];

        // Récupérer les valeurs existantes ou initialiser une structure vide
        $options = $config['openig_adhesion_simulator_formula_type_organisme_options'] ?? $this->default_options;
        // Stocker les options sous forme de structure dynamique
        $options_count = $form_state->get('options_count') ?? count($options);
        if ($form_state->getTriggeringElement()['#name'] === 'add_option') {
            $options_count++;
            $form_state->set('options_count', $options_count);
        }

        for ($i = 0; $i < $options_count; $i++) {
            $option_key = array_keys($options)[$i] ?? $i + 1;
            $form['openig_adhesion_simulator_formula_type_organisme_options'][$i] = [
                '#type' => 'container',
                '#attributes' => ['class' => ['group-organisme']],
                'label' => [
                  '#type' => 'textfield',
                  '#title' => $this->t('Type d\'organisme'),
                  '#default_value' => $options[$option_key]['label'] ?? '',
                  '#required' => true,
                  '#description' => "Label du type d'organisme",
                ],
                'amount' => [
                  '#type' => 'number',
                  '#title' => $this->t('Montant'),
                  '#default_value' => $options[$option_key]['amount'] ?? 0,
                  '#min' => 0,
                  '#description' => "Montant part fixe lié au type d'organisme",
                ],
            ];
        }
        /* Fin selecteur Type d'organisme */

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

        // Montant de la part variable (cat 1) - Organismes public
        $this->configuration['openig_adhesion_simulator_formula_population_part_variable'] = $values['openig_adhesion_simulator_formula_population_valeur_plafond']['openig_adhesion_simulator_formula_population_part_variable'];
        // Montant du plafond variable (cat 4) - Organismes à « vocation » SIG
        $this->configuration['openig_adhesion_simulator_formula_organisme_part_variable'] = $values['openig_adhesion_simulator_formula_population_valeur_plafond']['openig_adhesion_simulator_formula_organisme_part_variable'];
        // Montant de la part fixe (cat 3) - Personnes physiques
        $this->configuration['openig_adhesion_simulator_formula_organisme_valeur_fixe'] = $values['openig_adhesion_simulator_formula_population_valeur_plafond']['openig_adhesion_simulator_formula_organisme_valeur_fixe'];
        // Selecteur Type d'organisme
        $options = [];
        foreach ($form_state->getValue('openig_adhesion_simulator_formula_type_organisme_options') as $key => $option) {
            if (!empty($option['label'])) {
                $options[$key] = [
                  'label' => $option['label'],
                  'amount' => $option['amount'],
                ];
            }
        }
        $this->configuration['openig_adhesion_simulator_formula_type_organisme_options'] = $options;
    }
}
