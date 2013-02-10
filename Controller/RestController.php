<?php
/*
 * This file is part of the Morket HMVC package.
 *
 * (c) Morket <http://github.com/morket>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Morket\Bundle\HMVCBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;

abstract class RestController extends FOSRestController
{
    use HMVC;
}