<?php

namespace Webkul\Website\Filament\Customer\Resources;

use Webkul\Website\Filament\Customer\Resources\PageResource\Pages;
use Webkul\Website\Models\Page;
use Filament\Resources\Resource;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    // protected static ?string $slug = 'pages';

    protected static ?string $recordRouteKeyName = 'slug';

    protected static bool $shouldRegisterNavigation = false;

    public static function getPages(): array
    {
        return [
            'view' => Pages\ViewPage::route('/{record}'),
        ];
    }
}
