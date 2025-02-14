<?php

namespace Webkul\Purchase\Filament\Clusters\Configurations\Resources\ProductCategoryResource\Pages;

use Webkul\Product\Filament\Resources\CategoryResource\Pages\EditCategory;
use Webkul\Purchase\Filament\Clusters\Configurations\Resources\ProductCategoryResource;

class EditProductCategory extends EditCategory
{
    protected static string $resource = ProductCategoryResource::class;
}
