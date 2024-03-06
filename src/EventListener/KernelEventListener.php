<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\EventListener;

use HeimrichHannot\FilterBundle\Controller\FrontendAjaxController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class KernelEventListener implements EventSubscriberInterface
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (FrontendAjaxController::ROUTE_NAME_AJAX === $request->attributes->get('_route')) {
            if ($request->query->has('_locale')) {
                $locale = $request->query->get('_locale');
                $request->setLocale($locale);

                if ($this->translator instanceof LocaleAwareInterface) {
                    try {
                        $this->translator->setLocale($locale);
                    } catch (\InvalidArgumentException $e) {
                        $this->translator->setLocale($request->getDefaultLocale());
                    }
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 10]],
        ];
    }
}
