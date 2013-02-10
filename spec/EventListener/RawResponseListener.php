<?php

namespace spec\Morket\Bundle\HMVCBundle\EventListener;

use PHPSpec2\ObjectBehavior;

class RawResponseListener extends ObjectBehavior
{
    /**
     * @param Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent $event
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param Symfony\Component\HttpFoundation\ParameterBag $attributeBag
     * @param Morket\Bundle\HMVCBundle\HttpFoundation\Response $response
     * @param Morket\Bundle\HMVCBundle\HttpFoundation\ResponseFactory $responseFactory
     */
    function let($event, $request, $attributeBag, $response, $responseFactory)
    {
        $request->attributes = $attributeBag;
        $event->getRequest()->willReturn($request);
        $event->getControllerResult()->willReturn(array('something' => 'something'));

        $responseFactory->createResponse()->willReturn($response);
        $this->beConstructedWith($responseFactory);
    }

    function it_should_return_null_when_its_not_a_hmvc_call($event, $attributeBag, $response)
    {
        $attributeBag->get('_hmvc')->willReturn(false);
        $event->setResponse()->shouldNotBeCalled();
        $this->onKernelView($event)->shouldReturn(null);
    }

    function it_should_return_a_hmvc_response_object($event, $attributeBag, $response)
    {
        $attributeBag->get('_hmvc')->willReturn(true);
        $event->setResponse($response)->shouldBeCalled();

        $this->onKernelView($event);
    }

    function it_should_contain_the_controller_result($event, $attributeBag, $response)
    {
        $attributeBag->get('_hmvc')->willReturn(true);
        $event->setResponse($response)->shouldBeCalled();
        $response->setResult(array('something' => 'something'))->shouldBeCalled();

        $this->onKernelView($event);
    }
}