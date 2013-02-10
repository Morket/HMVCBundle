<?php
/*
 * This file is part of the Morket HMVC package.
 *
 * (c) Morket <http://github.com/morket>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Morket\Bundle\HMVCBundle\EventListener;

use Morket\Bundle\HMVCBundle\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * The RawResponseListener class will populate Response objects with the raw returned data instead of
 * turning return data to HTML/JSON/XML/etc, so you can internally call controllers without worrying
 * about all the Symfony magic and serialization overkill.
 *
 * @author Erik Duindam <erik.duindam@morket.com>
 */
class RawResponseListener
{
    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get('_hmvc')) {
            return;
        }

        $response = new Response();

        $result = $event->getControllerResult();
        if (is_array($result)) {
            $response->setResult($result);
        } elseif ($result instanceof \FOS\RestBundle\View\View) {
            $response->setResult($result->getData());
        }

        $event->setResponse($response);
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get('_hmvc')) {
            return;
        }

        throw $event->getException();
    }
}
