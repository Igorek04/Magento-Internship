<?php
namespace Perspective\ProductReservation\Helper;
use Magento\Framework\Exception\LocalizedException;
class DataValidation extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Validator\EmailAddress
     */
    protected $emailValidator;

    /**
     * @param \Magento\Framework\Validator\EmailAddress $emailValidator
     */
    public function __construct(
        \Magento\Framework\Validator\EmailAddress $emailValidator
    ) {
        $this->emailValidator = $emailValidator;
    }

    /**
     * @param array $data
     * @param string $telephone
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return void
     * @throws LocalizedException
     */
    public function validateData(array $data, string &$telephone, $product): void
    {
        $this->validateName($data['name']);
        $this->validateEmail($data['email']);
        $this->validatePhone($telephone);
        $this->validateProduct($product);
    }

    /**
     * @param string $name
     * @return void
     * @throws LocalizedException
     */
    public function validateName(string $name)
    {
        if (!preg_match('/^[\p{L}\s\-]+$/u', $name)) {
            throw new LocalizedException(
                __('Invalid name')
            );
        }
    }

    /**
     * @param string $email
     * @return void
     * @throws LocalizedException
     */
    public function validateEmail(string $email)
    {
        if (!$this->emailValidator->isValid($email)) {
            throw new LocalizedException(
                __('Invalid email address')
            );
        }
    }

    /**
     * @param string $telephone
     * @return void
     * @throws LocalizedException
     */
    public function validatePhone(string &$telephone)
    {
        if ($telephone == '') {
            $telephone = '0';
            return;
        }
        $telephone = preg_replace('/[^\d+]/', '', $telephone);
        if (strlen($telephone) < 10 || strlen($telephone) > 15) {
            throw new LocalizedException(
                __('Invalid phone number')
            );
        }
    }

    /**
     * @param $product
     * @return void
     * @throws LocalizedException
     */
    public function validateProduct($product)
    {
        if ($product->getTypeId() == 'configurable')
        {
            throw new LocalizedException(
                __('Please select product options')
            );
        }
    }
}
