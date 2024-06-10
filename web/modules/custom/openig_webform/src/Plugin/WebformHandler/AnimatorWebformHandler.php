<?php
namespace Drupal\openig_webform\Plugin\WebformHandler;

use Drupal\webform\Plugin\WebformHandler\EmailWebformHandler;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\user\Entity\User;

/**
 * Emails a webform submission.
 *
 * @WebformHandler(
 *   id = "email_pour_les_animateurs",
 *   label = @Translation("Email pour les animateurs"),
 *   category = @Translation("Notification"),
 *   description = @Translation("Sends a webform submission to a different animators per project group."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class AnimatorWebformHandler extends EmailWebformHandler {

    public function sendMessage(WebformSubmissionInterface $webform_submission, array $message) {
        $project_group = $webform_submission->getSourceEntity();
        $animators = array_column($project_group->field_animators->getValue(), 'target_id');

        $recipient = '';

        foreach($animators as $a) {
            $account = User::load($a);
            if ($recipient == '') {
                $recipient .= $account->getEmail();
            } else {
                $recipient .= ',' . $account->getEmail();
            }
        }

        $message['cc_mail'] = $recipient;

        parent::sendMessage($webform_submission, $message);
    }

}