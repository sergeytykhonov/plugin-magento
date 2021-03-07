<?php
/**
 * LiqPay Extension for Magento 2
 *
 * @author     Volodymyr Konstanchuk http://konstanchuk.com
 * @copyright  Copyright (c) 2017 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace LiqpayMagento\LiqPay\Controller\Checkout;

use LiqpayMagento\LiqPay\Block\SubmitForm;
use LiqpayMagento\LiqPay\Helper\Data as Helper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\LayoutFactory;

class Form extends Action
{
    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var LayoutFactory
     */
    protected $_layoutFactory;

    /**
     * @param Context         $context
     * @param CheckoutSession $checkoutSession
     * @param Helper          $helper
     * @param LayoutFactory   $layoutFactory
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        Helper $helper,
        LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);

        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->_layoutFactory = $layoutFactory;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        try {
            if (!$this->_helper->isEnabled()) {
                throw new \Exception(__('Payment is not allowed.'));
            }

            $order = $this->getCheckoutSession()->getLastRealOrder();

            if (!$order || !$order->getId()) {
                throw new \Exception(__('Order not found'));
            }

            if (!$this->_helper->checkOrderIsLiqPayPayment($order)) {
                throw new \Exception('Order payment method is not a LiqPay payment method');
            }

            /* @var SubmitForm $formBlock */
            $formBlock = $this->_layoutFactory->create()->createBlock(SubmitForm::class);
            $formBlock->setOrder($order);
            $data = [
                'status'  => 'success',
                'content' => $formBlock->toHtml(),
            ];
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong, please try again later'));
            $this->_helper->getLogger()->critical($e);
            $this->getCheckoutSession()->restoreQuote();
            $data = [
                'status'   => 'error',
                'redirect' => $this->_url->getUrl('checkout/cart'),
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData($data);

        return $result;
    }

    /**
     * Return checkout session object
     *
     * @return CheckoutSession
     */
    protected function getCheckoutSession(): CheckoutSession
    {
        return $this->_checkoutSession;
    }
}
