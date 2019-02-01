<?php declare(strict_types=1);

namespace DAPClientBundle\Security\Listener;

use DAPClientBundle\Model\User;
use DAPClientBundle\Security\UserProvider\ApiKeyUserProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class ApiKeySetCookieInResponseListener implements EventSubscriberInterface
{
    /**
     * @var User
     */
    private $currentUser;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN   => 'onInteractiveLogin',
            KernelEvents::RESPONSE              => 'setCookieInResponse',
        ];
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event) : void
    {
        $this->currentUser = $event->getAuthenticationToken()->getUser();

        $request = $event->getRequest();

        if (null !== $referer = $request->cookies->get('_referer')) {
            $request->query->set('_target_path', $referer);
            $request->cookies->remove('_referer');
        }
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function setCookieInResponse(FilterResponseEvent $event) : void
    {
        if (null === $this->currentUser ||
            !$event->getRequest()->attributes->has(ApiKeyUserProvider::COOKIE_ATTR_NAME)
        ) {
            return;
        }

        $event->getResponse()->headers->setCookie(
            new Cookie(
                ApiKeyUserProvider::COOKIE_ATTR_NAME,
                base64_encode(json_encode($this->currentUser->toArray())),
                new \DateTimeImmutable('+ 1 year'),
                '/',
                null,
                false,
                true,
                true
            )
        );
    }
}