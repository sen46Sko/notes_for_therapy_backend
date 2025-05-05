<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminStatsController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MoodController;
use App\Http\Controllers\MoodRelationController;
use App\Http\Controllers\StripeControllerV2;
use App\Http\Controllers\WeeklyWordsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\NoteQuestionController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AppleAuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\GoalTemplateController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\HomeworkController;
use App\Http\Controllers\HomeworkTemplateController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\SubscriptionWebhookController;
use App\Http\Controllers\SymptomController;
use App\Http\Controllers\TwoFactorAuthController;
use App\Http\Controllers\UserActionController;
use App\Http\Controllers\UserExperienceController;
use App\Http\Controllers\UserSymptomController;
use App\Http\Controllers\UserNotificationSettingController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\AppSettingsController;
use App\Http\Controllers\AdminTwoFactorController;
use App\Http\Controllers\SecuritySettingsController;
use App\Http\Controllers\AdminPasswordController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\DeviceManagementController;
use App\Http\Controllers\AdminNotificationController;
use App\Http\Controllers\AdminNotificationSettingController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum', 'admin.auth'])->group(function () {

    Route::get('/admin/test-session-timeout', function() {
        $admin = auth()->user();
        $settings = \App\Models\SecuritySettings::first();

        if (!$admin || !$admin->last_activity_at) {
            return response()->json(['error' => 'No activity data']);
        }

        $lastActivity = $admin->last_activity_at;
        $now = \Carbon\Carbon::now();
        $diffInMinutes = $now->diffInMinutes($lastActivity);

        return response()->json([
            'admin_id' => $admin->id,
            'last_activity_at' => $lastActivity->toDateTimeString(),
            'current_time' => $now->toDateTimeString(),
            'minutes_since_last_activity' => $diffInMinutes,
            'timeout_setting' => $settings->session_timeout_duration,
            'timeout_enabled' => $settings->session_timeout_enabled,
            'should_expire' => $diffInMinutes >= 1
        ]);
    });
});



// Admin Routes

// Admin auth routes
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])
    ->middleware(['auth:sanctum', 'admin.auth']);
Route::post('/admin/confirm-register', [AdminAuthController::class, 'confirmRegister']);

// Admin 2FA public routes
Route::post('/admin/2fa/verify-login', [AdminTwoFactorController::class, 'verifyDuringLogin']);
Route::post('/admin/2fa/resend', [AdminTwoFactorController::class, 'resendCode']);


// Get Settings

Route::get('/admin/app-settings', [AppSettingsController::class, 'getSettings']);

// Admin register link
Route::get('/admin-register/{uuid}', [AdminAuthController::class, 'validateRegistrationLink'])
    ->name('admin.register')
    ->middleware('admin.registration.link');




