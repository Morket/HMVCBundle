<?php
/*
 * This file is part of the Morket HMVC package.
 *
 * (c) Morket <http://github.com/morket>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Morket\Bundle\HMVCBundle\HttpFoundation;

/**
 * Extended response containing raw data/objects returned from Controllers
 */
class ResponseFactory
{
    /**
     * Create new response object
     * 
     * @return \Morket\Bundle\HMVCBundle\HttpFoundation\Response
     */
    public function createResponse()
    {
        return new Response;
    }
}