<?php

use Illuminate\Http\Request;

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

function getIp(){
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
        if (array_key_exists($key, $_SERVER) === true){
            foreach (explode(',', $_SERVER[$key]) as $ip){
                $ip = trim($ip); // just to be safe
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                    return $ip;
                }
            }
        }
    }
}

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/get-total', function (Request $request) {
    return response()->json(['total' => \App\Models\Message::count()]);
});

Route::get('/get-all-message', function (Request $request) {
    return response()->json(['message' => \App\Models\Message::all()]);
});

Route::post('/message', function (Request $request) {
	$name = $request->get('name');
	$message = $request->get('message');
	$ip = request()->ip();
	$data = ['name' => $name, 'message' => $message, 'ip' => $ip];
	$result = \App\Models\Message::create($data);
    return response()->json(['result' => $result, 'total' => \App\Models\Message::count()]);
});
