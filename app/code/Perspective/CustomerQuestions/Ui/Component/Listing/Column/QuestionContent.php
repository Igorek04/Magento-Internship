<?php
namespace Perspective\CustomerQuestions\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class QuestionContent extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $fullText = $item['question_text'] ?? '';
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
        }
        return $dataSource;
    }
}
