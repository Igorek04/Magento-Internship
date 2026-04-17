<?php
namespace Perspective\CustomerQuestions\Controller\Adminhtml\Answer;

use Magento\Backend\App\Action;
use Perspective\CustomerQuestions\Model\AnswerFactory;
use Perspective\CustomerQuestions\Model\ResourceModel\Answer as AnswerResourceModel;
use Magento\Framework\Controller\ResultFactory;

class Save extends Action
{
    protected $answerFactory;
    protected $answerResourceModel;

    public function __construct(
        Action\Context $context,
        AnswerFactory $answerFactory,
        AnswerResourceModel $answerResourceModel
    ) {
        parent::__construct($context);
        $this->answerFactory = $answerFactory;
        $this->answerResourceModel = $answerResourceModel;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $answerData = $data['general'];

        try {
            $model = $this->answerFactory->create();

            if (isset($answerData['entity_id'])) {
                $this->answerResourceModel->load($model, $answerData['entity_id']);
            }

            $model->setData($answerData);
            $this->answerResourceModel->save($model);

            $result = ['error' => false];
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
