<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use App\Providers\WuphfUser;
use Aws\DynamoDb\Marshaler;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

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
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'username' => 'required|max:64',
            'password' => 'required|min:8|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
		$marshal = new Marshaler();
		$ddb = \AWS::createClient("DynamoDb");
		try{
		$ddb->putItem(array(
			'TableName' => 'woof',
			'Item' => array(
				'username'   => array('S' => $data['username']),
				'password' => array('S' => bcrypt($data['password'])),
				'remember_token' => array('S' => "none"),
				'associated' => ['L'=>[['S'=>$data['username']]]]
			),
			'ConditionExpression' => 'attribute_not_exists(username)'
		));
		}catch(\Exception $ex){return null;}
		$record = $marshal->unmarshalItem($ddb->getItem(array(
			'ConsistentRead' => true,
			'TableName' => 'woof',
			'Key' => array(
				'username'   => array('S' => $data['username'])
			)
		))->toArray()["Item"]);
		return new WuphfUser($record);
    }
	
	public function register(Request $request)
    {
        $this->validator($request->all())->validate();
		
		$inter = $this->create($request->all());
		if(empty($inter)) return redirect("/register")
			->withErrors(Array("username"=>Array("That name is taken.")));
		
        event(new Registered($user = $inter));

        $this->guard()->login($user);

        return $this->registered($request, $user)
                        ?: redirect($this->redirectPath());
    }
}
