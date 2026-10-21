<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'file' => 'required|file',
            'type' => 'required|string',
//            'project_announcement_id' => 'required|integer',
//            'image.*' => 'required|mimes:jpg,jpeg,png,gif',
        ];
    }
}
