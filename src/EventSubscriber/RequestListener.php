<?php

namespace App\EventSubscriber;

use App\Entity\Link;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class RequestListener implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Environment twig environment
     */
    private $environment;

    public function __construct(EntityManagerInterface $em, Environment $env){
        $this->em = $em;
        $this->environment = $env;
    }

    /**
     * listen for redirects and increment the counter
     * @param ControllerArgumentsEvent $event
     */
    public function onControllerArguments(ControllerArgumentsEvent $event)
    {
        $namedArguments = $event->getRequest()->attributes->all();
        if (
            array_key_exists('_route', $namedArguments) &&
            $namedArguments['_route'] === 'redirect' &&
            $namedArguments['link'] instanceof Link
        ) {
            // this is a redirect, we need to increase the link analytics
            $link = $namedArguments['link'];
            $link->incrementCount();
            $this->em->persist($link);
            $this->em->flush();
            $this->em->refresh($link);
        }
    }

    /**
     * add a parameter to all of the twig templates
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $host = $event->getRequest()->getSchemeAndHttpHost();
        $this->environment->addGlobal('domain', $host.'/');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => ['onControllerArguments',128],
            KernelEvents::REQUEST => ['onKernelRequest',128],
        ];
    }
}