<?php
/**
 * LiqPay Extension for Magento 2
 *
 * @author     Volodymyr Konstanchuk http://konstanchuk.com
 * @copyright  Copyright (c) 2017 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace LiqpayMagento\LiqPay\Model;

use LiqpayMagento\LiqPay\Sdk\LiqPay;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\Logger as PaymentMethodLogger;
use Magento\Quote\Api\Data\CartInterface;

class Payment extends \Magento\Payment\Model\Method\AbstractMethod
{
    const METHOD_CODE = 'liqpaymagento_liqpay';

    /** @var string */
    protected $_code = self::METHOD_CODE;

    /** @var LiqPay */
    protected $_liqPay;

    /** @var bool */
    protected $_canCapture = true;

    /** @var bool */
    protected $_canVoid = true;

    /** @var bool */
    protected $_canUseForMultishipping = false;

    /** @var bool */
    protected $_canUseInternal = false;

    /** @var bool */
    protected $_isInitializeNeeded = true;

    /** @var bool */
    protected $_isGateway = true;

    /** @var bool */
    protected $_canAuthorize = false;

    /** @var bool */
    protected $_canCapturePartial = false;

    /** @var bool */
    protected $_canRefund = false;

    /** @var bool */
    protected $_canRefundInvoicePartial = false;

    /** @var bool */
    protected $_canUseCheckout = true;

    /** @var int|float|mixed */
    protected $_minOrderTotal = 0;

    /** @var string[] */
    protected $_supportedCurrencyCodes;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param Context                    $context
     * @param Registry                   $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory      $customAttributeFactory
     * @param PaymentHelper              $paymentData
     * @param ScopeConfigInterface       $scopeConfig
     * @param PaymentMethodLogger        $logger
     * @param UrlInterface               $urlBuilder
     * @param LiqPay                     $liqPay
     * @param array                      $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        PaymentHelper $paymentData,
        ScopeConfigInterface $scopeConfig,
        PaymentMethodLogger $logger,
        UrlInterface $urlBuilder,
        LiqPay $liqPay,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            null,
            null,
            $data
        );
        $this->_liqPay = $liqPay;
        $this->_supportedCurrencyCodes = $liqPay->getSupportedCurrencies();
        $this->_minOrderTotal = $this->getConfigData('min_order_total');
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * @param string $currencyCode
     *
     * @return bool
     *
     * @noinspection PhpMissingParamTypeInspection
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function canUseForCurrency($currencyCode)
    {
        return in_array($currencyCode, $this->_supportedCurrencyCodes, true);
    }

    /**
     * @param InfoInterface $payment
     * @param float         $amount
     *
     * @return $this|Payment
     *
     * @throws \Magento\Framework\Validator\Exception
     *
     * @noinspection PhpMissingParamTypeInspection
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function capture(InfoInterface $payment, $amount)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        $billing = $order->getBillingAddress();

        try {
            $payment->setTransactionId('liqpay-' . $order->getId())->setIsTransactionClosed(0);
        } catch (\Exception $e) {
            $this->debugData(['exception' => $e->getMessage()]);
            throw new \Magento\Framework\Validator\Exception(__('Payment capturing error.'));
        }

        return $this;
    }

    /**
     * @param CartInterface|null $quote
     *
     * @return bool
     *
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function isAvailable(CartInterface $quote = null)
    {
        if (!$this->_liqPay->getHelper()->isEnabled()) {
            return false;
        }

        $this->_minOrderTotal = $this->getConfigData('min_order_total');

        if ($quote && $quote->getBaseGrandTotal() < $this->_minOrderTotal) {
            return false;
        }

        return parent::isAvailable($quote);
    }
}
