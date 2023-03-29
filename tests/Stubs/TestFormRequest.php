<?php

namespace Tonysm\TurboLaravel\Tests\Stubs;

use Illuminate\Foundation\Http\FormRequest;

class TestFormRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => ['required'],
        ];
    }
}
