<?php
namespace Perspective\ProductReservation\Controller\Reservation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Controller\Result\RawFactory;

class Form extends Action
{
    protected $layoutFactory;
    protected $rawFactory;

    public function __construct(
        Context $context,
        RawFactory $rawFactory,
        LayoutFactory $layoutFactory
    ) {
        $this->rawFactory = $rawFactory;
        $this->layoutFactory = $layoutFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $layout = $this->layoutFactory->create();
        $block = $layout->createBlock(
            \Perspective\ProductReservation\Block\Form::class
        )->setTemplate(
            'Perspective_ProductReservation::product/view/reservation/form.phtml'
        )->toHtml();

        $resultRaw = $this->rawFactory->create();
        return $resultRaw->setContents($block);
    }
}
