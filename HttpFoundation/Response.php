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

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Extended response containing raw data/objects returned from Controllers
 */
class Response extends SymfonyResponse
{
    protected $result;

    public function setResult(array $result = null)
    {
        $this->result = $result;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function hasSingleResult()
    {
        return count($this->result) == 1;
    }

    public function getSingleResult()
    {
        return current($this->result);
    }
}