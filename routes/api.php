<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FiliereController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\UfrController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\ResponsableController;
use App\Models\Ufr;





/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

Route::post('/register-alumni', [AlumniController::class, 'register']);

/*
|--------------------------------------------------------------------------
| PASSWORD RESET
|--------------------------------------------------------------------------
*/
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
    ->name('password.forgot');

Route::post('/reset-password', [AuthController::class, 'resetPassword'])
    ->name('password.reset');

Route::get('/reset-password', [AuthController::class, 'showResetForm'])
    ->name('password.reset.form');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USER
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'getProfile']);
    Route::post('/profile', [ProfileController::class, 'updateProfile']);
    
});

Route::middleware('auth:sanctum')->get('/unread-count', [MessageController::class, 'getUnreadCount']);

/*
|--------------------------------------------------------------------------
| FILIERES (public ou auth selon ton choix)
|--------------------------------------------------------------------------
*/
Route::get('/filieres', [FiliereController::class, 'index']);

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES (SECURE + PROPRE)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum', RoleMiddleware::class . ':admin'])
    ->group(function () {

    // 📊 Alumni incomplets (sans diplôme)
Route::get('/alumni-without-diploma', [AdminController::class, 'alumniWithoutDiploma']);

// 📤 Upload diplôme
Route::post('/alumni/{id}/upload-diplome', [AdminController::class, 'uploadDiplome']);

// 📥 Import Excel
Route::post('/import-alumni', [AdminController::class, 'importAlumni']);

Route::post('/import-alumni', [AdminController::class, 'importAlumni']);
        // 📊 dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard']);

        // 👥 users management
        Route::get('/users', [AdminController::class, 'index']);
        Route::get('/pending', [AdminController::class, 'pending']);

        Route::post('/create-user', [AdminController::class, 'createUser']);

        Route::put('/approve/{id}', [AdminController::class, 'approve']);
        Route::put('/toggle/{id}', [AdminController::class, 'toggleStatus']);
        Route::put('/update/{id}', [AdminController::class, 'updateUser']);

        Route::delete('/delete/{id}', [AdminController::class, 'deleteUser']);
        Route::get('/stats-filiere', [AdminController::class, 'statsByFiliere']);
        Route::get('/export-users', [AdminController::class, 'exportUsers']);
        Route::get('/user-growth', [AdminController::class, 'userGrowth']);
        Route::get('/activity', [AdminController::class, 'activity']);
      Route::get('/profile/{id}', [ProfileController::class, 'adminGetProfile']);
Route::put('/profile/{id}', [AdminController::class, 'adminUpdateProfile']);
    });
//     Route::get('/profile', function () {
//     return response()->json([
//         'ok' => true
//     ]);
// });
Route::get('/entreprises', [EntrepriseController::class, 'index']);
Route::post('/entreprises', [EntrepriseController::class, 'store']);
    Route::get('/admin/pending-count', [AdminController::class, 'pendingCount']);
    Route::get('/admin/user/{id}', [AdminController::class, 'showUser']);
Route::put('/admin/user/{id}', [AdminController::class, 'updateUser']);
Route::middleware('auth:sanctum')->get('/test', function () {
    return auth()->user();
});



Route::get('/ufrs', function () {
    return Ufr::all();
});

Route::get('/departements-by-ufr/{id}', function ($id) {
    return \App\Models\Departement::where('ufr_id', $id)->get();
});

Route::get('/filieres-by-departement/{id}', function ($id) {
    return \App\Models\Filiere::where('departement_id', $id)->get();
});

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/alumni/user/{id}', [AlumniController::class, 'getPublicProfile']);

});

Route::post('/ufrs', [UfrController::class, 'store']);
Route::post('/departements', [DepartementController::class, 'store']);
Route::post('/filieres', [FiliereController::class, 'store']);
//Route::middleware('auth:sanctum')->get('/profile/{id}', [ProfileController::class, 'getPublicProfile']);

Route::get('/ping', function () {
    return response()->json(['ok' => true]);
});
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/conversation/{userId}', [MessageController::class, 'getConversation']);
   Route::post('/messages/send', [MessageController::class, 'send']);
    Route::delete('/messages/{id}', [MessageController::class, 'delete']);
   // Route::post('/messages/send-audio', [MessageController::class, 'sendAudio']);
//Route::post('/messages/send-media', [MessageController::class, 'sendMedia']);

});

// Route::middleware('auth:sanctum')->get(
//     '/alumni/user/{id}',
//     [AlumniController::class, 'getPublicProfile']
// );
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/alumni/users', [AlumniController::class, 'listUsers']);
});


// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('/messages/read/{id}', [MessageController::class, 'markAsRead']);
//     Route::post('/messages/delivered/{id}', [MessageController::class, 'markAsDelivered']);
// });
Route::get('/conversations', [MessageController::class, 'getConversations']);
Route::middleware('auth:sanctum')->get('/conversations', [ConversationController::class, 'index']);
Route::post('/conversations/{id}/read', [ConversationController::class, 'markAsRead']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/messages/delete/{id}', [MessageController::class, 'delete']);
    
});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/ping-online', [MessageController::class, 'pingOnline']);

    Route::post('/chat/enter/{conversationId}', [MessageController::class, 'enterChat']);

    Route::post('/chat/leave/{conversationId}', [MessageController::class, 'leaveChat']);

    Route::post('/messages/delivered/{conversationId}', [MessageController::class, 'markAsDelivered']);

    Route::post('/messages/read/{conversationId}', [MessageController::class, 'markAsRead']);

    Route::post('/messages/send', [MessageController::class, 'send']);

    Route::post('/messages/media', [MessageController::class, 'sendMedia']);

    Route::post('/messages/audio', [MessageController::class, 'sendAudio']);
    Route::post('/messages/delivered-single', [MessageController::class, 'markDeliveredSingle']);
});

Route::middleware(['auth:sanctum'])->prefix('responsable')->group(function () {

    Route::get('/dashboard', [ResponsableController::class, 'dashboard']);
    Route::get('/alumni', [ResponsableController::class, 'alumni']);
    Route::get('/alumni/{id}', [ResponsableController::class, 'show']);

    Route::get('/stats/promotion', [ResponsableController::class, 'statsPromotion']);
    Route::get('/growth', [ResponsableController::class, 'growth']);

    Route::get('/export', [ResponsableController::class, 'export']);

});

Route::get('/db-test', function () {
    return DB::table('users')->get();
});