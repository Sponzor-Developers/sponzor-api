<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\JsonController;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [
            [
                'id' => 1,
                'name' => 'John Doe',
                'email' => ''
            ],
        ];
        

        return JsonController::return('success', 200, 'Dashboard');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return JsonController::return('success', 200, 'Dashboard');
    }
}
