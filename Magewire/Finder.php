<?php declare(strict_types=1);

namespace YireoTraining\ExampleMageWireFinder\Magewire;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magewirephp\Magewire\Component;
use YireoTraining\ExampleMageWireFinder\Magewire\Finder\Option;

class Finder extends Component
{
    private string $attributeCode1 = '';
    private string $attributeCode2 = '';
    private string $attributeCode3 = '';
    public string $attributeValue1 = '';
    public string $attributeValue2 = '';
    public string $attributeValue3 = '';
    
    public array $products = [];

    public function __construct(
        private AttributeRepositoryInterface $attributeRepository,
        private ProductRepositoryInterface $productRepository,
        private SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    /**
     * @return AttributeOptionInterface[]
     * @throws NoSuchEntityException
     */
    public function getAttribute1Options(): array
    {
        $this->searchCriteriaBuilder->addFilter($this->attributeCode1, null, 'notnull');
        $searchResults = $this->productRepository->getList($this->searchCriteriaBuilder->create());
        $products = $searchResults->getItems();

        return $this->getOptions($products, $this->getAttribute($this->attributeCode1));
    }

    /**
     * @return AttributeOptionInterface[]
     * @throws NoSuchEntityException
     */
    public function getAttribute2Options(): array
    {
        if (!$this->attributeValue1) {
            return [];
        }

        $this->searchCriteriaBuilder->addFilter($this->attributeCode2, null, 'notnull');
        $this->searchCriteriaBuilder->addFilter($this->attributeCode1, $this->attributeValue1);
        $searchResults = $this->productRepository->getList($this->searchCriteriaBuilder->create());
        $products = $searchResults->getItems();

        return $this->getOptions($products, $this->getAttribute($this->attributeCode2));
    }

    /**
     * @return AttributeOptionInterface[]
     * @throws NoSuchEntityException
     */
    public function getAttribute3Options(): array
    {
        if (!$this->attributeValue1 || !$this->attributeValue2) {
            return [];
        }

        $this->searchCriteriaBuilder->addFilter($this->attributeCode3, null, 'notnull');
        $this->searchCriteriaBuilder->addFilter($this->attributeCode2, $this->attributeValue2);
        $this->searchCriteriaBuilder->addFilter($this->attributeCode1, $this->attributeValue1);
        $searchResults = $this->productRepository->getList($this->searchCriteriaBuilder->create());
        $products = $searchResults->getItems();

        return $this->getOptions($products, $this->getAttribute($this->attributeCode3));
    }

    /**
     * @return ProductInterface[]
     * @throws NoSuchEntityException
     */
    public function getProducts(): array
    {
        if (!$this->attributeValue1 || !$this->attributeValue2 || !$this->attributeValue3) {
            return [];
        }

        $this->searchCriteriaBuilder->addFilter($this->attributeCode3, $this->attributeValue3);
        $this->searchCriteriaBuilder->addFilter($this->attributeCode2, $this->attributeValue2);
        $this->searchCriteriaBuilder->addFilter($this->attributeCode1, $this->attributeValue1);
        $searchResults = $this->productRepository->getList($this->searchCriteriaBuilder->create());
        return $searchResults->getItems();
    }

    /**
     * @return string
     */
    public function getAttributeCode1(): string
    {
        return $this->attributeCode1;
    }

    /**
     * @param string $attributeCode1
     * @return void
     */
    public function setAttributeCode1(string $attributeCode1): void
    {
        $this->attributeCode1 = $attributeCode1;
    }

    /**
     * @return string
     */
    public function getAttributeCode2(): string
    {
        return $this->attributeCode2;
    }

    /**
     * @param string $attributeCode2
     * @return void
     */
    public function setAttributeCode2(string $attributeCode2): void
    {
        $this->attributeCode2 = $attributeCode2;
    }

    /**
     * @return string
     */
    public function getAttributeCode3(): string
    {
        return $this->attributeCode3;
    }

    /**
     * @param string $attributeCode3
     * @return void
     */
    public function setAttributeCode3(string $attributeCode3): void
    {
        $this->attributeCode3 = $attributeCode3;
    }

    /**
     * @return void
     */
    public function resetForm(): void
    {
        $this->attributeValue1 = '';
        $this->attributeValue2 = '';
        $this->attributeValue3 = '';
    }

    /**
     * @param array $products
     * @param AttributeInterface $attribute
     * @return Option[]
     */
    private function getOptions(array $products, AttributeInterface $attribute): array
    {
        if (!count($products)) {
            return [];
        }

        $options = [];
        foreach ($products as $product) {
            $optionValue = $product->getData($attribute->getAttributeCode());
            foreach(explode(',', $optionValue) as $optionValue) {
                $optionLabel = $this->getAttributeFrontendLabel($attribute, $optionValue);
                if (!isset($options[$optionValue])) {
                    $options[$optionValue] = new Option($optionLabel, $optionValue, 1);
                    continue;
                }

                $option = $options[$optionValue];
                $option->incrementProductCount();
            }
        }

        return $options;
    }

    /**
     * @param AttributeInterface $attribute
     * @param $optionValue
     * @return string
     */
    private function getAttributeFrontendLabel(AttributeInterface $attribute, $optionValue): string
    {
        foreach ($attribute->getOptions() as $option) {
            if ($option->getValue() === $optionValue) {
                return $option->getLabel();
            }

            if (in_array($option->getValue(), explode(',', (string)$optionValue))) {
                return $option->getLabel();
            }
        }

        return 'unknown: '.$optionValue;
    }

    /**
     * @param string $attributeCode
     * @return AttributeInterface
     * @throws NoSuchEntityException
     */
    private function getAttribute(string $attributeCode): AttributeInterface
    {
        return $this->attributeRepository->get('catalog_product', $attributeCode);
    }
}