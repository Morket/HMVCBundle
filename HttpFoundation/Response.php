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
 *
 * Erik Duindam <erik.duindam@morket.com>
 */
class Response extends SymfonyResponse
{
    /**
     * @var array
     */
    protected $result;

    /**
     * Set Controller result. To support RedirectResponses, data is optional.
     *
     * @param array $result
     */
    public function setResult(array $result = null)
    {
        $this->result = $result;
    }

    /**
     * Get Controller result
     *
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Whether it contains an array with only one element
     *
     * @return bool
     */
    public function hasSingleResult()
    {
        return count($this->result) == 1;
    }

    /**
     * Return only element in result array
     *
     * @return mixed
     */
    public function getSingleResult()
    {
        return current($this->result);
    }
}