<?php
namespace Perspective\Voting\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;


class Voting extends Template implements BlockInterface
{

    protected $_template = 'Perspective_Voting::widget/voting.phtml';
}
