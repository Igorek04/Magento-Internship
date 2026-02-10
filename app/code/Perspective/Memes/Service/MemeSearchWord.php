<?php
namespace Perspective\Memes\Service;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Perspective\Memes\Model\Memes\MemeDataHandler;

class MemeSearchWord
{
    protected $categoryRepository;
    protected $memeDataHandler;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        MemeDataHandler $memeDataHandler
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->memeDataHandler = $memeDataHandler;
    }

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
