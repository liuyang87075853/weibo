<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaticPagesController extends Controller
{
    //
    public function home()
    {
        return view('static_pages/home');
    }
    public function help()
    {
        return view('static_pages/help');
    }
    public function about()
    {
        return view('static_pages/about');
    }
    public function welcome(){
        return view('static_pages/welcome');
    }
    public function form(Request $request)
    {
        // 通过 $request 实例获取请求数据
    $id=$request->has('id') ? $request->get('id'):0;

    dd($request->all());
    }
}
