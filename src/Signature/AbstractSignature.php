<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 */

namespace Tmconsulting\Uniteller\Signature;

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
     * @param \Tmconsulting\Uniteller\Common\Builder $parameters
     *
     * @return string
     */
    public function create($parameters): string
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
