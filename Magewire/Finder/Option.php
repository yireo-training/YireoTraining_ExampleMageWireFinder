<?php declare(strict_types=1);

namespace YireoTraining\ExampleMageWireFinder\Magewire\Finder;

class Option
{
    public function __construct(
        private string $label,
        private string $value,
        private int $productCount
    ) {
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getProductCount(): int
    {
        return $this->productCount;
    }

    public function incrementProductCount()
    {
        $this->productCount++;
    }
}