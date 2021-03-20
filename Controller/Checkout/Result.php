<?php
/**
 * LiqPay Extension for Magento 2
 *
 * @author     Tykhonov Sergey
 * @copyright  Copyright (c) 2021 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */
declare(strict_types=1);

namespace LiqpayMagento\LiqPay\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Result extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    public function __construct(Context $context, PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;

        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        return $this->pageFactory->create();
    }
}
