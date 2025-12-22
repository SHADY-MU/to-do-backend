<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->apiResource('tasks', TaskController::class);
Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'index']);
Route::post('/user', [UserController::class, 'store']);

Route::middleware('auth:sanctum')->apiResource('user', UserController::class)->except(['index' , "store"]);

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = \App\Models\User::find($id);

    if (!$user) {
        return response()->json(["message" => "Invalid user"], 400);
    }

    if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return response()->json(["message" => "Invalid hash"], 400);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json(["message" => "Email already verified"], 200);
    }

    $user->markEmailAsVerified();
    event(new \Illuminate\Auth\Events\Verified($user));

    return response()->json(["message" => "Email verified successfully"], 200);
})->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return response()->json(["message" => "Email already verified"], 200);
    }
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification link sent!']);
})->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');

Route::post("login", [UserController::class, "login"])->name("login");

