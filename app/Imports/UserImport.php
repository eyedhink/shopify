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
        var_dump($row);
        return new User([
            'name' => $row[0],
            'password' => $row[1],
            'phone' => $row[2],
            'database' => $row[3],
        ]);
    }

    public function headingRow(): int
    {
        return 1;
    }
}

