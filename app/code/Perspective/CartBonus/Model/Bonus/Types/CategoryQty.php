<?php
namespace Perspective\CartBonus\Model\Bonus\Types;
class CategoryQty extends \Perspective\CartBonus\Model\Bonus\AbstractBonus
{
    public const BONUS_CODE = 'category_qty';
    public const MESSAGE_TEMPLATE = 'Bonus: %d%% discount for %d items from category %s';

    protected array $categoryItemsQtyArray = [];

    public function isApplicable($quote, $total): bool
    {
        if (!$this->isEnabled()){
            return false;
        }
        $config = $this->getConfig();

        //if categories and discount not configured
        if ($config['select_categories'] == null &&
            $config['discount_value'] == null &&
            $config['min_qty'] == null
        ) {
            return false;
        }

        //if totals summoned without quote
        $items = $quote->getItems();
        if (!$items) {
            return false;
        }

        //бонус активний якщо хоч одна з конфіг категорій має достатню кількість ітемів в квоті
        $applicableCategoryIds = $this->dataHelper->stringToArray($config['select_categories']);
        $minCategoryQty = $config['min_qty'];
        $categoryItemsQtyArray = []; //array for validation with category > qty

        foreach ($quote->getItems() as $item) {
            $productCategoryIds = $this->dataHelper->getCategoryIdsByProductSku($item->getSku());
            $qty = $item->getQty();

            foreach (array_intersect($productCategoryIds, $applicableCategoryIds) as $categoryId) {
                if (!isset($categoryItemsQtyArray[$categoryId])) {
                    $categoryItemsQtyArray[$categoryId] = 0;
                }
                $categoryItemsQtyArray[$categoryId] += $qty;
            }
        }
        $this->categoryItemsQtyArray = $categoryItemsQtyArray;
        foreach ($categoryItemsQtyArray as $qty) {
            if ($qty >= $minCategoryQty) {
                return true;
            }
        }
        return false;
    }

    public function apply($quote, $total): array
    {
        //масив з категоріями в яких є достатня кількість ітемів для бонуса
        $applicableCategories = $this->getCategoryItemsQtyArray();
        if (empty($applicableCategories)) {
            return [];
        }

        $discountConfigValue = $this->getConfig()['discount_value'];
        $totalDiscount = 0;
        $categoryDiscounts = [];
        $frontendMessages = [];

        //цикл по масиву категорій
        foreach ($applicableCategories as $categoryId => $qty) {
            $categoryDiscount = 0;
            //цикл по ітемам
            foreach ($quote->getItems() as $item) {
                //категорії ітема
                $productCategoryIds = $this->dataHelper->getCategoryIdsByProductSku($item->getSku());
                //якщо категорія ітема рівна категорії з масиву то розрахування знижки
                if (in_array($categoryId, $productCategoryIds)) {
                    $categoryDiscount += $item->getQty() * $item->getPrice() * ($discountConfigValue / 100);
                }
            }
            //якщо в категорії є знижка то зберігаємо
            if ($categoryDiscount > 0) {
                $categoryDiscounts[$categoryId] = $categoryDiscount;
                $totalDiscount += $categoryDiscount;

                //messages for frontend totals
                $categoryName = $this->dataHelper->getCategoryNameById($categoryId);
                $frontendMessages[] = sprintf(self::MESSAGE_TEMPLATE, $discountConfigValue, $qty, $categoryName);
            }
        }

        // вплив на тотали
        $total->addTotalAmount($this::BONUS_TOTAL_CODE, -$totalDiscount);
        $total->addBaseTotalAmount($this::BONUS_TOTAL_CODE, -$totalDiscount);

        return [
            'bonus_discount' => $totalDiscount,
            'bonus_messages' => $frontendMessages
        ];
    }

    public function getCategoryItemsQtyArray(): array
    {
        $minCategoryQty = $this->getConfig()['min_qty'];

        //return filtered array(масив категорій з достатньою кількістю ітемів)
        return array_filter(
            $this->categoryItemsQtyArray,
            fn($qty) => $qty >= $minCategoryQty
        );
    }
}
