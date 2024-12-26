<?php

namespace App\Http\Controllers;

use App\Repositories\RowRepository;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RowsController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private readonly RowRepository $rowRepository)
    {
    }

    public function index(Request $request): Application|Factory|View
    {
        $page = (int) $request->get('page', 1);

        $rowsGroup = $this->rowRepository->getRowsGroupedByDate();

        $rows = $this->paginateGroupedData($rowsGroup, $page);

        return view('rows.index', compact('rows'));
    }

    private function paginateGroupedData(Collection $groupedData, int $page): LengthAwarePaginator
    {
        $start = ($page - 1) * self::PER_PAGE;
        $paginatedGroups = $groupedData->slice($start, self::PER_PAGE);

        $totalRows = $groupedData->flatten()->count();

        return new LengthAwarePaginator(
            $paginatedGroups,
            $totalRows,
            self::PER_PAGE,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

}