// Admin protected routes
Route::middleware(['auth:sanctum', 'admin.auth', 'check.device.blocked', 'check.session.timeout'])->group(function () {

    // Notifications
    Route::get('/admin/global-notification-settings', [AdminNotificationSettingController::class, 'getGlobalSettings'])
        ->middleware('check.permission:view_notification_setting');
    Route::post('/admin/global-notification-settings', [AdminNotificationSettingController::class, 'updateGlobalSettings'])
        ->middleware('check.permission:view_notification_setting');
    Route::get('/admin/notification-settings', [AdminNotificationSettingController::class, 'index']);
    Route::post('/admin/notification-settings', [AdminNotificationSettingController::class, 'update']);


    Route::get('/admin/notifications', [AdminNotificationController::class, 'index']);
    Route::get('/admin/notifications/unread', [AdminNotificationController::class, 'unread']);
    Route::post('/admin/notifications/{id}/read', [AdminNotificationController::class, 'markAsRead']);
    Route::post('/admin/notifications/read-all', [AdminNotificationController::class, 'markAllAsRead']);

    // Password expire
    Route::post('/admin/change-password', [AdminPasswordController::class, 'changePassword']);
    Route::get('/admin/check-password-expiration', [AdminPasswordController::class, 'checkPasswordExpiration']);

    // Session expire
    Route::post('/admin/update-activity', [SessionController::class, 'updateActivity'])->middleware('update.activity');

    Route::middleware(['check.password.expiration'])->group(function () {


        // Stats (View Analytics)
        Route::get('/admin/stats', [AdminStatsController::class, 'stats'])
            ->middleware('check.permission:view_analytics');

        Route::get('/admin/user-activity', [AdminStatsController::class, 'userActivity'])
            ->middleware('check.permission:view_admin_logs');

        Route::get('/admin/users', [AdminUserController::class, 'users'])
            ->middleware('check.permission:view_users');

        Route::get('/admin/analytics/user-engagement', [AdminUserController::class, 'engagement'])
            ->middleware('check.permission:view_analytics');

        Route::get('/admin/analytics/stats', [AdminUserController::class, 'stats'])
            ->middleware('check.permission:view_analytics');

        Route::get('/admin/users/{id}', [AdminUserController::class, 'userDetails'])
            ->middleware('check.permission:view_users');

        Route::get('/admin/analytics/retention', [AdminUserController::class, 'retention'])
            ->middleware('check.permission:view_analytics');

        Route::post('/admin/users/deactivate', [AdminUserController::class, 'deactivateUser'])
            ->middleware('check.permission:suspend_user');

        Route::post('/admin/users/activate', [AdminUserController::class, 'activateUser'])
            ->middleware('check.permission:suspend_user');

        Route::get('/admin/analytics/subscriptions', [AdminStatsController::class, 'subscriptions'])
            ->middleware('check.permission:view_analytics');

        Route::get('/admin/analytics/map-stats', [AdminStatsController::class, 'getMapStats'])
            ->middleware('check.permission:view_analytics');

        // User actions
        Route::get('/activity/user-actions', [UserActionController::class, 'getUserActions'])
            ->middleware('check.permission:view_admin_logs');

        // Admin management
        Route::post('/admin/create-admin', [AdminAuthController::class, 'createAdmin'])
            ->middleware('check.permission:invite_user');

        Route::get('/admin/get-admins', [AdminAuthController::class, 'getAdmins'])
            ->middleware('check.permission:view_users');

        Route::post('/admin/update-admin-role/{id}', [AdminAuthController::class, 'updateAdminRole'])
            ->middleware('check.permission:assign_roles');

        Route::post('/admin/update-admin-permission/{id}', [AdminAuthController::class, 'updateAdminPermission'])
            ->middleware('check.permission:modify_permissions');

        Route::post('/admin/remove-admin', [AdminAuthController::class, 'removeAdmin'])
            ->middleware('check.permission:delete_user');

        Route::post('/admin/deactivate-admin', [AdminAuthController::class, 'deactivateAdmin'])
            ->middleware('check.permission:suspend_user');

        Route::post('/admin/activate-admin', [AdminAuthController::class, 'activateAdmin'])
            ->middleware('check.permission:suspend_user');

        Route::post('/admin/update-profile', [AdminAuthController::class, 'updateProfile']);

        // Ticket system
        Route::post('/admin/tickets/change-status/{id}', [TicketController::class, 'changeStatus'])
            ->middleware('check.permission:assign_tickets');

        Route::post('/admin/tickets/change-note/{id}', [TicketController::class, 'changeNote'])
            ->middleware('check.permission:respond_tickets');

        Route::post('/admin/tickets/message/{id}', [TicketController::class, 'adminSendMessage'])
            ->middleware('check.permission:respond_tickets');

        Route::get('/admin/tickets/get-stats', [TicketController::class, 'getStats'])
            ->middleware('check.permission:view_tickets');

        Route::get('/admin/tickets', [TicketController::class, 'listTickets'])
            ->middleware('check.permission:view_tickets');

        Route::get('/admin/tickets/{id}', [TicketController::class, 'getTicketDetails'])
            ->middleware('check.permission:view_tickets');

        Route::get('/admin/feedback', [FeedbackController::class, 'index'])
            ->middleware('check.permission:view_tickets');

        // Settings
        Route::post('/admin/update-settings', [AppSettingsController::class, 'updateSettings'])->middleware('check.permission:restrict_features');

        Route::get('/admin/security-settings', [SecuritySettingsController::class, 'getSettings'])->middleware('check.permission:restrict_features');;

        Route::post('/admin/security-settings', [SecuritySettingsController::class, 'updateSettings'])->middleware('check.permission:restrict_features');;

        Route::post('/admin/security-settings/reset', [SecuritySettingsController::class, 'resetToDefault'])->middleware('check.permission:restrict_features');;

     });

     // Device Management
         Route::prefix('admin/devices')->group(function () {
             Route::get('/', [DeviceManagementController::class, 'getDevices'])
                 ->middleware('check.permission:view_security_logs');
             Route::post('/block', [DeviceManagementController::class, 'blockDevice'])
                 ->middleware('check.permission:manage_login');
             Route::post('/unblock', [DeviceManagementController::class, 'unblockDevice'])
                 ->middleware('check.permission:manage_login');
             Route::post('/force-logout', [DeviceManagementController::class, 'forceLogout'])
                 ->middleware('check.permission:manage_login');
         });

         Route::get('/admin/activity-logs', [DeviceManagementController::class, 'getActivityLogs'])
             ->middleware('check.permission:view_security_logs');

         Route::get('/admin/failed-logins', [DeviceManagementController::class, 'getFailedLoginAttempts'])
             ->middleware('check.permission:view_security_logs');
});


