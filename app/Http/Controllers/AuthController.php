<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{   
    //Post
    //signup a user
    public function store(Request $request){

        $this->validate($request,[
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:5'
        ]);


        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');

        $user = new User([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password)
        ]);
        
        //if OK
        if($user->save()){
            $user-> signin = [
                'href' => 'api/v1/user/signin',
                'method' => 'POST',
                'params' => 'email,password'
            ];

            $response = [
                'msg' => 'User created',
                'user' => $user
            ];
            return response()->json($response,200);
        }

        //else
        $response = [
            'msg' => 'An Error has occured',
        ];
        return response()->json($response,400);
        
    }


    //Post
    //signin a user
    public function signin(Request $request){


        $this->validate($request,[
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $email = $request->input('email');
        $password = $request->input('password');
        $user = [
            'name' => 'Name',
            'email' => $email,
            'password' => $password
        ];

        $response = [
            'msg' => 'User signed in',
            'user' => $user
        ];

        return response()->json($response,200);

    }
}
