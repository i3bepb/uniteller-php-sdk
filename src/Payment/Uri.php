<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 */

namespace Tmconsulting\Uniteller\Payment;

/**
 * Class Uri
 */
class Uri
{
    /**
     * @var string
     */
    private $uri;

    /**
     * Uri constructor.
     *
     * @param string $uri
     */
    public function __construct(string $uri)
    {
        $this->uri = $uri;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @return void
     */
    public function go()
    {
        header(sprintf('Location: %s', $this->uri));
    }
}
