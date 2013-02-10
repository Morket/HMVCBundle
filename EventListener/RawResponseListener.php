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
use Morket\Bundle\HMVCBundle\HttpFoundation\ResponseFactory;

use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

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
     * @var \Morket\Bundle\HMVCBundle\HttpFoundation\ResponseFactory
     */
    protected $responseFactory;

    /**
     * @param \Morket\Bundle\HMVCBundle\HttpFoundation\ResponseFactory $responseFactory
     */
    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
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

        $response = $this->responseFactory->createResponse();

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
