from twilio.rest import TwilioRestClient
from twython import Twython
# from pysnap import Snapchat
from fpdf import FPDF
import datetime
import requests
import twython
import base64
import boto3
import json
import uuid
import os


def email(data, message, woof):
	recipient = data['address']
	import smtplib
	gmail_user = os.environ['gmail_user']
	gmail_pwd = os.environ['gmail_pwd']
	FROM = "woof@wuphf.me"
	TO = [recipient]
	SUBJECT = "You got a Woof!" if woof else "Notification"
	TEXT = message

	# Prepare actual message
	message = """From: %s\nTo: %s\nSubject: %s\n\n%s
		""" % (FROM, ", ".join(TO), SUBJECT, TEXT)
	try:
		server = smtplib.SMTP("smtp.gmail.com", 587)
		server.ehlo()
		server.starttls()
		server.login(gmail_user, gmail_pwd)
		server.sendmail(FROM, TO, message)
		server.close()
	except Exception, e:
		print e
	print "email!"


def text(data, message, woof):
	TwilioRestClient(os.environ['ACCOUNT_SID'],
					os.environ['AUTH_TOKEN']).messages.create(
					to=data['number'],
					from_="+15202140308",
					body="You got a Woof!\n{}".format(message))
	print "text!"


def phone(data, message, woof):
	s3 = boto3.resource(
		's3',
		region_name="us-east-1",
		aws_access_key_id=os.environ['ACCESS_KEY'],
		aws_secret_access_key=os.environ['SECRET_KEY']
	).Bucket('twi.wuphf.me')

	audio = boto3.client(
		'polly',
		region_name="us-east-1",
		aws_access_key_id=os.environ['ACCESS_KEY'],
		aws_secret_access_key=os.environ['SECRET_KEY']
	).synthesize_speech(
		Text=message,
		VoiceId="Salli",
		OutputFormat="mp3"
	)["AudioStream"].read()

	audioid = uuid.uuid4().hex

	s3.put_object(
		Body=audio,
		ContentType="audio/mpeg",
		Key="{}.mp3".format(audioid),
		ACL="public-read",
		Expires=(datetime.datetime.now() + datetime.timedelta(hours=1))
	).wait_until_exists()
	xml = """<?xml version="1.0" encoding="UTF-8"?>
<Response>
	<Play loop="10">http://twi.wuphf.me/{}.mp3</Play>
</Response>""".format(audioid)

	s3.put_object(
		Body=xml,
		ContentType="text/xml",
		Key="{}.xml".format(audioid),
		ACL="public-read",
		Expires=(datetime.datetime.now() + datetime.timedelta(hours=1))
	).wait_until_exists()

	TwilioRestClient(os.environ['ACCOUNT_SID'],
					os.environ['AUTH_TOKEN']).calls.create(
							Method="GET",
							url="http://twi.wuphf.me/{}.xml".format(audioid),
							to=data['number'],
							from_="+15202140308")
	print "Phone!"


def ink(data, message, woof):
	pdf = FPDF()
	pdf.add_page()
	pdf.set_y(20)
	pdf.image("Woo-Logo.png", w=190)
	pdf.set_font('Arial', '', 16)
	pdf.set_y(150)
	pdf.set_left_margin(55)
	pdf.multi_cell(100, 7, message, align="C")
	if woof:
		pdf.set_font('Arial', 'B', 16)
		pdf.text(80, 140, "You got a Wuphf!")
	h = {"printerid": data["printerid"],
		"title": "Woof!",
		"ticket": "{\"version\": \"1.0\",\"print\": {}}",
		"content": base64.b64encode(pdf.output('tuto1.pdf', 'S')),
		"contentType": "application/pdf",
		"contentTransferEncoding": "base64"
		}
	r = requests.post("https://www.google.com/cloudprint/submit", data=h, headers={
		"Authorization": "Bearer {}".format(os.environ['access_token'])})
	print "print!"


def tweet(data, message, woof):
	try:
		twitter = Twython(os.environ['Twitter_APP_KEY'], os.environ['Twitter_APP_SECRET'],
						  os.environ['Twitter_access_token'], os.environ['Twitter_access_sec'])
		twitter.update_status(status="@{} {}".format(data["username"], message)[:160])
		print "Twitter!"
	except twython.exceptions.TwythonError:
		print "Tweet failed!"

# def snapchat(data, message, woof):
#	s = Snapchat()
#	s.login('username', 'password')
#	print s.get_snaps()
#	print "snapchat!"

api_methods = {
	"email": email,
	"sms": text,
	"phone": phone,
	"print": ink,
	"tweet": tweet
	# "snapchat": snapchat
}


def lambda_handler(event, context):
	user = None
	message = None
	woof = False
	if len(event) < 1:
		return {"error": "no request body!"}
	try:
		user = event['user']
		message = event['message']
		woof = event['woof']
		if not isinstance(woof, bool):
			return {"error": "woof must be true or false!"}
	except ValueError, e:
		return {"error": "{}\n{}".format(str(e), event)}

	# open dyamodb interface
	table = boto3.resource(
		'dynamodb',
		region_name="us-east-1",
		aws_access_key_id=os.environ['ACCESS_KEY'],
		aws_secret_access_key=os.environ['SECRET_KEY']
	).Table(os.environ['table'])
	data = table.get_item(Key={"username": user})

	if "Item" not in data:
		return {"error": "{} not a user in the db!".format(user)}

	if woof:
		for key in data["Item"]:
			if key in api_methods:
				api_methods[key](data["Item"][key], message, woof)
	else:
		return {"error": "not implemented!"}


if __name__ == "__main__":
	with open("creds", "r") as f:
		data = json.load(f)
		for key in data:
			os.environ[key] = data[key]
	print lambda_handler({"user": "ferret_guy",
						"message": "Mark sent you a woof!",
						"woof": True}, "a")
