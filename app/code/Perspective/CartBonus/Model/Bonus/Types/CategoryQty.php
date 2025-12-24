<?php
namespace Perspective\CartBonus\Model\Bonus\Types;
class CategoryQty extends \Perspective\CartBonus\Model\Bonus\AbstractBonus
{
    public const BONUS_CODE = 'category_qty';

    public function isApplicable($quote): bool
    {
        //$this->isEnabled(); в менеджер вынести
        $config = $this->getConfig();
        if ($config['select_categories'] == null && $config['discount_value'] == null) {
            return false;
        }

        $categories = $this->dataHelper->stringToArray($config['select_categories']);
        foreach ($quote->getItems() as $item) {
            $sku = $item->getSku();
            $productCategoryIds = $this->dataHelper->getCategoryIdsByProductSku($sku);
            $a = $sku; //проверка на то что категория продукта попадает в допустимые категории конфига

        }



        return 1;
    }

    public function apply($quote): array
    {
        return [1,1];
    }
}
