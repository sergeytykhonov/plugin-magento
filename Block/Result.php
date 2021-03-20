<?php
/**
 * LiqPay Extension for Magento 2
 *
 * @author     Tykhonov Sergey
 * @copyright  Copyright (c) 2021 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */
declare(strict_types=1);

namespace LiqpayMagento\LiqPay\Block;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order;

class Result extends Template
{
    /**
     * @var string
     */
    protected $_template = 'checkout/result.phtml';

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    public function __construct(Template\Context $context, CheckoutSession $checkoutSession, array $data = [])
    {
        $this->checkoutSession = $checkoutSession;

        parent::__construct($context, $data);
    }

    /**
     * @return Order|null
     */
    public function getOrder(): ?Order
    {
        $order = $this->checkoutSession->getLastRealOrder();

        return $order && $order->getId() ? $order : null;
    }
}
