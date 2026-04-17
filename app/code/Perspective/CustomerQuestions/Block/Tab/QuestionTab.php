<?php
namespace Perspective\CustomerQuestions\Block\Tab;

use Magento\Framework\View\Element\Template;

class QuestionTab extends Template
{
    public function getTitle()
    {
        $viewModel = $this->getData('view_model');
        $count = 0;

        if ($viewModel) {
            $count = $viewModel->getQuestionsCount();
        }

        return __($this->getData('title')) . ($count > 0 ? " ($count)" : "");
    }
}
