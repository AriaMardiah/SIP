<?php

use App\Http\Controllers\Api\DashboardAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\ReadmeController;
use App\Http\Controllers\Api\ChatTemplateController;
use App\Http\Controllers\Api\AdminTaskController;
use App\Http\Controllers\Api\PetugasTaskController;
use App\Http\Controllers\Api\PelayananController;
use App\Http\Controllers\Api\ReportPelayananController;


Route::post('/login', [AuthController::class, 'login']);
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);

    Route::get('/admin/task-recap', [AdminTaskController::class, 'getCumulativeReport']);
    Route::post('/admin/tasks', [AdminTaskController::class, 'storeTask']);
    Route::put('/admin/tasks/{id}', [AdminTaskController::class, 'updateTask']);
    Route::delete('/admin/tasks/{id}', [AdminTaskController::class, 'destroyTask']);
    
    Route::get('/admin/service-types-list', [PelayananController::class, 'index']);
    Route::get('/admin/pelayanan-report', [PelayananController::class, 'getMonthlyReport']);
    Route::post('/admin/services-init', [PelayananController::class, 'initService']);
    Route::put('/admin/services-rename', [PelayananController::class, 'renameService']);
    Route::delete('/admin/services-remove', [PelayananController::class, 'removeService']);
    
    Route::get('/petugas/services', [ReportPelayananController::class, 'index']);
    Route::post('/petugas/services', [ReportPelayananController::class, 'store']);
    Route::patch('/petugas/services/{id}/progress', [ReportPelayananController::class, 'updateProgress']);
    Route::delete('/petugas/services/{id}', [ReportPelayananController::class, 'destroy']);
    
    Route::get('/petugas/daily-task', [PetugasTaskController::class, 'index']);
    Route::post('/task-reports', [PetugasTaskController::class, 'store']);

    Route::apiResource('schedules', ScheduleController::class);
    Route::get('/my-schedules', [ScheduleController::class, 'mySchedule']);

    Route::apiResource('template-chats', ChatTemplateController::class);

    Route::apiResource('readmes', ReadmeController::class);

    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', fn($request) => $request->user());
    Route::get('/DashboardAdmin', [DashboardAdmin::class,'index']);
});
