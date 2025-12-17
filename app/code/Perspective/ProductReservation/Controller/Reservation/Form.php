<?php
namespace Perspective\ProductReservation\Controller\Reservation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Perspective\ProductReservation\ViewModel\FormData;

class Form extends Action
{
    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;
    /**
     * @var RawFactory
     */
    protected $rawFactory;
    /**
     * @var FormData
     */
    protected $viewModel;
    /**
     * @param Context $context
     * @param RawFactory $rawFactory
     * @param LayoutFactory $layoutFactory
     * @param FormData $viewModel
     */
    public function __construct(
        Context $context,
        RawFactory $rawFactory,
        LayoutFactory $layoutFactory,
        FormData $viewModel
    ) {
        $this->rawFactory = $rawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->viewModel = $viewModel;
        parent::__construct($context);
    }

    /**
     * Controller for create block with form template and viewModel
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $layout = $this->layoutFactory->create();
        $block = $layout->createBlock(
            \Perspective\ProductReservation\Block\Form::class,
            'reservation_form_block',
            [
                'data' => [
                    'viewModel' => $this->viewModel,
                ]
            ]
        )->setTemplate(
            'Perspective_ProductReservation::product/view/reservation/form.phtml'
        )->toHtml();

        $resultRaw = $this->rawFactory->create();
        return $resultRaw->setContents($block);
    }
}
