<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Aws\DynamoDb\Marshaler;

class WuphfController extends Controller
{
	public function wuphf(Request $request)
    {
		$this->validate($request, [
			'recipient' => 'required',
			'message' => 'required',
		]);
		
		if(\Auth::guest()) return response("unauthenticated", 403);
		if(!in_array($request->input("recipient"), \Auth::user()->associated)) return redirect("/")
			->withErrors(Array("recipient"=>Array("You are not authorized to Wuphf at that person.")));
		
		$data_string = json_encode(Array(
			"user" => $request->input("recipient"),
			"message" => $request->input("message"),
			"woof" => true
		));
		
		$ch = curl_init('https://gv7s8ts8a7.execute-api.us-east-1.amazonaws.com/prod/internal-api-access/notify');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			"Connection: keep-alive"
		));
																															
		$result = curl_exec($ch);
		$responseInfo = curl_getinfo($ch);
		
		error_log(var_export($result, true));
		error_log(var_export($responseInfo, true));
		error_log($data_string);
		
		return redirect("/")
			->withErrors(Array("success"=>Array("Your Wuphf was sent!")));
    }
}