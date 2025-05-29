<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Row;

class ProductsImport implements
    OnEachRow,
    WithHeadingRow,
    SkipsOnFailure,
    WithChunkReading,
    ShouldQueue
{
    use SkipsFailures;

    protected $categoryId;
    protected $businessId;
    protected $branchId;
    protected $userId;

    public function __construct($categoryId, $businessId, $branchId, $userId)
    {
        $this->categoryId = $categoryId;
        $this->businessId = $businessId;
        $this->branchId = $branchId;
        $this->userId = $userId;
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        Product::create([
            'name'              => $data['name'],
            'unique_identifier' => $data['unique_identifier'],
            'category_id'       => $this->categoryId,
            'business_id'       => $this->businessId,
            'branch_id'         => $this->branchId,
            'user_id'           => $this->userId,
        ]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
