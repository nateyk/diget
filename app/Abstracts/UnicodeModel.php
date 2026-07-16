<?php

namespace App\Abstracts;

use Illuminate\Database\Eloquent\Model;

abstract class UnicodeModel extends Model
{
    protected function asJson($value, $flags = 0)
    {
        return json_encode($value, $flags | JSON_UNESCAPED_UNICODE);
    }
}
