<?php

namespace Drupal\openig_adhesion\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\block\Entity\Block;

class SimulatorForm extends FormBase {

    private $options;

    public function __construct() {
        $this->options = [
            '1' => 'Une commune',
            '2' => 'Un département',
            '3' => 'Une région',
            '4' => 'Une communauté de communes',
            '5' => 'Une communauté d\'agglomération',
            '6' => 'Une communauté urbaine',
            '7' => 'Une métropole',
            '8' => 'Un service déconcentré de l\'Etat',
            '9' => 'Un autre EPCI',
            '10' => 'Un autre organisme public',
            '11' => 'Un organisme privé',
            '12' => 'Une association',
            '13' => 'Un organisme à vocation SIG',
            '14' => 'Une personne morale [Adhésion de soutien]',
            '15' => 'Une personne physique [Adhésion de soutien à titre individuel]',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return "openig_adhesion_simulator_form";
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        // Organism type
        $form['organism_type'] = [
            '#type' => 'select',
            '#title' => 'Quel est le type de votre organisme ?',
            '#attributes' => array(
                'class' => array('organism_type'),
            ),
            '#options' => $this->options,
            '#required' => TRUE,
        ];

        $form['dynamic'] = array(
            '#type' => 'container',
            '#attributes' => array(
                'class' => array('adhesion-simulator-form__dynamic'),
            ),
        );

        // Population
        $form['dynamic']['population'] = [
            '#type' => 'textfield',
            '#title' => 'Population de l\'organisme représenté',
            '#prefix' => '
                <div id="population" class="adhesion-simulator-form__item adhesion-simulator-form__item--hidden">
                <div class="adhesion-simulator-form__label">La cotisation est fonction de la population de l’entité. Le tarif par habitant est de 0,07 €. Le plafond de la cotisation est fixé à 20 000 € et sans limite pour les organismes désireux de donner plus ou historiquement financeurs.</div>',
            '#suffix' => '
                <div class="adhesion-simulator-form__result">
                    Cotisation estimée à <span class="adhesion-simulator-form__value" id="simulator_type_1">...</span>
                </div></div>'
        ];

        // Salariés
        $form['dynamic']['salaries'] = [
            '#type' => 'textfield',
            '#title' => 'Nombre de salariés',
            '#prefix' => '
                <div id="salaries" class="adhesion-simulator-form__item adhesion-simulator-form__item--hidden">
                    <div class="adhesion-simulator-form__label">La cotisation est fonction du nombre de salariés de l’entité. L’effectif pris en compte est celui du groupe pour les entreprises privées.</div>',
            '#suffix' => '
                <div class="adhesion-simulator-form__result">
                    Cotisation estimée à <span class="adhesion-simulator-form__value" id="simulator_type_2">...</span>
                </div></div>',
        ];

        // Budget
        $form['dynamic']['budget'] = [
            '#type' => 'textfield',
            '#title' => 'Budget de l\'organisme année N-1',
            '#prefix' => '
                <div id="budget" class="adhesion-simulator-form__item adhesion-simulator-form__item--hidden">
                    <div class="adhesion-simulator-form__label">Cotisation proportionnelle à la somme des cotisations perçues par l’organisme l’année précédente, avec un pourcentage de 5% de ces cotisations. Le plafond est fixé à 25 000 €. L’adhésion dans cette catégorie permet à l’adhérent de faire bénéficier ses propres membres de l’ensemble des services d’OPenIG.</div>',
            '#suffix' => '
                <div class="adhesion-simulator-form__result">
                    Cotisation estimée à <span class="adhesion-simulator-form__value" id="simulator_type_3">...</span>
                </div></div>'
        ];

        // Individuel
        $form['dynamic']['individual'] = [
            '#markup' => '
            <div id="individual" class="adhesion-simulator-form__item adhesion-simulator-form__item--hidden"><div class="adhesion-simulator-form__markup">
                <div class="adhesion-simulator-form__title">Personnes individuelles</div>
                <div class="adhesion-simulator-form__label">Elles permettent à des personnes morales ou physiques d’adhérer à OPenIG avec un niveau de services limité. En revanche, les communes membres de droit (c’est-à-dire dont l’EPCI est adhérente) et désireuses d’apporter leur soutien, conservent logiquement un niveau de services complet. Ces cotisations permettent d’accéder aux instances de gouvernance selon les modalités fixées dans les statuts.</div>
                <div class="adhesion-simulator-form__result">
                    <div class="adhesion-simulator-form__option">Cotisation individuel à partir de <span class="adhesion-simulator-form__value" id="simulator_type_3">20€</span></div>
                    <div class="adhesion-simulator-form__option">Cotisation personne morale à partir de <span class="adhesion-simulator-form__value" id="simulator_type_3">250€</span></div>
                </div>
            </div></div>',
        ];

        // Email
        $form['dynamic']['email'] = [
            '#type' => 'email',
            '#title' => 'Sur quelle adresse email voulez-vous recevoir un devis ?',
            '#attributes' => [
                'placeholder' => 'contact@exemple.com'
            ],
            '#required' => TRUE,
            '#suffix' => '
                <div id="email_format_error" class="adhesion-simulator-form__error">
                </div>',
        ];

        // Captcha
        $form['captcha'] = [
            '#type' => 'captcha',
            '#captcha_type' => 'captcha/Math',
        ];

        $form['simulation_result'] = [
            '#type' => 'hidden',
            '#attributes' => [
                'id' => 'simulation_result'
            ]
        ];

        $form['submit'] = [
            '#prefix' => '<div class="form-actions">',
            '#type' => 'submit',
            '#value' => $this->t('Recevoir mon devis par email'),
            '#attributes' => [
                'class' => ['site-search__desktop'],
            ],
        ];

        $form['#cache']['contexts'][] = 'session';

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        // Bind values & send email
        $block_config = Block::load('simulatoradhesionblock');
        if ($block_config) {
            $settings = $block_config->get('settings');

            // Get email parameter if defined
            $email = $form_state->getValue('email');

            // Get organism_type parameter if defined
            $organism_type = $form_state->getValue('organism_type');

            // Get population parameter if defined
            $population = $form_state->getValue('population');

            // Get salaries parameter if defined
            $salaries = $form_state->getValue('salaries');

            // Get budget parameter if defined
            $budget = $form_state->getValue('budget');

            // Get result parameter if defined
            $simulation_result = $form_state->getValue('simulation_result');

            // Send confirmation email
            switch ($organism_type) {
                case '1':
                case '2':
                case '3':
                case '4':
                case '5':
                case '6':
                case '7':
                case '8':
                case '9':
                case '10':
                    $message = $settings['type_1_email_content'];
                    $message = str_replace('@type', $this->options[$organism_type], $message);
                    $message = str_replace('@population', $population, $message);
                    $message = str_replace('@simulation_result', $simulation_result, $message);
                    $this->sendMail($settings['type_1_email_title'], $message, $email);
                    break;
                case '11':
                case '12':
                    $message = $settings['type_2_email_content'];
                    $message = str_replace('@type', $this->options[$organism_type], $message);
                    $message = str_replace('@salaries', $salaries, $message);
                    $message = str_replace('@simulation_result', $simulation_result, $message);
                    $this->sendMail($settings['type_2_email_title'], $message, $email);
                    break;
                case '13':
                    $message = $settings['type_3_email_content'];
                    $message = str_replace('@type', $this->options[$organism_type], $message);
                    $message = str_replace('@budget', $budget, $message);
                    $message = str_replace('@simulation_result', $simulation_result, $message);
                    $this->sendMail($settings['type_3_email_title'], $message, $email);
                    break;
                case '14':
                case '15':
                    $message = $settings['type_4_email_content'];
                    $message = str_replace('@type', $this->options[$organism_type], $message);
                    $message = str_replace('@population', $population, $message);
                    $message = str_replace('@simulation_result', $simulation_result, $message);
                    $this->sendMail($settings['type_4_email_title'], $message, $email);
                    break;
            }

            // Build message
            $message = $settings['notification_email_content'];
            if ($email) { $message = str_replace('@email', $email, $message); }
            if ($organism_type) { $message = str_replace('@type', $this->options[$organism_type], $message); }
            if ($population) { $message = str_replace('@population', $population, $message); }
            if ($salaries) { $message = str_replace('@salaries', $salaries, $message); }
            if ($budget) { $message = str_replace('@budget', $budget, $message); }
            if ($simulation_result) { $message = str_replace('@simulation_result', $simulation_result, $message); }

            // Send notification email
            $params = [
                'subject' => 'OPenIG : Demande de devis',
                'message' => $message
            ];

            $mailManager = \Drupal::service('plugin.manager.mail');
            $message = $mailManager->mail('openig_adhesion', 'openig_adhesion_simulator_submit', $settings['notification_emails'], 'fr', $params, NULL, TRUE);
            if ($message['result']) {
                \Drupal::messenger()->addMessage('Votre demande de devis a bien été envoyé.');
                \Drupal::logger('openig_adhesion')->info('Mail send to : @response', [ '@response' => print_r($settings['notification_emails'], true) ]);
            } else {
                \Drupal::logger('openig_adhesion')->error('Unable to send mail, message: @message', ['@message' => print_r($message, true)]);
            }

        }

        $form_state->setRedirect('entity.node.canonical', [ 'node' => 239 ]);
    }

    private function sendMail($subject, $message, $email) {
        $params = [
            'subject' => $subject,
            'message' => $message
        ];

        $mailManager = \Drupal::service('plugin.manager.mail');
        return $mailManager->mail('openig_adhesion', 'openig_adhesion_simulator_submit', $email, 'fr', $params, NULL, TRUE);
    }
}
