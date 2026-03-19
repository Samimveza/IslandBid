<?php

class CategoryService
{
    public function __construct(private CategoryRepository $categories)
    {
    }

    public function listActive(): array
    {
        return $this->categories->getActiveCategories();
    }

    public function getFields(string $idCategory): array
    {
        return $this->categories->getActiveFieldsWithOptions($idCategory);
    }
}

