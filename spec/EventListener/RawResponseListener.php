<?php

namespace spec\Morket\Bundle\HMVCBundle\EventListener;

use PHPSpec2\ObjectBehavior;

class RawResponseListener extends ObjectBehavior
{
    /**
     * @param Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent $event
     * @param Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $exceptionEvent
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param Symfony\Component\HttpFoundation\ParameterBag $attributeBag
     * @param Morket\Bundle\HMVCBundle\HttpFoundation\Response $response
     * @param Morket\Bundle\HMVCBundle\HttpFoundation\ResponseFactory $responseFactory
     */
    function let($event, $exceptionEvent, $request, $attributeBag, $response, $responseFactory)
    {
        $request->attributes = $attributeBag;

        $event->getRequest()->willReturn($request);
        $exceptionEvent->getRequest()->willReturn($request);
        $responseFactory->createResponse()->willReturn($response);

        $this->beConstructedWith($responseFactory);
    }

    function its_kernel_view_should_return_null_when_its_not_a_hmvc_call($event, $attributeBag)
    {
        $attributeBag->get('_hmvc')->willReturn(false);
        $event->setResponse()->shouldNotBeCalled();

        $this->onKernelView($event)->shouldReturn(null);
    }

    function its_kernel_view_should_return_a_hmvc_response_object($event, $attributeBag, $response)
    {
        $attributeBag->get('_hmvc')->willReturn(true);
        $event->setResponse($response)->shouldBeCalled();

        $this->onKernelView($event);
    }

    function its_kernel_view_should_contain_the_controller_result($event, $attributeBag, $response)
    {
        $controllerResult = array('something' => 'something');

        $attributeBag->get('_hmvc')->willReturn(true);
        $event->getControllerResult()->willReturn($controllerResult);
        $event->setResponse($response)->shouldBeCalled();
        $response->setResult($controllerResult)->shouldBeCalled();

        $this->onKernelView($event);
    }

    function its_kernel_view_should_convert_view_to_data($event, $attributeBag, $response)
    {
        $view = new \FOS\RestBundle\View\View;
        $data = array('something' => 'somethingelse');
        $view->setData($data);

        $attributeBag->get('_hmvc')->willReturn(true);
        $event->getControllerResult()->willReturn($view);
        $event->setResponse($response)->shouldBeCalled();
        $response->setResult($data)->shouldBeCalled();

        $this->onKernelView($event);
    }

    function its_kernel_exception_should_return_null_when_its_not_a_hmvc_call($exceptionEvent, $attributeBag)
    {
        $attributeBag->get('_hmvc')->willReturn(false);
        $exceptionEvent->getException()->shouldNotBeCalled();

        $this->onKernelException($exceptionEvent);
    }

    function its_kernel_exception_should_throw_exception($exceptionEvent, $attributeBag)
    {
        $attributeBag->get('_hmvc')->willReturn(true);
        $exceptionEvent->getException()->willReturn(new \RuntimeException);

        $this->shouldThrow(new \RuntimeException)
             ->duringOnKernelException($exceptionEvent);
    }
}