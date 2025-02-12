<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 */

namespace Tmconsulting\Uniteller\Signature;

use Psr\Log\LoggerInterface;

/**
 * Interface SignatureInterface
 *
 * @package Tmconsulting\Client
 */
interface SignatureInterface
{
    /**
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null);

    /**
     * Create signature
     *
     * @param array|\Tmconsulting\Uniteller\ArraybleInterface $parameters
     *
     * @return string
     */
    public function create($parameters): string;

    /**
     * Verify signature
     *
     * @param string $signature
     *
     * @return bool
     */
    public function verify($signature);
}
