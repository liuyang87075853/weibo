<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mail;

class PasswordController extends Controller
{
    public function __construct()
    {
        //找回密码页面限流，每1分钟可访问2次
        $this->middleware('throttle:2,1', [
            'only' => ['showLinkRequestForm'],
        ]);
        //发送密码重置邮件限流，每10分钟3次，
        $this->middleware('throttle:3,10', [
            'only' => ['sendResetLinkEmail'],
        ]);
    }
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }
    //处理找回密码发送邮件
    public function sendResetLinkEmail(Request $request)
    {
        //验证邮箱
        $request->validate(['email' => 'required|email']);
        $email = $request->email;
        //获取对应用户
        $user = User::where('email', $email)->first();

        //如果用户不存在
        if (is_null($user)) {
            session()->flash('danger', '邮箱未注册');
            return redirect()->back()->withInput();
        }

        //4生成Token，会在视图emails.reset_link里面拼接链接
        $token = hash_hmac('sha256', Str::random(40), config('app.key'));
        //保存到数据库
        DB::table('password_resets')->updateOrInsert(['email' => $email], [
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => new Carbon,
        ]);
        //6将tokey链接发送给用户
        Mail::send('emails.reset_link', compact('token'), function ($message) use ($email) {
            $message->to($email)->subject('忘记密码');
        });
        session()->flash('success', '重置密码邮件发送成功，请查收');
        return redirect()->back();
    }
    //显示重置密码页面
    public function showResetForm(Request $request)
    {
        $token = $request->route()->parameter('token');
        return view('auth.passwords.reset', compact('token'));
    }
    //处理重置密码数据
    public function reset(Request $request)
    {
        //验证数据
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);
        $email = $request->email;
        $token = $request->token;
        //挽回密码链接有效时间
        $expires = 60 * 10;

        //获取对应用户
        $user = User::where('email', $email)->first();
        //判断用户是否存在
        if (is_null($user)) {
            session()->flash('danger', '邮箱未注册');
            return redirect() - back()->withInput();
        }
        //读取重置的记录
        $record = (array) DB::table('password_resets')->where('email', $email)->first();

        //如果记录存在
        if ($record) {
            //检查是否过期
            if (Carbon::parse($record['created_at'])->addSeconds($expires)->isPast()) {
                session()->flash('danger', '链接已过期，请重新尝试');
                return redirect()->back();
            }
            //检查是否正确
            if (!Hash::check($token, $record['token'])) {
                session()->flash('danger', '令牌错误');
                return redirect()->back();
            }
            //一切正常，更新用户密码
            $user->update(['password' => bcrypt($request->password)]);

            //提示用户更新成功
            session()->flash('success', '密码重置成功，请使用新密码登录');
            return redirect()->route('login');
        }
        //记录不存在
        session()->flash('danger', '未找到重置记录');
        return redirect()->back();
    }

}
