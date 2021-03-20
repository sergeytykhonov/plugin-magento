<?php
/**
 * LiqPay Extension for Magento 2
 *
 * @author     Volodymyr Konstanchuk http://konstanchuk.com
 * @copyright  Copyright (c) 2017 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace LiqpayMagento\LiqPay\Helper;

use LiqpayMagento\LiqPay\Model\Payment as LiqPayPayment;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    protected const XML_PATH_IS_ENABLED  = 'payment/liqpaymagento_liqpay/active';
    protected const XML_PATH_PUBLIC_KEY  = 'payment/liqpaymagento_liqpay/public_key';
    protected const XML_PATH_PRIVATE_KEY = 'payment/liqpaymagento_liqpay/private_key';
    protected const XML_PATH_TEST_MODE   = 'payment/liqpaymagento_liqpay/sandbox';
    protected const XML_PATH_TEST_ORDER_SUFFIX = 'payment/liqpaymagento_liqpay/sandbox_order_surfix';
    protected const XML_PATH_DESCRIPTION = 'payment/liqpaymagento_liqpay/description';
    protected const XML_PATH_CALLBACK_SECURITY_CHECK = 'payment/liqpaymagento_liqpay/security_check';
    protected const XML_PATH_RESULT_URL = 'payment/liqpaymagento_liqpay/result_url';

    /**
     * @var PaymentHelper
     */
    protected $_paymentHelper;

    /**
     * @param Context       $context
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(Context $context, PaymentHelper $paymentHelper)
    {
        parent::__construct($context);

        $this->_paymentHelper = $paymentHelper;
    }

    // <editor-fold desc="Settings">

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        if (!$this->scopeConfig->isSetFlag(static::XML_PATH_IS_ENABLED, ScopeInterface::SCOPE_STORE)) {
            return false;
        }

        if ($this->getPublicKey() && $this->getPrivateKey()) {
            return true;
        }

        $this->_logger->error(
            __('The LiqpayMagento\LiqPay module is turned off, because public or private key is not set')
        );

        return false;
    }

    /**
     * @return bool
     */
    public function isTestMode(): bool
    {
        return $this->scopeConfig->isSetFlag(static::XML_PATH_TEST_MODE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isSecurityCheck(): bool
    {
        return $this->scopeConfig->isSetFlag(static::XML_PATH_CALLBACK_SECURITY_CHECK, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return \trim($this->scopeConfig->getValue(static::XML_PATH_PUBLIC_KEY, ScopeInterface::SCOPE_STORE));
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        return \trim($this->scopeConfig->getValue(static::XML_PATH_PRIVATE_KEY, ScopeInterface::SCOPE_STORE));
    }

    /**
     * @return string
     */
    public function getTestOrderSuffix(): string
    {
        return \trim($this->scopeConfig->getValue(static::XML_PATH_TEST_ORDER_SUFFIX, ScopeInterface::SCOPE_STORE));
    }

    /**
     * @param OrderInterface $order
     *
     * @return string
     */
    public function getLiqPayDescription(OrderInterface $order): string
    {
        $description = \trim($this->scopeConfig->getValue(static::XML_PATH_DESCRIPTION, ScopeInterface::SCOPE_STORE));

        $params = [
            '{order_id}' => $order->getIncrementId(),
        ];

        return \strtr($description, $params);
    }

    /**
     * @return string
     */
    public function getResultUrl(): string
    {
        $url = \trim($this->scopeConfig->getValue(static::XML_PATH_RESULT_URL, ScopeInterface::SCOPE_STORE));

        if (\preg_match('#^https?://#', $url)) {
            return $url;
        }

        if (!$url) {
            return $this->_urlBuilder->getBaseUrl();
        }

        return $this->_getUrl($url);
    }

    // </editor-fold>

    /**
     * @param OrderInterface $order
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkOrderIsLiqPayPayment(OrderInterface $order): bool
    {
        $payment = $order->getPayment();

        if (!$payment) {
            return false;
        }

        $method = $payment->getMethod();
        $methodInstance = $this->_paymentHelper->getMethodInstance($method);

        return $methodInstance instanceof LiqPayPayment;
    }

    /**
     * @param string|null $data
     * @param string|null $receivedPublicKey
     * @param string      $receivedSignature
     *
     * @return bool
     */
    public function securityOrderCheck($data, $receivedPublicKey, string $receivedSignature): bool
    {
        if (!$this->isSecurityCheck()) {
            return true;
        }

        $publicKey = $this->getPublicKey();

        if ($publicKey !== $receivedPublicKey) {
            return false;
        }

        $privateKey = $this->getPrivateKey();
        $generatedSignature = \base64_encode(\sha1($privateKey . $data . $privateKey, 1));

        return $receivedSignature === $generatedSignature;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->_logger;
    }
}
