# Unique Emails

[![CircleCI](https://circleci.com/gh/timrourke/unique-emails.svg?style=svg)](https://circleci.com/gh/timrourke/unique-emails)

This is a simple server that counts the number of unique emails provided to it, based on the rules that email labels and dots in the email's local part are not considered part of the canonical email address. The domain is unmodified.

## Running this application

1. Clone this repository
2. Bring up the application by running `docker-compose up`
3. Send a POST request to `http://localhost/api/uniqueEmails` containing a JSON body like the one below:

```json
{
	"emails": [
		"someone@coolplace.com",
		"some.one@coolplace.com",
		"some.one+alabel@coolplace.com",
		"fred@anotherplace.com"
	]
}
```

You should receive a JSON-encoded response like the one below:

```
{
	"numUniqueEmails": 2,
	"uniqueEmails": [
		"someone@coolplace.com",
		"fred@anotherplace.com"
	]
}
```
