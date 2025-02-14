<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 */

namespace Tmconsulting\Uniteller;

use Tmconsulting\Uniteller\Order\Order;

/**
 * Interface ClientInterface
 *
 * @package Tmconsulting\Client
 */
interface ClientInterface
{
    /**
     * @param \Tmconsulting\Uniteller\Payment\PaymentBuilder|array $paymentBuilder
     *
     * @return \Tmconsulting\Uniteller\Payment\UriInterface
     */
    public function payment($paymentBuilder);

    /**
     * @param \Tmconsulting\Uniteller\Cancel\CancelBuilder|array $parameters
     * @return Order
     */
    public function cancel($parameters);

    /**
     * @param \Tmconsulting\Uniteller\Results\ResultsBuilder|array $resultsBuilder
     *
     * @return Order
     */
    public function results($resultsBuilder);

    /**
     * @param array $parameters
     * @return mixed
     */
    public function recurrent($parameters);

    /**
     * @param array $parameters
     * @return mixed
     */
    public function confirm($parameters);

    /**
     * @param array $parameters
     * @return mixed
     */
    public function card($parameters);

    /**
     * Verify signature when Client will be send callback request.
     *
     * @param array $params
     * @return bool
     */
    public function verifyCallbackRequest(array $params);
}
