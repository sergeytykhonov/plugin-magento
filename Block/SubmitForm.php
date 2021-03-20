<?php
/**
 * LiqPay Extension for Magento 2
 *
 * @author     Volodymyr Konstanchuk http://konstanchuk.com
 * @copyright  Copyright (c) 2017 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */
declare(strict_types=1);

namespace LiqpayMagento\LiqPay\Block;

use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order;
use LiqpayMagento\LiqPay\Helper\Data as Helper;
use LiqpayMagento\LiqPay\Sdk\LiqPay;

class SubmitForm extends Template
{
    /** @var Order|null */
    protected $_order = null;

    /* @var LiqPay */
    protected $_liqPay;

    /* @var Helper */
    protected $_helper;

    /**
     * @param Template\Context $context
     * @param LiqPay           $liqPay
     * @param Helper           $helper
     * @param array            $data
     */
    public function __construct(
        Template\Context $context,
        LiqPay $liqPay,
        Helper $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_liqPay = $liqPay;
        $this->_helper = $helper;
    }

    /**
     * @return Order
     *
     * @throws \Exception
     */
    public function getOrder(): Order
    {
        if ($this->_order === null) {
            throw new \Exception('Order is not set');
        }

        return $this->_order;
    }

    /**
     * @param Order $order
     */
    public function setOrder(Order $order)
    {
        $this->_order = $order;
    }

    /**
     * @return string|bool
     */
    protected function _loadCache()
    {
        return false;
    }

    /**
     * @return string
     *
     * @throws \Exception
     *
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function toHtml()
    {
        $order = $this->getOrder();

        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $html = $this->_liqPay->cnb_form([
            'action'      => 'pay',
            'amount'      => $order->getGrandTotal(),
            'currency'    => $order->getOrderCurrencyCode(),
            'description' => $this->_helper->getLiqPayDescription($order),
            'order_id'    => $order->getIncrementId(),
            'server_url'  => $this->getUrl('liqpay/liqpay/callback'),
            'result_url'  => $this->_helper->getResultUrl(),
        ]);

        return $html;
    }
}
