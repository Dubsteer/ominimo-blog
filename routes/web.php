<?php

use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::post('/posts/{post}/comments', [CommentController::class, 'store'])
    ->middleware('throttle:comments')
    ->name('comments.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function (): void {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    Route::patch('/comments/{comment}/status', [CommentController::class, 'updateStatus'])->name('comments.status.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:moderator,administrator'])
    ->group(function (): void {
        Route::get('/', [ModerationController::class, 'index'])->name('index');
        Route::patch('/posts/{post}/status', [ModerationController::class, 'updatePostStatus'])->name('posts.status.update');
        Route::patch('/comments/{comment}/status', [ModerationController::class, 'updateCommentStatus'])->name('comments.status.update');

        Route::middleware('role:administrator')->group(function (): void {
            Route::get('/users', [UserRoleController::class, 'index'])->name('users.index');
            Route::patch('/users/{user}/role', [UserRoleController::class, 'update'])->name('users.role.update');
        });
    });

Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
