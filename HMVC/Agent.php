<?php
/*
 * This file is part of the Morket HMVC package.
 *
 * (c) Morket <http://github.com/morket>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Morket\Bundle\HMVCBundle\HMVC;

use Symfony\Bundle\FrameworkBundle\HttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Routing\Router;


/**
 * Each HMVC 'module' is called a triad. Triad's interact via their Agents. In this Bundle we use the Agent
 * to make inter-triad calls.
 *
 * @author Erik Duindam <erik.duindam@morket.com>
 */
class Agent
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\HttpKernel
     */
    protected $httpKernel;

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router
     * @param \Symfony\Bundle\FrameworkBundle\HttpKernel $httpKernel
     */
    public function __construct(Request $request, Router $router, HttpKernel $httpKernel)
    {
        $this->request = $request;
        $this->router = $router;
        $this->httpKernel = $httpKernel;
    }

    /**
     * Call a controller action internally and get the data returned like it was returned by the controller.
     * If an array with only one item was returned by the controller, you'll get that value immediately.
     * You could set the $rawResponse flag to get the entire Response object.
     *
     * The main difference with just instantiating a controller and calling the action directly is the fact
     * that this HMVC call will actually trigger all events and listeners and will use the normal Symfony
     * flow.
     *
     * It's still a pretty lightweight call though. If you would want to boost performance even more,
     * extend the RawResponseListener found in this Bundle and stop propagation of all events when making
     * an HMVC call, blocking all other listeners for internal calls. Stopping propagation is done with
     * native Symfony code, by calling $event->stopPropagation().
     *
     * @param string $route
     * @param array $attributes
     * @param array $data
     * @param array $query
     * @param bool $rawResponse
     * @return mixed
     */
    public function call($route, $attributes = array(), $data = array(), $query = array(), $rawResponse = false)
    {
        $defaults = $this->router->getRouteCollection()->get($route)->getDefaults();

        $controller = $defaults['_controller'];
        $attributes['_hmvc'] = $controller;
        $attributes['_controller'] = $controller;

        $subRequest = $this->request->duplicate($query, $data, $attributes);

        $response = $this->httpKernel->handle($subRequest, \Symfony\Component\HttpKernel\HttpKernelInterface::SUB_REQUEST);

        if ($rawResponse || !$response instanceof \Morket\Bundle\HMVCBundle\HttpFoundation\Response) {
            return $response;
        }

        if ($response->hasSingleResult()) {
            return $response->getSingleResult();
        }

        return $response->getResult();
    }
}