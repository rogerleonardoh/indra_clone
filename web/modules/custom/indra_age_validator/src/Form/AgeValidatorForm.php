<?php

namespace Drupal\indra_age_validator\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AgeValidatorForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'age_validator_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['day'] = [
      '#type' => 'number',
      '#default_value' => !empty($form_state->getValue(['dob'])) ? $form_state->getValue(['dob']) : '',
      '#attributes' => [
        'placeholder' => '00',
      ],
      '#required' => TRUE,
      '#min' => 0,
      '#max' => 31,
      '#description' => 'Día',
      '#prefix' => '
        <div class="container age-validator-form">
          <div class="row justify-content-md-center">
            <div class="col-3">',
    ];
    $form['month'] = [
      '#type' => 'number',
      '#default_value' => !empty($form_state->getValue(['dob'])) ? $form_state->getValue(['dob']) : '',
      '#attributes' => [
        'placeholder' => '00',
      ],
      '#required' => TRUE,
      '#min' => 0,
      '#max' => 12,
      '#description' => 'Mes',
      '#prefix' => '
            </div>
            <div class="col-3">',
    ];
    $form['year'] = [
      '#type' => 'number',
      '#default_value' => !empty($form_state->getValue(['dob'])) ? $form_state->getValue(['dob']) : '',
      '#attributes' => [
        'placeholder' => '0000',
      ],
      '#required' => TRUE,
      '#min' => 1900,
      '#max' => 2020,
      '#description' => 'Año',
      '#prefix' => '
            </div>
            <div class="col-3">',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Entrar',
      '#prefix' => '
            </div>
            <div class="col-1">',
      '#suffix' => '
            </div>
          </div>
        </div>',
    ];
    $form['confirmation'] = [
      '#type' => 'checkbox',
      '#title' => 'Recordarme en este equipo',
      '#description' => 'No deberías seleccionar "recordar mis datos" si compartes este computador con menores de edad.',
      '#prefix' => '
        <div class="container remember">
          <div class="row justify-content-md-center">
            <div class="col-9">',
      '#suffix' => '
            </div>
          </div>
        </div>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();


    $date = \DateTime::createFromFormat('Y-m-d', $form_values['year'] . '-' . $form_values['month'] . '-' . $form_values['day']);
    $date = $date->modify('+18 year');
    $now = new \DateTime('now');

    if ($date > $now) {
      $form_state->setResponse(new TrustedRedirectResponse('https://www.tapintoyourbeer.com/age_check.cfm', 302));
    }
    else {
      $url = \Drupal\Core\Url::fromRoute('<front>')->toString();
      $response = new RedirectResponse($url);

      if ($form_values['confirmation']) {
        $_SESSION["session-allowed-age"] = 1;
      }
      else {
        $response->headers->setCookie(new Cookie('cookie-allowed-age', 1));
        $form_state->setResponse($response);
      }
    }
  }

}
