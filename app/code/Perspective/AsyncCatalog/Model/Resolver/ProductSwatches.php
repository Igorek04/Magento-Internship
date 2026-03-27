<?php
namespace Perspective\AsyncCatalog\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\View\LayoutInterface;
use Magento\Swatches\Block\Product\Renderer\Listing\Configurable as SwatchRenderer;
use Magento\Swatches\ViewModel\Product\Renderer\Configurable as ConfigurableViewModel;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Catalog\Model\ProductRepository;

class ProductSwatches implements ResolverInterface
{
    private $layout;
    private $configurableViewModel;
    private $state;
    private $productRepository;

    public function __construct(
        LayoutInterface $layout,
        ConfigurableViewModel $configurableViewModel,
        State $state,
        ProductRepository $productRepository
    ) {
        $this->layout = $layout;
        $this->configurableViewModel = $configurableViewModel;
        $this->state = $state;
        $this->productRepository = $productRepository;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $product = $this->productRepository->getById($value['model']->getId());

        return $this->state->emulateAreaCode(
            Area::AREA_FRONTEND,
            [$this, 'renderSwatches'],
            [$product]
        );
    }

    public function renderSwatches($product)
    {
        $html = $this->layout->createBlock(SwatchRenderer::class, 'swatch_renderer_' . $product->getId())
            ->setTemplate('Magento_Swatches::product/listing/renderer.phtml')
            ->setProduct($product)
            ->setData('configurable_view_model', $this->configurableViewModel)
            ->toHtml();

        $html = str_replace(
            '"selectorProduct": ".product-item-details"',
            '"selectorProduct": ".product-item-info"',
            $html
        );

        if (strpos($html, '"sku"') === false) {
            $html = str_replace(
                '"productId"',
                '"sku":{},"productId"',
                $html
            );
        }

        return $html;
    }
}
