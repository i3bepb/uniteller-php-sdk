<?php

namespace Tmconsulting\Uniteller\Payment;

class PaymentLink
{
    /**
     * @var string
     */
    private $url;

    /**
     * PaymentLink constructor.
     *
     * @param string $uri
     */
    public function __construct(string $uri)
    {
        $this->url = $uri;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return void
     */
    public function go()
    {
        header(sprintf('Location: %s', $this->url));
    }
}
