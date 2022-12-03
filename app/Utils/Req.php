<?php /** @noinspection PhpUnused */

declare(strict_types=1);

namespace App\Utils;

use Illuminate\Http\Request;

class Req
{
    public static function usePage(): array
    {
        /** @var Request $request */
        $request = app('request');
        $currentPage = $request->input('currentPage', 0);
        $perPage = $request->input('perPage', 0);
        $columns = ['*'];
        return [$perPage, $columns, 'page', $currentPage];
    }
}
