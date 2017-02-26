<?php

namespace App\Providers;

use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;

class DynamoUserProvider implements UserProvider
{
    /**
     * The active database connection.
     *
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $conn;

    /**
     * The hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    /**
     * The table containing the users.
     *
     * @var string
     */
    protected $table;

    /**
     * Create a new database user provider.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $conn
     * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
     * @param  string  $table
     * @return void
     */
    public function __construct(DynamoDbClient $conn, HasherContract $hasher, $table)
    {
        $this->conn = $conn;
        $this->table = $table;
        $this->hasher = $hasher;
		$this->marshal = new Marshaler();
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
		$record = $this->conn->getItem(array(
			'ConsistentRead' => true,
			'TableName' => $this->table,
			'Key'       => array(
				'username'   => array('S' => $identifier)
			)
		));
		if(empty($record["Item"])) return null;
        $record = $this->marshal->unmarshalItem($record->toArray()["Item"]);

        return $this->getGenericUser($record);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
		$record = $this->conn->getItem(array(
			'ConsistentRead' => true,
			'TableName' => $this->table,
			'Key'       => array(
				'username'   => array('S' => $identifier),
				'remember_token' => array('S' => $token)
			)
		));
		if(empty($record["Item"])) return null;
        $record = $this->marshal->unmarshalItem($record->toArray()["Item"]);

        return $this->getGenericUser($user);
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(UserContract $user, $token)
    {
		$this->conn->updateItem(array(
			'ConsistentRead' => true,
			'TableName' => $this->table,
			'Key'       => array(
				'username'   => array('S' => $user->getAuthIdentifier())
			),
			'ExpressionAttributeNames' => [
				'#TK' => 'remember_token'
			],
			'ExpressionAttributeValues' =>  [
				':val' => ['S' => $token],
			] ,
			'UpdateExpression' => 'set #TK = :val'
		));
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $query = Array();

        foreach ($credentials as $key => $value) {
            if (! Str::contains($key, 'password')) {
                $query[$key] = Array('S' => $value);
            }
        }
		
		if(!count($query)) return null;
		
		$record = $this->conn->getItem(array(
			'ConsistentRead' => true,
			'TableName' => $this->table,
			'Key'       => $query
		));
		if(empty($record["Item"])) return null;
        $record = $this->marshal->unmarshalItem($record->toArray()["Item"]);

        return $this->getGenericUser($record);
    }

    /**
     * Get the generic user.
     *
     * @param  mixed  $user
     * @return \App\Providers\WuphfUser|null
     */
    protected function getGenericUser($user)
    {
        if (! is_null($user)) {
            return new WuphfUser((array) $user);
        }
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        return $this->hasher->check(
            $credentials['password'], $user->getAuthPassword()
        );
    }
}
