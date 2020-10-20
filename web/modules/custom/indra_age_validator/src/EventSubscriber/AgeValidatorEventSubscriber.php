<?php

namespace Drupal\indra_age_validator\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Allows manipulation of the response object when performing a redirect.
 */
class AgeValidatorEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onRespond'];
    return $events;
  }

  /**
   * Allows manipulation of the response object when performing a redirect.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The Event to process.
   */
  public function onRespond(FilterResponseEvent $event) {
    $ageVerified = FALSE;

    if ((isset($_SESSION["session-allowed-age"]) && $_SESSION["session-allowed-age"])
      || (isset($_COOKIE["cookie-allowed-age"]) && $_COOKIE["cookie-allowed-age"])) {
      $ageVerified = TRUE;
    }

    $skipUrls = [];
    $skipUrls[] = '/user';
    $skipUrls[] = '/user/*';
    $skipUrls[] = '/age-verification';
    $skipPaths = implode("\r\n", $skipUrls);

    $userRoles = \Drupal::currentUser()->getRoles();
    $currentPath = \Drupal::service('path.current')->getPath();
    $match = \Drupal::service('path.matcher')->matchPath($currentPath, $skipPaths);

    if (!$ageVerified) {
      if (!$match && !in_array('administrator', $userRoles)) {
        $redirect = new RedirectResponse('/age-verification');
        $event->setResponse($redirect);
      }
    }
    else {
      $skipUrls = [];
      $skipUrls[] = '/age-verification';
      $skipPaths = implode("\r\n", $skipUrls);
      $match = \Drupal::service('path.matcher')->matchPath($currentPath, $skipPaths);

      if ($match) {
        $url = \Drupal\Core\Url::fromRoute('<front>')->toString();
        $redirect = new RedirectResponse($url);;
        $event->setResponse($redirect);
      }
    }
  }

}
