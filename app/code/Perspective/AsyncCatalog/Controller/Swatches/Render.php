<?php
namespace Perspective\AsyncCatalog\Controller\Swatches;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Swatches\Block\Product\Renderer\Listing\Configurable;
use Magento\Swatches\ViewModel\Product\Renderer\Configurable as ConfigurableViewModel;

class Render implements HttpGetActionInterface
{
    /** @var JsonFactory */
    protected $resultJsonFactory;

    /** @var RequestInterface */
    protected $request;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var LayoutInterface */
    protected $layout;

    protected $configurableViewModel;

    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        LayoutInterface $layout,
        ConfigurableViewModel $configurableViewModel
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->layout = $layout;
        $this->configurableViewModel = $configurableViewModel;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $productIds = $this->request->getParam('ids');

        if (!$productIds || !is_array($productIds)) {
            return $result->setData([]);
        }

        $htmlResponse = [];

        foreach ($productIds as $id) {
            try {
                $product = $this->productRepository->getById((int)$id);

                if ($product->getTypeId() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {

                    $block = $this->layout->createBlock(
                        \Magento\Swatches\Block\Product\Renderer\Listing\Configurable::class,
                        'swatch_renderer_' . $id
                    );

                    $block->setTemplate('Magento_Swatches::product/listing/renderer.phtml')
                        ->setProduct($product);


                    $block->setData('configurable_view_model', $this->configurableViewModel);

                    $html = $block->toHtml();
                    $html = str_replace(
                        '"selectorProduct": ".product-item-details"',
                        '"selectorProduct": ".product-item-info"',
                        $html
                    );
                    $htmlResponse[$id] = $html;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $result->setData($htmlResponse);
    }
}
