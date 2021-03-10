<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function __construct()
    {
        /* 中间件限制未登陆用户可访问页面 */
        $this->middleware('auth',[
            'except'=>['show','create','store','index']
        ]);
        //只让未登陆用户访问注册 页面
        $this->middleware('guest',[
            'only'=>['create']
        ]);
    }
    public function index(){
        $users=User::paginate(6);
        return view('users.index',compact('users'));
    }
    /* 注册用户 */
    public function create()
    {
        return view('users.create');
    }
    /* 显示用户 */
    public function show(User $user)
    {
        return view('users.show',compact('user'));
    }
    // 保存用户
    public function store(Request $request)
    {
        $this->validate($request,[
            'name'=>'required|unique:users|max:50',
            'email'=>'required|email|unique:users|max:255',
            'password'=>'required|confirmed|min:6'
        ]);

        $user=User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>bcrypt($request->password),
        ]);
        Auth::login($user);
        session()->flash('success','欢迎，您将在这里开启一段全新的旅程！');
        return redirect()->route('users.show',[$user]);
    }

    public function edit(User $user)
    {
        $this->authorize('update',$user);
        return view('users.edit',compact('user'));
    }
    public function update(User $user,Request $request)
    {
        $this->authorize('update',$user);
        $this->validate($request,[
            'name'=>'required|max:50',
            'password'=>'required|confirmed|min:6'
        ]);
        $data=[];
        $data['name']=$request->name;
        if($request->password){
            $data['password']=bcrypt($request->password);
        }
        $user->update($data);
        // 更新个人信息提示
        session()->flash('success','个人资料更新成功！');

        return redirect()->route('users.show',$user);
    }
    public function destroy(User $user)
    {
        //删除授权
        $this->authorize('destroy',$user);
        $user->delete();
        session()->flash('success','成功删除用户！');
        return back();
    }
}
