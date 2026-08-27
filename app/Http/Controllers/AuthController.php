<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Auth;

class AuthController extends Controller
{

    public function login()
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }
        return view('auth.signin');
    }

    public function loginProcess(Request $request)
    {
        $this->loginValidator($request->all())->validate();
        $credentials = $request->only(['username', 'password']);
        $remember = $request->get('remember', false);
        if ($this->guard()->attempt($credentials, $remember)) {
            return $this->sendLoginResponse($request);
        }
        return back()->withInput()->withErrors([
            'username' => $this->getFailedLoginMessage(),
        ]);
    }

    /**
     * @return logout
     * clear session 
     * get method
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        return redirect(route('login'));
    }




    /**
     * @return 
     * validate login
     */
    protected function loginValidator(array $data)
    {
        return \Validator::make($data, [
            'username' => 'required',
            'password' => 'required',
        ]);
    }


    protected function getFailedLoginMessage()
    {
        return \Lang::has('auth.failed')
            ? trans('auth.failed')
            : 'These credentials do not match our records.';
    }


    /**
     * @return
     * generate session
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        $user =  \Auth::guard(config('permission.guard'))->user();
        \Cache::forget('user-permissions-' . $user->id);
        return redirect()->intended($this->redirectPath())->with(['success' => 'Login Successfully']);
    }


    protected function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }
        return property_exists($this, 'redirectTo') ? $this->redirectTo : route('admin.dashboard');
    }


    protected function guard()
    {
        return Auth::guard('admin');
    }
}
