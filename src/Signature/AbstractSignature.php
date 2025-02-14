<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 */

namespace Tmconsulting\Uniteller\Signature;

use Tmconsulting\Uniteller\Builder\BuilderInterface;

/**
 * Class Signature
 *
 * @package Tmconsulting\Client
 */
abstract class AbstractSignature implements SignatureInterface
{
    /**
     * Create signature
     *
     * @param \Tmconsulting\Uniteller\Builder\BuilderInterface $builder
     *
     * @return string
     */
    public function create(BuilderInterface $builder): string
    {
        $string = implode('&', array_map(static function ($item) {
            return md5($item ?? '');
        }, $this->toArray()));

        return strtoupper(md5($string));
    }

    /**
     * Verify signature
     *
     * @param string $signature
     * @return bool
     */
    public function verify($signature)
    {
        return $this->create() === $signature;
    }
}
