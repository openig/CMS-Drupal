<?php

namespace Drupal\openig_redirect_connect\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoginPageRedirectSubscriber implements EventSubscriberInterface {

  protected $currentUser;

  public function __construct(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
  }

  public static function getSubscribedEvents() {
    $events[KernelEvents::CONTROLLER][] = ['onController', 0];
    return $events;
  }

  public function onController(ControllerEvent $event) {
    $request = $event->getRequest();
    $path = $request->getPathInfo(); // ex: '/connexion'

    // Ne traiter que la route /connexion exacte.
    if ($path !== '/connexion') {
      return;
    }

    // Si utilisateur connecté, rediriger vers la racine.
    if ($this->currentUser->isAuthenticated()) {
      $response = new RedirectResponse('/');
      $event->setController(function() use ($response) {
        return $response;
      });
    }

    // Sinon laisser la route /connexion fonctionner normalement (page visible).
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
    );
  }
}
