<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;

class UserImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return Model|User|null
     */
    public function model(array $row): Model|User|null
    {
        return new User([
            'name' => $row[0],
            'password' => $row[1],
            'credits' => $row[2],
            'phone' => $row[3],
            'database' => $row[4],
        ]);
    }

    public function headingRow(): int
    {
        return 1;
    }
}

