<?php

namespace App\Http\Controllers;

use App\Models\User;
use Hash;
use Illuminate\Support\Str;
use DB;
use Mail;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }
    public function sendResetLinkEmail(Request $request)
    {
        //验证邮箱
        $request->validate(['email'=>'required|email']);
        $email=$request->email;
        //获取对应用户
        $user=User::where('email',$email)->first();

        //如果用户不存在
        if(is_null($user)){
            session()->flash('danger','邮箱未注册');
            return redirect()->back()->withInput();
        }

        //4生成Token，会在视图emails.reset_link里面拼接链接
        $token=hash_hmac('sha256',Str::random(40),config('app.key'));
        //保存到数据库
        DB::table('password_resets')->updateOrInsert(['email'=>$email],[
            'email'=>$email,
            'token'=>Hash::make($token),
            'created_at'=>new Carbon,
        ]);
        //6将tokey链接发送给用户
        Mail::send('emails.reset_link',compact('token'),function($message) use ($email){
            $message->to($email)->subject('忘记密码');
        });
        session()->flash('success','重置密码邮件发送成功，请查收');
        return redirect()->back();
    }
}
