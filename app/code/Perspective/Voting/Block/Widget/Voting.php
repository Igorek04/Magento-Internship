<?php
namespace Perspective\Voting\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

use Perspective\Voting\Service\CacheManager;
use Perspective\Voting\Model\VotingManager;
use Perspective\Voting\Model\VotingOptionManager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;


class Voting extends Template implements BlockInterface
{
    protected $_template = 'Perspective_Voting::widget/voting.phtml';

    protected $votingManager;
    protected $votingOptionManager;
    protected $cacheManager;
    protected $productRepository;
    protected $imageHelper;
    public function __construct(
        Template\Context $context,
        VotingManager $votingManager,
        VotingOptionManager $votingOptionManager,
        CacheManager $cacheManager,
        ProductRepositoryInterface $productRepository,
        Image $imageHelper,
        array $data = []
    ) {
        $this->votingManager = $votingManager;
        $this->votingOptionManager = $votingOptionManager;
        $this->cacheManager = $cacheManager;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $data);
    }

    public function prepareVotingData(): array
    {
        $votingId = $this->_data['voting_id'];
        $data = $this->cacheManager->getVotingCache($votingId);
        if (!$data) {
            $voting = $this->votingManager->getById($votingId);

            $data = [
                'id'             => $voting->getId(),
                'title'          => $voting->getTitle(),
                'description'    => $voting->getDescription(),
                'management_type' => $voting->getManagementType(),
                'allow_guest'   => (bool)$voting->getAllowGuests(),
                'manual_status'         => $voting->getStatus(),          // manual activity status
                'auto_end_date'       => $voting->getEndDate(),         // auto finish date
                'is_finished'    => (bool)$voting->getIsFinished(),
                'winner_option_id'      => $voting->getWinnerOptionId(),
                'finished_at'    => $voting->getFinishedAt(),
                'options'        => $this->prepareOptionsData($votingId)
            ];
        }
        $this->cacheManager->saveVotingCache($votingId, $data);
        return $data;
    }


    protected function prepareOptionsData(int $votingId): array
    {
        $options = $this->votingOptionManager->getOptionsByVotingId($votingId);
        $result = [];

        foreach ($options as $option) {
            $optionData = [
                'option_id'   => $option->getId(),
                'title'       => $option->getTitle(),
                'description' => $option->getDescription(),
                'votes'       => (int)$option->getTotalVotes(),
                'product'     => null
            ];

            if ($option->getProductId()) {
                $product = $this->productRepository->getById($option->getProductId());
                $productName = $product->getName();
                $productImage = $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl();
                $productUrl = $product->getProductUrl();

                $optionData['product'] = [
                    'name' => $productName,
                    'image' => $productImage,
                    'url' => $productUrl
                ];
            }
            $result[] = $optionData;
        }

        // filter options by votes
        $votes = array_column($result, 'votes');
        array_multisort($votes, SORT_DESC, $result);
        return $result;
    }
}
