<?php

namespace App\Imports;

use App\Models\Specialize;
use App\Models\Supervisor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SupervisorsImport implements ToModel, WithHeadingRow, WithChunkReading, ShouldQueue
{
    private $specializes;
    private $admin_id;

    public function __construct($admin_id)
    {
        $this->admin_id = $admin_id;
        $this->specializes = Specialize::select('id', 'name')->get();

    }

    public function model(array $row)
    {
        $specialize = $this->specializes->where('name', $row['specialization'])->first();
        $specialize_id = $specialize ? $specialize->id : '';

        return new Supervisor([
            'name' => $row['name'],
            'university_id' => trim($row['university_id']),
            'email' => trim($row['email']),
            'phone' => "0" . trim($row['phone']),
            'gender' => $row['gender'] ?? 'male',
            'specialize_id' => $specialize_id,
            'password' => bcrypt(trim($row['password'])),
            'admin_id' => $this->admin_id,
        ]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }

}
