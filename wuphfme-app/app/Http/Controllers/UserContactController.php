<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Aws\DynamoDb\Marshaler;

class UserContactController extends Controller
{
	protected $validAPIs = [
		"phone",
		"sms",
		"email",
		"tweet",
		"snapchat",
		"print"
	];
	
	public function create(Request $request){
		if(\Auth::guest()) return response("unauthenticated", 403); //implement authentication
		if(empty($request->input("username"))) return response("badval", 403);
		$ddb = \AWS::createClient("DynamoDb");
		//try{
		$ddb->putItem(array(
			'TableName' => 'woof',
			'Item' => array(
				'username' => array('S' => $request->input("username"))
			)
		));
		$ddb->updateItem(array(
			'ConsistentRead' => true,
			'TableName' => 'woof',
			'Key' => array(
				'username' => array('S' => \Auth::user()->username)
			),
			'ExpressionAttributeNames' => [
				'#TK' => "associated"
			],
			'ExpressionAttributeValues' =>  [
				':val' => ["L"=>[['S' => $request->input("username")]]],
			],
			'UpdateExpression'=>"SET #TK = list_append(#TK, :val)"
		));
		//}catch(\Exception $ex){}
		return redirect("/manage");
	}
	
    public function set(Request $request, $user, $method, $key)
    {
		if(\Auth::guest()) return response("unauthenticated", 403); //implement authentication
		if(!in_array($method, $this->validAPIs)) return response("badapi", 403); //bad api
		if(empty($request->input("val"))) return response("deleting nyi", 403); //one day
		$ddb = \AWS::createClient("DynamoDb");
		try{
		$ddb->updateItem(array(
			'ConsistentRead' => true,
			'TableName' => 'woof',
			'Key' => array(
				'username' => array('S' => $user)
			),
			'ExpressionAttributeNames' => [
				'#TK' => $method
			],
			'ExpressionAttributeValues' =>  [
				':val' => ['M' => Array($key=>Array("S"=>" "))],
			],
			'ConditionExpression' => 'attribute_not_exists(#TK)',
			'UpdateExpression' => 'set #TK = :val'
		));
		}catch(\Exception $ex){}
		$reuslt = $ddb->updateItem(array(
			'ConsistentRead' => true,
			'TableName' => 'woof',
			'Key' => array(
				'username' => array('S' => $user)
			),
			'ExpressionAttributeNames' => [
				'#TK' => $method,
				'#SK' => $key
			],
			'ExpressionAttributeValues' =>  [
				':val' => ['S' => $request->input("val")],
			],
			'UpdateExpression' => 'set #TK.#SK = :val'
		));
        return response("set", 200);
    }
	
    public function get(Request $request, $user, $method, $key)
    {
		if(\Auth::guest()) return response("unauthenticated", 403); //implement authentication
		if(!in_array($method, $this->validAPIs)) return response("badapi", 403); //bad api
		$ddb = \AWS::createClient("DynamoDb");
		$why_would_you_design_it_this_way = new Marshaler();
		$record = $why_would_you_design_it_this_way->unmarshalItem($ddb->getItem(array(
			'ConsistentRead' => true,
			'TableName' => 'woof',
			'Key' => array(
				'username' => array('S' => $user)
			)
		))->toArray()["Item"]);
		if (empty($record[$method][$key])) {
			$result = "";
		} else {
			$result = $record[$method][$key];
		}
		return response($result, 200);
    }
}