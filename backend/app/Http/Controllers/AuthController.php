<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        \Log::info('Registration attempt started', ['request_data' => $request->all()]);
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                \Log::warning('Registration validation failed', ['errors' => $validator->errors()]);
                // Log validation failure
                $this->logAuthActivity('register_validation_failed', [
                    'email' => $request->email,
                    'errors' => $validator->errors()
                ]);
                return response()->json($validator->errors(), 400);
            }

            \Log::info('Creating new user...');
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);
            \Log::info('User created successfully', ['user_id' => $user->id]);

            // Log successful registration
            $this->logAuthActivity('register_success', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Log registration error
            $this->logAuthActivity('register_error', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Registration failed'], 500);
        }
    }

    private function logAuthActivity($action, $data)
    {
        try {
            $loggerUrl = env('LOGGER_SERVICE_URL', 'http://localhost:3000/api/auth-logs');
            // Add a check to see if logger service is enabled
            if (env('ENABLE_LOGGER_SERVICE', false)) {
                Http::timeout(2)->post($loggerUrl, [
                    'action' => $action,
                    'auth_data' => $data,
                    'timestamp' => now()->toISOString()
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail if logger service is not available
            Log::debug('Logger service not available: ' . $e->getMessage());
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}