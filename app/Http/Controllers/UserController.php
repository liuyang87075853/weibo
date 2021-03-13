<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mail;

class UserController extends Controller
{
    public function __construct()
    {
        /* 中间件限制未登陆用户可访问页面 */
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'index', 'confirmEmail'],
        ]);
        //只让未登陆用户访问注册 页面
        $this->middleware('guest', [
            'only' => ['create'],
        ]);
        //注册限流，一个小时内，最多提交10次
        $this->middleware('throttle:10,60', [
            'only' => ['store'],
        ]);
    }
    public function index()
    {
        $users = User::paginate(6);
        return view('users.index', compact('users'));
    }
    /* 注册用户 */
    public function create()
    {
        return view('users.create');
    }
    /* 显示用户 */
    public function show(User $user)
    {
        $statuses = $user->statuses()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('users.show', compact('user', 'statuses'));
    }
    // 保存用户
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:users|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        $this->sendEmailConfirmationTo($user);

        Auth::login($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收！');
        return redirect('/');
    }
    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'summer@example.com';
        $name = 'Summer';
        $to = $user->email;
        $subject = "感谢注册 Weibo 应用！请确认你的注册邮箱。";

        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }
    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user);
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'required|confirmed|min:6',
        ]);
        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);
        // 更新个人信息提示
        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $user);
    }
    public function destroy(User $user)
    {
        //删除授权
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();
        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', "恭喜您，激活成功！");
        return redirect()->route('users.show', [$user]);
    }
}
