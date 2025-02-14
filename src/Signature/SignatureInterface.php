<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 */

namespace Tmconsulting\Uniteller\Signature;

use Tmconsulting\Uniteller\Builder\BuilderInterface;

/**
 * Interface SignatureInterface
 *
 * @package Tmconsulting\Client
 */
interface SignatureInterface
{
    /**
     * Create signature
     *
     * @param \Tmconsulting\Uniteller\Builder\BuilderInterface $builder
     *
     * @return string
     */
    public function create(BuilderInterface $builder): string;

    /**
     * Verify signature
     *
     * @param string $signature
     *
     * @return bool
     */
    public function verify($signature);
}
