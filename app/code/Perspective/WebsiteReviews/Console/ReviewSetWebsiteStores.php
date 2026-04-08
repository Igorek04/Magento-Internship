<?php
namespace Perspective\WebsiteReviews\Console;

use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\ResourceModel\Review as ReviewResourceModel;
use Perspective\WebsiteReviews\Service\ReviewStoreManager;

class ReviewSetWebsiteStores extends Command
{
    /**
     * @var ReviewStoreManager
     */
    protected $reviewStoreManager;
    /**
     * @var ReviewResourceModel
     */
    protected $reviewResourceModel;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param ReviewStoreManager $reviewStoreManager
     * @param ReviewResourceModel $reviewResourceModel
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ReviewStoreManager $reviewStoreManager,
        ReviewResourceModel $reviewResourceModel,
        CollectionFactory $collectionFactory

    ) {
        $this->reviewStoreManager = $reviewStoreManager;
        $this->reviewResourceModel = $reviewResourceModel;
        $this->collectionFactory = $collectionFactory;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('perspective:reviews:set-website-stores')
            ->setDescription('Set website stores for reviews');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws AlreadyExistsException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Process reviews in batches(pagination) to avoid memory issues
        $totalCount = $this->collectionFactory->create()->getSize();
        $pageSize = 100;
        $totalPages = ceil($totalCount / $pageSize);

        $updatedCount = 0;

        for ($currentPage = 1; $currentPage <= $totalPages; $currentPage++) {
            $collection = $this->collectionFactory->create();
            $collection->addStoreData();
            $collection->setPageSize($pageSize);
            $collection->setCurPage($currentPage);


            foreach ($collection as $review) {
                // count stores before update
                $baseStoresCount = count($review->getStores());

                $this->reviewStoreManager->setWebsiteStoresToReview($review);

                // if new count stores > base   -> save
                if (count($review->getStores()) > $baseStoresCount) {
                    $this->reviewResourceModel->save($review);
                    $updatedCount++;
                }
            }
            $collection->clear();
        }
        $output->writeln("<info>Done! Total reviews updated: $updatedCount</info>");
        return Cli::RETURN_SUCCESS;
    }
}
