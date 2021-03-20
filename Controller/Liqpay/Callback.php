<?php
/**
 * LiqPay Extension for Magento 2
 *
 * @author     Tykhonov Sergey
 * @copyright  Copyright (c) 2021 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */
declare(strict_types=1);

namespace LiqpayMagento\LiqPay\Controller\Liqpay;

use LiqpayMagento\LiqPay\Api\LiqPayCallbackInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Callback extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var LiqPayCallbackInterface
     */
    private $callbackModel;

    /**
     * @param Context                 $context
     * @param LiqPayCallbackInterface $callbackModel
     */
    public function __construct(Context $context, LiqPayCallbackInterface $callbackModel)
    {
        $this->callbackModel = $callbackModel;

        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->callbackModel->callback();
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
