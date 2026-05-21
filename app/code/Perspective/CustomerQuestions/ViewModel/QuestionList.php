<?php
namespace Perspective\CustomerQuestions\ViewModel;

use IntlDateFormatter;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\RequestInterface;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Perspective\CustomerQuestions\Model\ResourceModel\Question\CollectionFactory as QuestionCollectionFactory;
use Perspective\CustomerQuestions\Model\ResourceModel\Answer\CollectionFactory as AnswerCollectionFactory;
use Perspective\CustomerQuestions\Model\Source\Status;

class QuestionList implements ArgumentInterface
{
    protected $request;
    protected $storeManager;
    protected $timezone;
    protected $questionCollectionFactory;
    protected $answerCollectionFactory;

    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        TimezoneInterface $timezone,
        QuestionCollectionFactory $questionCollectionFactory,
        AnswerCollectionFactory $answerCollectionFactory
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->timezone = $timezone;
        $this->questionCollectionFactory = $questionCollectionFactory;
        $this->answerCollectionFactory = $answerCollectionFactory;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->request->getParam('id');
    }

    private function formatDate($dateString): string
    {
        return $this->timezone->formatDateTime(
            $dateString,
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::SHORT
        );
    }


    private function getAvatarLetter($name)
    {
        $name = trim((string)$name);

        if ($name !== '') {
            return mb_strtoupper(mb_substr($name, 0, 1));
        }

        return 'U';
    }

    public function getQaData(): array
    {
        $productId = $this->getProductId();
        $storeId = $this->storeManager->getStore()->getId();

        $qaData = [];

        $questionCollection = $this->questionCollectionFactory->create()
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('status', Status::STATUS_APPROVED)
            ->setOrder('created_at', 'DESC');

        $questionIds = $questionCollection->getColumnValues('entity_id');

        if (empty($questionIds)) {
            return $qaData;
        }

        foreach ($questionCollection as $question) {
            $qaData[$question->getId()] = [
                'id'            => $question->getId(),
                'author_name'   => $question->getAuthorName(),
                'avatar_letter' => $this->getAvatarLetter($question->getAuthorName()),
                'text'          => $question->getQuestionText(),
                'date'          => $this->formatDate($question->getCreatedAt()),
                'answers'       => []
            ];
        }

        $answerCollection = $this->answerCollectionFactory->create()
            ->addFieldToFilter('question_id', ['in' => $questionIds])
            ->addFieldToFilter('status', Status::STATUS_APPROVED)
            ->setOrder('created_at', 'ASC');

        foreach ($answerCollection as $answer) {
            $qId = $answer->getQuestionId();

            $qaData[$qId]['answers'][] = [
                'id'            => $answer->getId(),
                'author_name'   => $answer->getAuthorName(),
                'avatar_letter' => $this->getAvatarLetter($answer->getAuthorName()),
                'text'          => $answer->getAnswerText(),
                'date'          => $this->formatDate($answer->getCreatedAt()),
                'is_admin'      => $answer->getIsAdmin()
            ];
        }

        return $qaData;
    }
}
