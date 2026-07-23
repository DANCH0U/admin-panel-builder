<?php

namespace App\AdminPanel\Tables;

use App\AdminPanel\Engine\Search\SearchableColumn;

class Search
{
    public static function column(string $name): SearchableColumn
    {
        return SearchableColumn::make($name)->strategy('like');
    }
}
