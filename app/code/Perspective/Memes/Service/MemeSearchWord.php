<?php
namespace Perspective\Memes\Service;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Perspective\Memes\Model\Memes\MemeDataHandler;

class MemeSearchWord
{
    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;
    /**
     * @var MemeDataHandler
     */
    protected $memeDataHandler;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param MemeDataHandler $memeDataHandler
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        MemeDataHandler $memeDataHandler
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->memeDataHandler = $memeDataHandler;
    }

    /**
     * Returns a search word for Giphy API based on quote subtotal:
     * if subtotal < 100 returns 'Test', else first word of first product's first category
     *
     * @param $quoteId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getSearchWordForQuote($quoteId): string
    {
        $searchWord = 'Test';
        $quote = $this->memeDataHandler->getEntity('quote', $quoteId);

        $subtotal = $quote->getBaseSubtotal();
        if ($subtotal > 100) {
            $item = $quote->getItems()[0];
            $firstCategoryId = $item->getProduct()->getCategoryIds()[0];
            $category = $this->categoryRepository->get($firstCategoryId);
            $categoryName = $category->getName();
            $words = explode(' ', $categoryName);
            $searchWord = $words[0];
        }
        return $searchWord;
    }
}