// User Routes

Route::get('', function () {
    return response()->json(['message' => 'Welcome to Notes For Therapy API']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//   Start Authentication
Route::post('socialLogin', [ApiController::class, 'socialLogin']);
Route::get('socialLogin', [ApiController::class, 'socialLogin']);
Route::post('login', [ApiController::class, 'authenticate']);
Route::post('register', [ApiController::class, 'register']);
Route::post('forgot', [ApiController::class, 'forgot']);
Route::post('verify', [ApiController::class, 'VerifyCode']);
Route::post('reset_password', [ApiController::class, 'ResetPassword']);
Route::get('getcountry', [ApiController::class, 'getcountry']);
Route::get('getstates/{countryid}', [ApiController::class, 'getstates']);
Route::get('getcities/{stateid}', [ApiController::class, 'getcities']);

// Google OAuth
Route::post('auth/google', [AuthController::class, 'googleOAuth']);

Route::post('auth/apple/sign-in', [AppleAuthController::class, 'signIn']);
Route::post('auth/apple/sign-up', [AppleAuthController::class, 'signUp']);

// Route::get('auth/google', [AuthController::class, 'redirectToProvider']);
// Route::get('auth/google/callback', [AuthController::class, 'handleProviderCallback']);

// Password reset V2

Route::get("auth/request_password_change", [AuthController::class, 'requestPasswordChange']);
Route::get("auth/check_otp", [AuthController::class, 'checkOtp']);
Route::post("auth/change_password", [AuthController::class, 'changePassword']);
Route::group(['middleware' => ['jwt.verify']], function () {
    Route::get("auth/request_email_change", [AuthController::class, 'requestEmailChange']);
    Route::post("auth/change_email", [AuthController::class, 'changeEmail']);
    Route::post("auth/change_password/authorized", [AuthController::class, 'changePasswordAuthorized']);
    Route::post('auth/alert', [AuthController::class, 'unsuccessfulAuthAlert']);

});

// Two Factor Authentication

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::post('2fa/init', [TwoFactorAuthController::class, 'initTwoFactor']);
    Route::post('2fa/verify', [TwoFactorAuthController::class, 'verifyTwoFactor']);
    Route::post('2fa/disable', [TwoFactorAuthController::class, 'disableTwoFactor']);
});

Route::post('2fa/verify-code', [TwoFactorAuthController::class, 'verifyCode']);

//    End Authentication



//  Stripe Webhook

Route::post('webhook/stripe', [StripeControllerV2::class, 'webhook']);

Route::post('payments/apple/webhook', [SubscriptionWebhookController::class, 'handleApplePayWebhook']);
Route::post('payments/apple/webhook/sandbox', [SubscriptionWebhookController::class, 'handleApplePayWebhook']);

Route::post('payments/google/webhook', [SubscriptionWebhookController::class, 'handleGooglePayWebhook']);
Route::post('payments/google/webhook/sandbox', [SubscriptionWebhookController::class, 'handleGooglePayWebhook']);

Route::middleware('jwt.verify')->post('/subscription/check-token', [SubscriptionWebhookController::class, 'checkToken']);

Route::group(['middleware' => ['jwt.verify']], function () {


    // Start User Action

    Route::post('/record-action', [UserActionController::class, 'recordAction']);

    // End User Action


    //   Start User Api
    Route::post('logout', [ApiController::class, 'logout']);
    Route::post('delete_account', [ApiController::class, 'delete_account']);
    Route::post('change_password', [ApiController::class, 'change_password']);
    Route::get('profile', [ApiController::class, 'get_user']);
    Route::get('user_list', [ApiController::class, 'user_list']);
    Route::get('trainer', [ApiController::class, 'trainer']);
    Route::get('trainertoclient', [ApiController::class, 'trainertoclient']);
    Route::get('add_client_to_trainer/{id}', [ApiController::class, 'add_client_to_trainer']);
    Route::post('update_profile', [ApiController::class, 'update_profile']);
    Route::get('deleteAccount', [ApiController::class, 'destroy']);
    Route::get('edit_profle/{id}', [ApiController::class, 'edit_profle']);

    //      End User Api


    // Users Tickets

    Route::post('/problems/message/{id}', [TicketController::class, 'userSendMessage']);
    Route::get('/user/tickets', [TicketController::class, 'getUserTickets']);

    // Home analytics

    Route::prefix('home')->group(function () {
        Route::get('chart', [AnalyticsController::class, 'homeChart']);
        Route::get('goals', [AnalyticsController::class, 'homeGoals']);
        Route::get('homeworks', [AnalyticsController::class, 'homeHomeworks']);
        Route::get('search', [AnalyticsController::class, 'search']);
    });

    // End Home analytics


    // Start Goal

    // Goal routes
    Route::get('goals', [GoalController::class, 'index']);
    Route::post('goals', [GoalController::class, 'store']);
    Route::get('goals/{id}', [GoalController::class, 'show']);
    Route::put('goals/{id}', [GoalController::class, 'update']);
    Route::delete('goals/{id}', [GoalController::class, 'destroy']);

    // GoalTemplate routes
    Route::get('goal-templates', [GoalTemplateController::class, 'index']);
    Route::post('goal-templates', [GoalTemplateController::class, 'store']);
    Route::get('goal-templates/{id}', [GoalTemplateController::class, 'show']);
    Route::put('goal-templates/{id}', [GoalTemplateController::class, 'update']);
    Route::delete('goal-templates/{id}', [GoalTemplateController::class, 'destroy']);

    //   Start Note
    // Route::resource('note', NoteController::class);
    //    End Note////

    Route::get('/note', [NoteController::class, 'index']);
    Route::post('/note', [NoteController::class, 'store']);
    Route::put('/note/{id}', [NoteController::class, 'update']);
    Route::delete('/note/{id}', [NoteController::class, 'destroy']);
    Route::get('/note/activity', [NoteController::class, 'activity']);
    Route::get('/note-questions', [NoteQuestionController::class, 'index']);

    //   Start Note
    Route::resource('notification', NotificationController::class);
    Route::post('notification/seen', [NotificationController::class, 'seen']);
    Route::post('notification/seen/{id}', [NotificationController::class, 'seenById']);
    Route::post('notification/hide', [NotificationController::class, 'hide']);
    Route::post('notification/hide/{id}', [NotificationController::class, 'hideById']);
    Route::get('notification_by_date_time/{date}/{time}', [NotificationController::class, 'index']);
    //    End Note//


    // Start Problem
    Route::get('/auth/problems', [ProblemController::class, 'index']);
    Route::post('/auth/problems', [ProblemController::class, 'store']);
    // End Problem


    // Start User Experience

    Route::resource('user-experiences', UserExperienceController::class);
    Route::resource('notification-settings', UserNotificationSettingController::class);

    // End User Experience

    // Start User Feedback

    Route::post('/feedback', [FeedbackController::class, 'store']);

    // End User Feedback

    //   Start Goal
    // Route::resource('goal', GoalController::class);
    //    End Goal

    // start goal tracking
    // Route::resource('goal_tracking', GoalController::class);
    // Route::post('goal_tracking_by_date/{id}', [GoalController::class, 'index']);

    //   Start Symptom
    //    End Symptom
    //   Start SymptomTracking
    //    End SymptomTracking

    //   Start GoalTracking
    // Route::resource('goal_tracking', GoalTrackingController::class);
    // Route::post('goal_tracking_by_date/{id}', [GoalTrackingController::class, 'index']);
    //    End GoalTracking

    //     Start SymptomTracking
    Route::resource('event', EventController::class);

    //    End SymptomTracking


    //    Start Onboarding
    Route::resource('onboarding', OnboardingController::class);
    //   End Onboarding


    // Start Homework
    Route::get('/homeworks/activity', [HomeworkController::class, 'activity']);
    Route::resource('homeworks', HomeworkController::class);
    Route::resource('homework-templates', HomeworkTemplateController::class);
    // End Homework


    // Start Symptom
    Route::apiResource('symptoms', SymptomController::class);
    Route::get('symptoms/common', [SymptomController::class, 'common']);

    Route::get('/user-symptoms', [UserSymptomController::class, 'index']);
    Route::post('/user-symptoms', [UserSymptomController::class, 'store']);
    // End UserSymptom


    // Start Mood
    // routes/api.php

    Route::post('/mood', [MoodController::class, 'store']);
    Route::delete('/mood/{id}', [MoodController::class, 'destroy']);
    Route::get('/mood-by-date', [MoodController::class, 'getMoodByDate']);
    Route::get('/mood-info', [MoodController::class, 'getMoodInfo']);
    Route::get('/mood-weekly-report', [MoodController::class, 'getWeeklyReport']);
    Route::get('/mood-monthly-report', [MoodController::class, 'getMonthlyReport']);

    Route::apiResource('mood-relations', MoodRelationController::class);
    Route::get('mood-relations/common', [MoodRelationController::class, 'common']);

    // End Mood

    //     Start HomeWork
    // Route::resource('homework', HomeWorkController::class);
    // Route::post('updatehomework/{id}', [HomeWorkController::class, 'update']);
    //    End HomeWork

    //    Start Weekly Word
    Route::resource('words', WeeklyWordsController::class);
    //    End Weekly Word

    Route::post('setup_intent', [StripeController::class, 'setupIntent']);
    Route::post('subscribe',  [StripeController::class, 'subscribe']);
    Route::post('payment', [StripeController::class, 'payment']);
    // Route::get('get_plans', [StripeController::class, 'get_plans']);
    // Route::get('cancel_subscription', [StripeController::class, 'cancel_subscription']);

    // Start Stripe Controller Version 2.0
    Route::get('get_subscription', [StripeControllerV2::class, 'get_subscription']);
    Route::get('get_prices', [StripeControllerV2::class, 'get_prices']);
    Route::post('initialize_subscription', [StripeControllerV2::class, 'initialize_subscription']);
    Route::get('cancel_subscription', [StripeControllerV2::class, 'cancel_subscription']);
    Route::get('applyCoupon/{couponid}', [ApiController::class, 'applyCoupon']);




    // Watson chat

    Route::prefix('chat')->group(function () {
        Route::post('/session', [ChatController::class, 'createSession']);
        Route::post('/session/{sessionId}/message', [ChatController::class, 'sendMessage']);
    });
});
