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
		"snapchat"
	];
	
    /**
     * Set the session data
     *
     * @param  string $value
     * @return Response
     */
    public function set(Request $request, $user, $method, $key)
    {
		if(false) return response("unauthenticated", 403); //implement authentication
		if(!in_array($method, $this->validAPIs)) return response("badapi", 403); //bad api
		$ddb = \AWS::createClient("DynamoDb");
		$reuslt = $ddb->updateItem(array(
			'ConsistentRead' => true,
			'TableName' => 'woof',
			'Key'       => array(
				'username'   => array('S' => $user)
			),
			'ExpressionAttributeNames' => [
				'#TK' => $method,
				'#SK' => $key
			],
			'ExpressionAttributeValues' =>  [
				':val' => ['S' => $request->input("val", "")],
			] ,
			'UpdateExpression' => 'set #TK.#SK = :val'
		));
        return response("set", 200);
    }
	
	/**
     * Return the session data.
     *
     * @return Response
     */
    public function get(Request $request, $user, $method, $key)
    {
		if(false) return response("unauthenticated", 403); //implement authentication
		if(!in_array($method, $this->validAPIs)) return response("badapi", 403); //bad api
		$ddb = \AWS::createClient("DynamoDb");
		$why_would_you_design_it_this_way = new Marshaler();
		$record = $why_would_you_design_it_this_way->unmarshalItem($ddb->getItem(array(
			'ConsistentRead' => true,
			'TableName' => 'woof',
			'Key'       => array(
				'username'   => array('S' => $user)
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