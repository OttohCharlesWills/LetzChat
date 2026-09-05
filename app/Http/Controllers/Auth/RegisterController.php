<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Device;
use App\Models\UserActivity;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name'     => ['required', 'string', 'max:255'],
            'last_name'      => ['required', 'string', 'max:255'],
            'username'       => ['required', 'string', 'max:50', 'unique:users'],
            'email'          => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone'          => ['nullable', 'string', 'max:20', 'unique:users'],
            'gender'         => ['required', 'in:male,female,other'],
            'date_of_birth'  => ['required', 'date', 'before:today'],
            'password'       => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @return User
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $geo = app(\App\Services\GeolocationService::class)->lookup($request->ip());

        DB::beginTransaction();

        try {

            $user = User::create([
                'first_name'        => $request->first_name,
                'last_name'         => $request->last_name,
                'username'          => strtolower($request->username),

                'email'             => strtolower($request->email),
                'phone'             => $request->phone,

                'gender'            => $request->gender,
                'date_of_birth'     => $request->date_of_birth,

                'password'          => Hash::make($request->password),

                'bio'               => null,
                'avatar'            => null,
                'cover_photo'       => null,

                // Auto-filled from IP at signup — the user never has to type
                // this, and it's used for feed/ad region matching. They can
                // still override it later if they edit their profile location.
                'location'          => $geo['city'] && $geo['country']
                    ? "{$geo['city']}, {$geo['country']}"
                    : null,

                'profile_completed' => false,

                'is_active'         => true,
                'is_banned'         => false,
                'is_online'         => true,

                'last_seen_at'      => now(),

                'timezone' => $geo['timezone'] ?? 'Africa/Lagos',
                'language'          => 'en',
            ]);

            $device = Device::create([
                'user_id' => $user->id,

                'device_name' => $agent->device() ?: 'Unknown Device',

                'device_type' => $agent->isDesktop()
                    ? 'desktop'
                    : ($agent->isTablet()
                        ? 'tablet'
                        : ($agent->isMobile()
                            ? 'mobile'
                            : 'web')),

                'operating_system' => $agent->platform() ?? 'Unknown',
                'os_version' => $agent->version($agent->platform()) ?? null,

                'browser' => $agent->browser() ?? 'Unknown',
                'browser_version' => $agent->version($agent->browser()) ?? null,

                'device_identifier' => (string) Str::uuid(),

                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),

                'is_trusted' => true,
                'is_current' => true,

                'last_login_at' => now(),
                'last_active_at' => now(),
            ]);

            UserSession::create([
                'user_id' => $user->id,
                'device_id' => $device->id,

                'session_uuid' => (string) Str::uuid(),

                'platform' => $agent->isDesktop()
                    ? 'web'
                    : ($agent->platform() ? strtolower($agent->platform()) : 'web'),

                'started_at' => now(),
                'is_active' => true,
                'duration_seconds' => 0,

                'entry_screen' => 'register',

                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),

                'country' => $geo['country'],
                'state'   => $geo['state'],
                'city'    => $geo['city'],

                'metadata' => [
                    'source' => 'website'
                ],
            ]);

            UserActivity::create([
                'user_id' => $user->id,
                'device_id' => $device->id,

                'event_name' => 'register',
                'context' => 'authentication',

                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),

                'metadata' => [
                    'source' => 'website',
                    'geo' => $geo,
                ],
            ]);

            DB::commit();

            $this->guard()->login($user);

            return redirect($this->redirectPath());

        } catch (\Throwable $e) {

            DB::rollBack();

            \Illuminate\Support\Facades\Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'email' => $request->email,
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'error' => 'Registration failed. Please try again.',
                ]);
        }
    }
}