<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $success_status = 200;

    public function registerUser(Request $request)
    {
        // --------------------- [ User Registration ] ---------------------------

        $validator = Validator::make($request->all(),
            [
                'name' => 'required',
                'email' => 'required|unique:users',
                'password' => 'required',
                'user_type' => 'required|in:1,2'
            ]
        );

        // if validation fails
        if ($validator->fails()) {

            return response()->json(["success" => false, "status" => 400, "msg" => $validator->errors()]);

        }

        $input = array(
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'user_type' => $request->user_type
        );

        $user = User::create($input);
        return response()->json(["success" => true, "status" => 200, "user" => $user]);

    }

    // --------------------------- [ User Login ] ------------------------------

    public function loginUser(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                'email' => 'required',
                'password' => 'required',
                //'user_type' => 'required',
            ]
        );

        // check if validation fails
        if ($validator->fails()) {
            return response()->json(["validation errors" => $validator->errors()]);
        }

        $email = $request->email;
        $password = $request->password;

        $user = DB::table("users")->where("email", "=", $email)->first();

        if (is_null($user)) {

            return response()->json(["success" => false, "msg" => "Email doesn't exist"]);

        }

        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {

            $user = Auth::user();

            $token = $user->createToken('token')->accessToken;
            $success['success'] = true;
            $success['msg'] = "Success! you are logged in successfully";
            $success['user']  = $user;
            $success['token'] = $token;

            return response()->json(['success' => $success], $this->success_status);

        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }


    // ---------------------------- [ Use Detail ] -------------------------------
    public function userDetail()
    {

        $user = Auth::user();

        return response()->json(['success' => $user], $this->success_status);

    }


    // -------------------------- [ Edit Using Passport Auth ]--------------------
    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(),
            [
                'name' => 'required',
                'email' => 'required',
                'password' => 'required',
            ]
        );

        // if validation fails

        if ($validator->fails()) {
            return response()->json(["validation errors" => $validator->errors()]);
        }

        $userDataArray = array(
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        );

        $user = User::where('id', $user->id)->update($userDataArray);

        return response()->json(['success' => true, 'message' => 'User updated successfully']);

    }


// ----------------------------- [ Delete User ] -----------------------------
    public function deleteUser()
    {

        $user = Auth::user();

        $user = User::findOrFail($user->id);
        $user->delete();
        return response()->json(['success' => true, 'message' => 'User deleted successfully']);
    }
}
