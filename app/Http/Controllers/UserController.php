<?php

namespace App\Http\Controllers;

use App\Models\Chain;
use App\User;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;
use Exception;
use App\Http\Requests\CreateUserRequest;

class UserController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('guest');
    }


    public function signup(CreateUserRequest $request)
    {
        $data = $request->only('name', 'email', 'password', 'phone');
        $user = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: "",
            'password' => bcrypt($data['password']),
        ]);
        if ($user->save()) {
            if ($token = JWTAuth::attempt(["email" => $data['email'], 'password' => $data['password']])
            ) {
                $chain = app('App\Http\Controllers\ChainController')->firstChain();
                return response()->json(['token' => $token, 'user' => $user, 'chain' => $chain, 'status' => "OK"], 201);
            } else {
                return response()->json(["signin_error" => "authentication failed"], 400);
            }
        } else {
            return response()->json(["error" => "saving error"], 400);
        }
    }

    public function signin(Request $request)
    {
        $credentials = $request->only('login');
        if (strpos($request->input('login'), '@') === false) {
            $where = ['phone' => $request->input('login')];
        } else {
            $where = ['email' => $request->input('login')];
        }
        if (count($where) > 0) {
            $user = User::with('chains')->select("id")->where($where)->first();
            if ($user) {
                foreach ($user->chains as $key=>$value)  {
                    $chain = new \stdClass();
                    $chain->id = $user->chains[$key]['id'];
                    $chain->title = $user->chains[$key]['title'];
                    $user->chains[$key] = $chain;
                }
                return response()->json(["data" => ["chains" => $user->chains], "status" => "OK"], 200);
            } else {
                return response()->json(["data" => ["chains" => []], "status" => "USER NOT FOUND"], 404);
            }
        }
        return response()->json(['error' => 'Phone or Email is empty'], 400);
    }

    public function login(Request $request)
    {
        $chainId = (integer)$request->route('chain');
        if (empty($chainId)) {
            return response()->json(['error' => 'The id of chain into route is required'], 400);
        }
        $credentials = $request->only('password');
        if (!empty($credentials['password']) && (!empty($request->input('login')))) {
            if (strpos($request->input('login'), '@') === false) {
               $credentials['phone']=$request->input('login');
            } else {
                $credentials['email']=$request->input('login');
            }
            try {
                if (!$token = JWTAuth::attempt($credentials)) {
                    return response()->json(['error' => 'Invalid Credentials!'], 401);
                }
            } catch (JWTException $e) {
                return response()->json(['error' => 'Exception!'], 401);
            }
            $havSalon = app('App\Http\Controllers\SalonController')->haveAnySalon($chainId);
            $response = ['token' => $token, "user" => Auth::User()];
            if ($havSalon === 0) {
                $response['redirect_to_create_salon'] = 1;
            } else {
                $response['redirect_to_create_salon'] = 0;
            }
            $ownChain = Chain::where(['user_id' => Auth::id()])->select("id")->where(['id' => $chainId])->first();
            if (!$ownChain || $ownChain->id !== $chainId) {
                return response()->json(["error" => "Incorrect the ID of chain or permission denied!"], 400);
            }
            $response["chain"] = $ownChain;
            return response()->json($response, 200);
        }
        return response()->json(['error' => 'One and may be all fields: login, password, are empty.'], 400);
    }

    /**
     * Mask
     *
     * @param $str
     * @param $first
     * @param $last
     * @return string
     */
    function mask($str, $first, $last)
    {
        $len = strlen($str);
        $toShow = $first + $last;
        return substr($str, 0, $len <= $toShow ? 0 : $first) . str_repeat("*", $len - ($len <= $toShow ? 0 : $toShow)) . substr($str, $len - $last, $len <= $toShow ? 0 : $last);
    }

    /**
     * Partially hide email
     *
     * @param $email
     * @return string
     */
    function hide_email($email)
    {
        $mail_parts = explode("@", $email);
        $domain_parts = explode('.', $mail_parts[1]);

        $mail_parts[0] = $this->mask($mail_parts[0], 2, 1); // show first 2 letters and last 1 letter
        $domain_parts[0] = $this->mask($domain_parts[0], 2, 1); // same here
        $mail_parts[1] = implode('.', $domain_parts);

        return implode("@", $mail_parts);
    }

    /**
     * Partially hide phone
     *
     * @param $phone
     * @return string
     */
    function hide_phone($phone)
    {
        return substr($phone, 0, -6) . "******";
    }

    /**
     * Get user login info
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginInfo(Request $request)
    {
        $rules = ['login' => 'required|max:255'];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'errors' => $validation->messages()], 400);
        } else {
            if (strpos($request->input('login'), '@') === false) {
                $user = User::where('phone', $request->input('login'))->first();
            } else {
                $user = User::where('email', $request->input('login'))->first();
            }
            if (!$user) {
                return response()->json(['success' => false, 'errors' => ['user' => 'User not found']], 400);
            } else {
                $userInfo = [
                    'email' => $this->hide_email($user->email),
                    'phone' => $this->hide_phone($user->phone)
                ];
                return response()->json(['success' => true, 'errors' => [], 'data' => $userInfo], 400);
            }
        }
    }

    /**
     * Forgot password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $rules = [];
        if ($request->has('email')) {
            $rules['email'] = 'required|max:255|exists:users,email';
        } else {
            $rules['phone'] = 'required|max:255|exists:users,phone';
        }
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'errors' => $validation->messages()], 400);
        } else {
            if ($request->has('phone')) {
                $user = User::where('phone', $request->input('phone'))->first();
                if ($user) {
                    if ($user->send_code_block_count >= 3 && Carbon::parse($user->send_code_block_date)->diffInMinutes(Carbon::now()) < 60) {
                        return response()->json(['success' => false, 'errors' => ['user' => 'user_blocked']], 400);
                    }
                    $token = str_random(6);
                    $user->reset_password_token = $token;
                    $user->send_code_block_count = $user->send_code_block_count + 1;
                    $user->send_code_block_date = Carbon::now()->format('Y-m-d H:i:s');
                    $user->save();
                }
                $client = new Client();
                $client->request('GET', "http://smsc.ru/sys/send.php?login=plyyyy&psw=4b60008e342a5e3799190646c2a81185&phones=" . $request->input('phone') . "&mes=" . $token . "&sender=aVisits");
            } else {
                $token = str_random(32);
                $user = User::where('email', $request->input('email'))->first();
                if ($user) {
                    $user->reset_password_token = $token;
                    $user->save();
                }
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From: aVisits <noreply@avisits.com>' . "\r\n";
                mail($request->input('email'), 'Reset Password', '<a href="http://avisits.com/#/auth/reset-password?token=' . $token . '">Reset Password</a>', $headers);
            }
            return response()->json(['success' => true, 'errors' => []], 200);
        }
    }

    /**
     * Reset password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $rules = [
            "token" => 'required|exists:users,reset_password_token',
            "password" => "required|min:6|max:12",
            "confirm_password" => "same:password|required|min:6|max:12",
        ];
        if ($request->has('phone')) {
            $rules['phone'] = 'required|exists:users,phone';
            $user = User::where('phone', $request->input('phone'))->first();
        }
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            if ($request->has('phone')) {
                $user = User::where('phone', $request->input('phone'))->first();
                if ($user) {
                    if ($user->wrong_code_block_count >= 3 && Carbon::parse($user->wrong_code_block_date)->diffInMinutes(Carbon::now()) < 30) {
                        return response()->json(['success' => false, 'errors' => ['user' => 'user_blocked']], 400);
                    }
                    if (in_array('token', $validation->errors()->keys())) {
                        $user->wrong_code_block_count = $user->wrong_code_block_count + 1;
                        $user->wrong_code_block_date = Carbon::now()->format('Y-m-d H:i:s');
                        $user->save();
                    }
                }
            }
            return response()->json(['success' => false, 'errors' => $validation->messages()], 400);
        } else {
            $user = User::where('reset_password_token', $request->input('token'))->first();
            if ($user) {
                $user->password = bcrypt($request->input('password'));
                $user->reset_password_token = '';
                $user->wrong_code_block_count = 0;
                $user->wrong_code_block_date = Carbon::now();
                $user->send_code_block_count = 0;
                $user->send_code_block_date = Carbon::now();
                $user->save();
            }
            return response()->json(['success' => true], 200);
        }
    }

    /**
     * Generate password
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public
    function generatePassword()
    {
        return response()->json(['success' => true, 'password' => str_random(8)], 200);
    }

    public
    function users()
    {
        return response()->json(User::all(), 200);
    }

}
