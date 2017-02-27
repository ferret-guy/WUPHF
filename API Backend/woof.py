from boto3.dynamodb.conditions import Key
from boto3.dynamodb import types
import boto3
import json


class APIDatabase:
	def __init__(self, keyfile, table=None):
		with open(keyfile, "r") as f:
			data = json.load(f)
			self.ACCESS_KEY = data['access_key_id']
			self.SECRET_KEY = data['secret_access_key']
		self.dynamodb = boto3.resource(
			'dynamodb',
			region_name="us-east-1",
			aws_access_key_id=self.ACCESS_KEY,
			aws_secret_access_key=self.SECRET_KEY
		)
		# Getters and setters because table is a reference to dynamodb.Table
		if table is not None:
			self.table = self.dynamodb.Table(table)
		else:
			self.table = None

	@property
	def table(self):
		if self.table is not None:
			return self.table.table_name
		else:
			return None

	@table.setter
	def table(self, db):
		if db is not None:
			self.table = self.dynamodb.Table(db)
		else:
			self.table = None

	@staticmethod
	def float_to_decimal(item):
		"""Method to convert all float elements in nested dict and list object to float"""
		for key in item:
			if isinstance(item[key], list):
				tmp = list()
				for i in item[key]:
					if isinstance(i, dict):
						tmp.append(APIDatabase.float_to_decimal(i))
					elif isinstance(i, float):
						tmp.append(types.Decimal(i))
				item[key] = tmp
			if isinstance(item[key], dict):
				item[key] = APIDatabase.float_to_decimal(item[key])
			if isinstance(item[key], float):
				item[key] = types.Decimal(item[key])
		return item


if __name__ == "__main__":
	import os
	os.environ['table'] = "woof"
	with open("creds", "r") as f:
		data = json.load(f)
		for key in data:
			os.environ[key] = data[key]
	table = boto3.resource(
		'dynamodb',
		region_name="us-east-1",
		aws_access_key_id=os.environ['ACCESS_KEY'],
		aws_secret_access_key=os.environ['SECRET_KEY']
	).Table(os.environ['table'])

	table.update_item(Key={"username": "ferret_guy"},
					UpdateExpression='SET snapchat = :val1',
					ExpressionAttributeValues={
						':val1': {"username": "test"}
					})

