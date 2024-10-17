<?php

namespace App\Repositories;

use App\Traits\AuthTrait;
use App\Models\ProductLine;
use App\Traits\Base\BaseTrait;
use App\Repositories\BaseRepository;
use App\Services\Filter\FilterService;
use App\Http\Resources\ProductLineResources;

class ProductLineRepository extends BaseRepository
{
    use AuthTrait, BaseTrait;

    /**
     * Show product lines.
     *
     * @return ProductLineResources|array
     */
    public function showProductLines(): ProductLineResources|array
    {
        if($this->getQuery() == null) {
            if(!$this->isAuthourized()) return ['message' => 'You do not have permission to show product lines'];
            $this->setQuery(ProductLine::query()->latest());
        }

        return $this->applyFiltersOnQuery()->getOrCountResources();
    }

    /**
     * Show product line.
     *
     * @param ProductLine|string|null $productLineId
     * @return ProductLine|array|null
     */
    public function showProductLine(ProductLine|string|null $productLineId = null): ProductLine|array|null
    {
        if(($productLine = $productLineId) instanceof ProductLine) {
            $productLine = $this->applyEagerLoadingOnModel($productLine);
        }else {
            $query = $this->getQuery() ?? ProductLine::query();
            if($productLineId) $query = $query->where('product_lines.id', $productLineId);
            $this->setQuery($query)->applyEagerLoadingOnQuery();
            $productLine = $this->query->first();
        }

        return $this->showResourceExistence($productLine);
    }

    /***********************************************
     *            MISCELLANEOUS METHODS           *
     **********************************************/
}
