<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-03 12:52:29
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-03 18:20:19
 */


namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use Hash;
use Session;

class CustomAuthController extends Controller
{
    public function login() {
        return view('auth.login');
    }
    public function register() {
        return view('auth.register');
    }
    public function registerUser(Request $request) {
        $request->validate([
            'name'=>'required',
            'email'=>'required|email|unique:users',
            'password'=>'required|min:5|max:12'
        ]);
        $user = new User();
        $user->name=$request->name;
        $user->email=$request->email;
        $user->password= Hash::make($request->password);
        $res = $user->save();

        if($res) {
            return back()->with('success', 'You have been successfully registered');
        } else {
            return back()->with('fail', 'Something went wrong');
        }
    }
    public function loginUser(Request $request) {
        $request->validate([
            'email'=>'required|email',
            'password'=>'required|min:5|max:12'
        ]);
        $user = User::where('email', '=', $request->email)->first();
        if($user) {
            if (Hash::check($request->password, $user->password)) {
                $request->session()->put('loginId', $user->id);
                return redirect('dashboard');
            } else {
                return back()->with('fail', 'Incorrect password');
            }
        } else {
            return back()->with('fail', 'No account found for this email');
        }
    }
    public function dashboard() {
        $data = array();
        if(Session::has('loginId')) {
            $data = User::where('id', '=', Session::get('loginId'))->first();
        }
        $allOrders = DB::table('orders')->get();
        return view('dashboard', compact('data', 'allOrders'));
    }

    public function logout() {
        if(Session::has('loginId')) {
            Session::pull('loginId');
            return redirect('login');
        }
    }
}
