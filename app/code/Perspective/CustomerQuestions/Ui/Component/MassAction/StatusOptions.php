<?php
namespace Perspective\CustomerQuestions\Ui\Component\MassAction;

use Magento\Framework\UrlInterface;
use Perspective\CustomerQuestions\Model\Source\Status as StatusSource;
use JsonSerializable;

class StatusOptions implements JsonSerializable
{
    protected $urlBuilder;
    protected $statusSource;

    public function __construct(
        UrlInterface $urlBuilder,
        StatusSource $statusSource
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->statusSource = $statusSource;
    }

    public function jsonSerialize(): array
    {
        $options = [];
        $urlPath = 'customerquestions/question/massStatus';

        foreach ($this->statusSource->getOptionArray() as $value => $label) {
            $options[$value] = [
                'type' => 'status_' . $value,
                'label' => $label,
                'url' => $this->urlBuilder->getUrl($urlPath, [
                    'status' => $value,
                    'namespace' => 'customer_questions_listing'
                ])
            ];
        }
        return $options;
    }
}
