<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Auth;
use Illuminate\Http\Request;

class StatusesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function store(Request $request)
    {
        //验证微博内容字段
        $this->validate($request, [
            'content' => 'required|max:140',
        ]);
        //创建内容，并发布
        Auth::user()->statuses()->create([
            'content' => $request['content'],
        ]);
        session()->flash('success', '动态发布成功！');
        return redirect()->back();
    }
    //删除微博动态
    public function destroy(Status $status)
    {
        $this->authorize('destroy', $status);
        $status->delete();
        session()->flash('success', '微博已被成功删除！');
        return redirect()->back();
    }
}
