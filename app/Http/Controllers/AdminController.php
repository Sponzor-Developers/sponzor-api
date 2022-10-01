<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\JsonController;
use Illuminate\Support\Facades\DB;


class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {        
        if(!auth()->user()->is_admin){
            return JsonController::return('error', 403, 'You are not authorized to access this resource');
        }
        return JsonController::return('success', 200, 'Welcome to the API');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->is_admin){
            return JsonController::return('error', 403, 'You are not authorized to access this resource');
        }
        return JsonController::return('success', 200, 'User created', ['user' => DB::table('users')->insert($request->all())]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(!auth()->user()->is_admin){
            return JsonController::return('error', 403, 'You are not authorized to access this resource');
        }
        return JsonController::return('success', 200, 'User', ['user' => DB::table('users')->where('id', $id)->first()]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(!auth()->user()->is_admin){
            return JsonController::return('error', 403, 'You are not authorized to access this resource');
        }
        return JsonController::return('success', 200, 'User updated', ['user' => DB::table('users')->where('id', $id)->update($request->all())]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!auth()->user()->is_admin){
            return JsonController::return('error', 403, 'You are not authorized to access this resource');
        }
        return JsonController::return('success', 200, 'User deleted', ['user' => DB::table('users')->where('id', $id)->delete()]);
    }
}
