<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        if (isset($user)) {

            $tasks = Task::where("user_id", $user->id)->get();
            $user->img = $user->img
                ? asset("storage/user_imgs/$user->img")
                : asset("images/default.jpeg");

            return response()->json([
                "status" => true,
                'user' => $user,
                "tasks" => $tasks
            ], 200);

        } else {
            return response()->json([
                "satus" => false,
            ]);
        }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile("img")) {
            $newName = uniqid() . '.' . $request->img->extension();
            $request->img->storeAs('user_imgs', $newName, 'public');
            $data["img"] = $newName;
        }

        $user = User::create($data);
        $token = $user->createToken('api-token')->plainTextToken;

        try {
            event(new Registered($user));
        } catch (\Throwable $e) {}

        return response()->json([
            'user' => $user,
            "token" => $token
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        if (Auth::id() != $id) {
            return response()->json(["error" => "Unauthorized"], 403);
        }

        $old_user = Auth::user();

        $validator = Validator::make($request->all(), [
            "name" => "required|string|min:3",
            "email" => "required|email|min:3|unique:users,email,$id",
            "img" => "nullable|image|mimes:jpg,jpeg,png",
            "gender" => "required|in:male,female",
        ]);

        if($request->hasFile("img")){
            if($old_user["img"] != "defeult.png"){
                $oldPath = storage_path("app/public/user_imgs/" . $old_user->img);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $newName = uniqid() . '.' . $request->img->extension();
            $request->img->storeAs('user_imgs', $newName, 'public');

        }else{
             $newName = $old_user->img;
        }

        User::where("id", $id)->update([
            "name" => $request->name,
            "email" => $request->email,
            "gender" => $request->gender,
            "img" => $newName
        ]);


        return response()->json([
            'status' => 'success',
            'user' => User::find($id)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (Auth::id() != $id) {
            return response()->json(["error" => "Unauthorized"], 403);
        }

        User::where("id", $id)->delete();
    }

    public function login(Request $request)
    {

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

        $token = $user->createToken('api-token')->plainTextToken;



        return response()->json([
            'status' => true,
            'user' => $user,
            'token' => $token
        ], 200);

    }


}
