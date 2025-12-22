<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // اختبر البيانات الأول
        // dd($request->all());

        $validated = $request->validate([
            'name' => 'required|string|min:3',
            'tag' => 'required|string|min:3',
            'type' => 'required|string|min:3',
        ]);

         Task::create([
            'name' => $validated['name'],
            'tag' => $validated['tag'],
            'type' => $validated['type'],
            "user_id" => Auth::user()->id
        ]);

        return response()->json([
            'status' => 'success',
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json([
            Task::findOrFail($id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
         $validated = $request->validate([
            'name' => 'required|string|min:3',
            'tag' => 'required|string|min:3',
            'type' => 'required|string|min:3',
        ]);

        Task::where("id" , $id)->update([
            'name' => $validated['name'],
            'tag' => $validated['tag'],
            'type' => $validated['type'],
            "user_id" => Auth::user()->id
        ]);

        return response()->json([
            'status' => 'success',
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Task::where("id" , $id)->delete();
        return response()->json([
            "status" => true
        ]);
    }
}
