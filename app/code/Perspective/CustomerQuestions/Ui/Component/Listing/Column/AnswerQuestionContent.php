<?php
namespace Perspective\CustomerQuestions\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Perspective\CustomerQuestions\Model\ResourceModel\Question\CollectionFactory;
use Magento\Framework\App\RequestInterface;


class AnswerQuestionContent extends Column
{
    protected $collectionFactory;
    protected $request;
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $components = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    //hide question content field for inserted grid
    public function prepare()
    {
        $referer = $this->request->getServer('HTTP_REFERER');

        if ($referer && str_contains($referer, 'customerquestions/question/edit')) {
            $config = $this->getData('config');
            $config['visible'] = false;
            $this->setData('config', $config);
        }

        parent::prepare();
    }

    public function prepareDataSource(array $dataSource)
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        $ids = [];
        foreach ($dataSource['data']['items'] as $item) {
            $ids[] = $item['question_id'];
        }

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('entity_id', ['in' => array_unique($ids)]);

        $map = [];
        foreach ($collection as $question) {
            $map[$question->getId()] = $question->getQuestionText();
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $fullText = $map[$item['question_id']];
            $cleanText = strip_tags($fullText);

            if (mb_strlen($cleanText) > 50) {
                $shortText = mb_substr($cleanText, 0, 50) . '...';
                $item[$this->getData('name')] = sprintf(
                    '<span title="%s" style="cursor:help; border-bottom:1px dotted #777;">%s</span>',
                    htmlspecialchars($cleanText),
                    htmlspecialchars($shortText)
                );
            } else {
                $item[$this->getData('name')] = htmlspecialchars($cleanText);
            }
        }

        return $dataSource;
    }
}
