Mailora
==============================================

Wrapper for Laravel's email functionality that adds HTTP API and front-end-dev-friendly view-creation and use.

Table of Contents:

- [Mailora](#mailora)
  * [1 - Installation and Configuration](#1---installation-and-configuration)
    + [1.1 - Installation](#11---installation)
    + [1.2 - Config for production](#12---config-for-production)
      - [1.2.1 - secrets](#121---secrets)
      - [1.2.2 - non-sensitive](#122---non-sensitive)
    + [1.3 - Config for local-development](#13---config-for-local-development)
  * [2 - API Reference](#2---api-reference)
    + [2.1 - Send email from anywhere](#21---send-email-from-anywhere)
      - [2.1.1 - Request Example](#211---request-example)
      - [2.1.2 - Request Parameters](#212---request-parameters)
      - [2.1.3 - Response Example](#213---response-example)
        * [2.1.3.1 -  `{200 OK}`](#2131------200-ok--)
        * [2.1.3.2 - `{500 Internal Server Error}`](#2132-----500-internal-server-error--)
    + [2.2 - Send email with authenticated user](#22---send-email-with-authenticated-user)
      - [2.2.1 - Request Example](#221---request-example)
      - [2.2.2 - Request Parameters](#222---request-parameters)
      - [2.2.3 - Response Example](#223---response-example)
        * [2.2.3.1 - `{200 OK}`](#2231-----200-ok--)
        * [2.2.3.2 - `{500 Internal Server Error}`](#2232-----500-internal-server-error--)
  * [3 - Miscellaneous Notes](#3---miscellaneous-notes)
    + [3.1 - About Customized Messages in Responses](#31---about-customized-messages-in-responses)


<!-- ecotrust-canada.github.io/markdown-toc -->


1 - Installation and Configuration
------------------------------

### 1.1 - Installation

Install using Composer by running 

```
$ composer require railroad/mailora
```

or add to composer.json

```
{
    "require": {
        "railroad/mailora": "dev-master"
    }
}
```

and then run `composer update railroad/mailora`.

(I'm not sure what to do regarding specifying which version to use. Maybe just use "dev-master"?)

run `php artisan vendor:publish`

This will copy view files, the configuration file, and the routes file from the package into your application. Commit these additions.

TODO: IS THIS EVERYTHING ?

### 1.2 - Config for production

#### 1.2.1 - secrets

Define these in your environmental variables as there is no handling for for them via the config file. They shouldn't committed, thus there's no consideration for that.

Provide the required values to your applications config/mail.php file. See Laravel documention for details.

Don't commit your MAIL_PASSWORD, but rather supply it as an environmental variable.

Supply as environmental variables to keep secret:

* "MAIL_PASSWORD"

Hardcode these in the config file:

Configure the 'drive' value of your applications' config/mail.php (can just supply MAIL_DRIVER environmental variable). config/mail.php is a Laravel config file.

(remember that because the config file uses Laravel's "env()" helper function, you can override a hardcoded value at any time by supplying an environmental variable. This can be useful for alternate configurations for local or staging environments.

#### 1.2.2 - non-sensitive

Provide values for the following fields. 

They are defaults when no other value is provided by the request (or any programmatic means on route to the sending—in a specialized "Mailable" class type for example). This is useful if many there are many places in your application  are sending emails destined for single email address. In such cases, you don't have to then specify the recipient-address. Just leave it blank knowing the Mailora installation is configured to send everything without a recipient-address to that address.

| key                               | requires app-specific config values | notes regarding requirement                                                       | description                                                                                                                                                                                                                       | 
|-----------------------------------|-------------------------------------|-----------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------| 
| safety-recipient                  | yes                                 | nothing will work without this                                                    | email to send to when environment is not production                                                                                                                                                                               | 
| approved-from-public-recipients   | yes\*                               | required for public-route functionality                                           | list (array) of email addresses that emails can be sent to when publicly-available route is used                                                                                                                                  | 
| auth_middleware                   | yes\*                               | required for auth-protected-route functionality                                   | names of your applications authentication middleware behind which you wish to guard the totally open *not* publicly-accessible route                                                                                              | 
| views-directory                   | no\*                                | no change required unless view files created use non-standard path                |  path of directory with your applications custom email templates. From application root (as returned by Laravel's `base_path()` helper)                                                                                           | 
| mailables-namespace               | no\*                                | no change required unless classes created use non-standard namespace              |  namespace of Mailable classes in your application.                                                                                                                                                                               | 
| name-of-production-env            | no\*                                |  no change required unless your production environment is not called "production" | if application's "environment" is anything other than this value then emails will only be sent to the address provided by the "safety-recipient" config value                                                                     | 
| public-free-for-all               |                                     |                                                                                   | `true` would allow public-route to have "send-to" address specified in request. Creates a publicly accessible free-email-sending service that's ripe for exploitation should anybody discover it. You probably shouldn't do this. | 
| admin                             |                                     |                                                                                   | email address to send errors messages to                                                                                                                                                                                          | 
| defaults.sender-address           | yes                                 | nothing will work without this                                                    | if no sender-address specified in request use this one                                                                                                                                                                            | 
| defaults.sender-name              |                                     |                                                                                   | if no sender-name *and* no sender-address specified in request use this name. Will not apply to requests where sender-address supplied.                                                                                           | 
| defaults.recipient-address        | yes                                 | nothing will work without this                                                    | if no recipient-address specified in request use this one                                                                                                                                                                         | 
| defaults.subject                  |                                     |                                                                                   | if no subject specified in request use this one. If this not configured package hard-coded default used.                                                                                                                          | 
| defaults.success-message          |                                     |                                                                                   | if no recipient-address specified in request use this one If this not configured package hard-coded default used.                                                                                                                 | 
| defaults.error-message            |                                     |                                                                                   | if no error-message specified in request use this one If this not configured package hard-coded default used.                                                                                                                     | 
| defaults.type                     |                                     |                                                                                   | if no type specified in request use this one If this not configured package hard-coded default used.                                                                                                                              | 
| defaults.users-email-set-reply-to |                                     |                                                                                   | if authentication-protected route used and no reply-to address provided in request user's email address is set as reply-to                                                                                                        | 
 
<!-- donatstudios.com/CsvToMarkdownTable
key,requires app-specific config values,notes regarding requirement,description
safety-recipient,yes,nothing will work without this,email to send to when environment is not production
approved-from-public-recipients,yes\*,required for public-route functionality,list (array) of email addresses that emails can be sent to when publicly-available route is used
auth_middleware,yes\*,required for auth-protected-route functionality,names of your applications authentication middleware behind which you wish to guard the totally open *not* publicly-accessible route
views-directory,no\*,no change required unless view files created use non-standard path, path of directory with your applications custom email templates. From application root (as returned by Laravel's `base_path()` helper)
mailables-namespace,no\*,no change required unless classes created use non-standard namespace, namespace of Mailable classes in your application.
name-of-production-env,no\*, no change required unless your production environment is not called "production",if application's "environment" is anything other than this value then emails will only be sent to the address provided by the "safety-recipient" config value
public-free-for-all,,,`true` would allow public-route to have "send-to" address specified in request. Creates a publicly accessible free-email-sending service that's ripe for exploitation should anybody discover it. You probably shouldn't do this.
admin,,,email address to send errors messages to
defaults.sender-address,yes,nothing will work without this,if no sender-address specified in request use this one
defaults.sender-name,,,if no sender-name *and* no sender-address specified in request use this name. Will not apply to requests where sender-address supplied.
defaults.recipient-address,yes,nothing will work without this,if no recipient-address specified in request use this one
defaults.subject,,,if no subject specified in request use this one. If this not configured package hard-coded default used.
defaults.success-message,,,if no recipient-address specified in request use this one If this not configured package hard-coded default used.
defaults.error-message,,,if no error-message specified in request use this one If this not configured package hard-coded default used.
defaults.type,,,if no type specified in request use this one If this not configured package hard-coded default used.
defaults.users-email-set-reply-to,,,if authentication-protected route used and no reply-to address provided in request user's email address is set as reply-to
-->

\* with caveat—see note


Note 1: If sender-name provided but not sender-address then sender-name will not be used. This is so that an unintended use of a "name" on an unrelated.

Note 3: if environment is anything other than "production", this must be set, otherwise emails will **not** be sent to any email other than that supplied by the "MAIL_SAFETY_RECIPIENT" environmental variable. If that env-var is not set then no emails will send.

Note 4: If set to boolean `true`, public endpoint can then take a "recipient-address" body param that is not in the "approved-from-public-recipients" list. 

<!-- todo: yeah? is this really? so? Maybe if not just delete this one -->
<!-- todo: yeah? is this really? so? Maybe if not just delete this one -->
<!-- todo: yeah? is this really? so? Maybe if not just delete this one -->
<!-- todo: yeah? is this really? so? Maybe if not just delete this one -->
<!-- todo: yeah? is this really? so? Maybe if not just delete this one -->
<!-- todo: yeah? is this really? so? Maybe if not just delete this one -->

Note 5: Example for "approved-from-public-recipients": `['foo@your-domain.com', 'bar@your-domain.com']`

Note 6: if "users-email-set-reply-to" is true, in requests to the route requiring authentication (where a user is available from Laravel's `auth()->user()`), if no "reply-to" is passed in the request, the user's email address will be set. Note though, that you can also specify in any request to *not* use it—thus overriding a `true` value here only when needed.

In the /config/mailora.php file installed in your application by running `composer install`, replace the empty strings with values for use in production.



<-- ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓ UPDATE according to config/mailora.php changes ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓ -->
<-- ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓ UPDATE according to config/mailora.php changes ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓ -->
<-- ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓ UPDATE according to config/mailora.php changes ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓ -->
```php
<?php

return [
    // update
    // update
    // update
    // update
    // update
    // update
    // update
    // update
    // update
    // update
    // update
    // update
];
```

[Link to config file](https://github.com/railroadmedia/mailora/blob/master/config/mailora.php)

<!-- todo: link to config file -->
<!-- todo: link to config file -->
<!-- todo: link to config file -->
<!-- todo: link to config file -->
<!-- todo: link to config file -->
<!-- todo: link to config file -->
<!-- todo: link to config file -->

(tf is "auth_middleware" you ask? See the next section)


These values in config use [Laravel's "env" function](https://laravel.com/docs/master/helpers#method-env) to use constants provided as environmental variables. If those a constant is null, then the value provided to the "env" function.


<!-- todo: ensure "sender" changed to "sender-address" as needed -->
<!-- todo: ensure "sender" changed to "sender-address" as needed -->
<!-- todo: ensure "sender" changed to "sender-address" as needed -->
<!-- todo: ensure "sender" changed to "sender-address" as needed -->
<!-- todo: ensure "sender" changed to "sender-address" as needed -->
<!-- todo: ensure "sender" changed to "sender-address" as needed -->

You can then call these configuration values using [Laravel's config helper](https://laravel.com/docs/master/helpers#method-config):

```php
$senderAddress = config('mail.defaults.sender-address');

$mail = config('mail.defaults');

$senderName = $mail['sender-name'];
```

Thus, you can provide these as environmental variables:

* MAILORA_SAFETY_RECIPIENT
* MAILORA_DEFAULT_RECIPIENT
* MAILORA_APPROVED_FROM_PUBLIC_RECIPIENTS
* MAILORA_DEFAULT_ADMIN
* MAILORA_DEFAULT_TYPE
* MAILORA_NAME_OF_PROD_ENV
* MAILORA_DEFAULT_TYPE
* MAILORA_PUBLIC_FREE_FOR_ALL

But you don't need to. It's better to just set the values for production in the config file, commit that, and then use the environmental variables for local as per below.

#### 1.2.3 - authentication middleware

Supply an array of one or more values as "auth_middleware". Use the key from the "$routeMiddleware" property of your application's App\Http\Kernel class ([configured as per Laravel functionality](https://laravel.com/docs/5.6/middleware#registering-middleware)).

For example, if your app/Http/Kernel.php has:

```
    protected $routeMiddleware = [
        'auth' => \WordpressAuthMiddleware::class,
        'auth-special' => \WordpressSpecialAuthMiddleware::class,
        'requires.edge' => \App\Http\Middleware\RequiresEdge::class,
        'requires.edge-or-pack' => \App\Http\Middleware\RequiresPackOrEdge::class,
        'auth.admin' => \WordpressAdminAuthMiddleware::class,
        'auth.executives' => \StatisticsWhiteListFilter::class,
        'auth.shippers' => \App\Http\Middleware\ShippersOrAdmin::class,
    ];
```

then in config/mailora.php you can have this:

```php
return [
    'defaults' => [
        // omitted in the name of brevity
    ],
    'auth_middleware' => ['auth', 'auth-special']
];
```

### 1.3 - Config for local-development


Provide values to .env. You can provide any you want, but the only one that you **must** set is "MAIL_SAFETY_RECIPIENT". There are cases when failure to provide a value to this variable will cause no emails to send. See the note above ("Note 3" in section about configuration variable).

It's also recommended to set "MAIL_DEFAULT_RECIPIENT" and "MAIL_FROM_ADDRESS" to avoid confusing and/or spamming your co-workers.

example:

```
MAIL_SAFETY_RECIPIENT=jonathan+recordeo_local_safety_recipient@drumeo.com
MAIL_DEFAULT_RECIPIENT=jonathan+recordeo_local_default_recipient@drumeo.com
MAIL_FROM_ADDRESS=system+from_local_dev@recordeo.com
MAIL_DEFAULT_SENDER_NAME=Recordeo
MAIL_FROM_ADDRESS_PUBLIC=system+public@recordeo.com
```

**Especially important is `MAIL_SAFETY_RECIPIENT`**. This ensures that customers (and fellow team members) are not spammed when you're developing locally. When that variable is set, and the environment is *not* production *all* emails go to that specified address. If that variable is not set, no emails will be sent (so long as environment is *not* "production").


2 - API Reference
------------------------------

There are two endpoints:

1. `POST /mail/` (Publicly accessible)
1. `POST /members/mail/` (User must be authenticated to access this endpoint)

See details below.


### 2.1 - Send email from anywhere

`POST /mail/`

Can be called from publicly-exposed. Can be called from anywhere. Handy for sending emails from support and sales pages.

Recipient cannot be specified. Will be sent to MAIL_FROM_ADDRESS unless MAIL_FROM_ADDRESS_PUBLIC provided. Though, you can set the "Sender name"<!-- change if below is implemented -->

User must be defined in config file "config/mailora.php". If user is not present there, email will not be send to intended recipient but rather a "unauthorized_recipient" email will be sent to the address provided by c.

<!-- todo: implement this -->
<!-- todo: implement this -->
<!-- todo: implement this -->
<!-- todo: implement this -->
<!-- todo: implement this -->
<!-- todo: implement this -->

#### 2.1.1 - Request Example

```javascript
let data = {
    'subject' : 'qux',
    'sender-address' : 'quux@guuz.com',
    'sender-name' : 'corge',
    'reply-to' : 'grault@garply.com',
    'type' : 'waldo',
    'error-message' : 'fred',
    'success-message' : 'plugh',
};

$.ajax({
    url: 'https://www.foo.com/mailora/mail?' ,
    type: 'get',
    dataType: 'json',
    data: data,
    success: function(response) { /* handle error */ },
    error: function(response) { /* handle error */ }
});
```

#### 2.1.2 - Request Parameters

| param type (path\|query\|body) |  key            |  required |  default                                                      |  description\|notes                               | 
|--------------------------------|-----------------|-----------|---------------------------------------------------------------|---------------------------------------------------| 
| body                           | subject         | no        | value returned by `config('mailora.defaults.subject')`        |                                                   | 
| body                           | sender-address  | no        | value returned by `config('mailora.defaults.sender-address')` |                                                   | 
| body                           | sender-name     | no        | value returned by `config('mailora.defaults.sender-name')`    | will not be used unless "sender-address" provided | 
| body                           | reply-to        | no        | `null`                                                        |                                                   | 
| body                           | type            | no        |  'general'                                                    |                                                   | 
| body                           | error-message   | no        | `null`                                                        |                                                   | 
| body                           | success-message | no        | `null`                                                        |                                                   | 

<!-- donatstudios.com/CsvToMarkdownTable
param type (path\|query\|body), key, required, default, description\|notes
body,subject,no,value returned by `config('mailora.defaults.subject')`
body,sender-address,no,value returned by `config('mailora.defaults.sender-address')`
body,sender-name,no,value returned by `config('mailora.defaults.sender-name')`,will not be used unless "sender-address" provided
body,reply-to,no,`null`
body,type,no, 'general'
body,error-message,no,`null`
body,success-message,no,`null`
-->

#### 2.1.3 - Response Example

See the note in the section below "[About Customized Messages in Responses](#about-customized-messages-in-responses)"

##### 2.1.3.1 -  `{200 OK}`

```json

{"success-message":"foo"}

```

##### 2.1.3.2 - `{500 Internal Server Error}`

```json

{"error-message":"foo"}

```



### 2.2 - Send email with authenticated user

`POST /members/mail/`


#### 2.2.1 - Request Example

```javascript
let data = {
    'recipient-address' : 'foo@bar.com',
    'subject' : 'qux',
    'sender-address' : 'quux@guuz.com',
    'sender-name' : 'corge',
    'reply-to' : 'grault@garply.com',
    'type' : 'waldo',
    'error-message' : 'fred',
    'success-message' : 'plugh',
};

$.ajax({
    url: 'https://www.foo.com/mailora/members/mail?' ,
    type: 'get',
    dataType: 'json',
    data: data,
    success: function(response) { /* handle error */ },
    error: function(response) { /* handle error */ }
});
```

#### 2.2.2 - Request Parameters

| path\|query\|body |  key                     |  required |  default                                                      |  description\|notes                                | 
|-------------------|--------------------------|-----------|---------------------------------------------------------------|----------------------------------------------------| 
| body              | recipient-address        |  no       | value returned by `config('mailora.defaults.recipient')`      |                                                    | 
| body              | subject                  |  no       | value returned by `config('mailora.defaults.subject')`        |                                                    | 
| body              | sender-address           |  no       | value returned by `config('mailora.defaults.sender-address')` |                                                    | 
| body              | sender-name              |  no       | value returned by `config('mailora.defaults.sender-name')`    |  will not be used unless "sender-address" provided | 
| body              | reply-to                 |  no       |  `null`                                                       |                                                    | 
| body              | type                     |  no       |  'general'                                                    |                                                    | 
| body              | error-message            |  no       |  `null`                                                       |                                                    | 
| body              | success-message          |  no       |  `null`                                                       |                                                    | 
| body              | users-email-set-reply-to | no        | `null`                                                        |                                                    | 

<!-- donatstudios.com/CsvToMarkdownTable
path\|query\|body, key, required, default, description\|notes
body,recipient-address, no,value returned by `config('mailora.defaults.recipient')`
body,subject, no,value returned by `config('mailora.defaults.subject')`
body,sender-address, no,value returned by `config('mailora.defaults.sender-address')`
body,sender-name, no,value returned by `config('mailora.defaults.sender-name')`, will not be used unless "sender-address" provided
body,reply-to, no, `null`
body,type, no, 'general'
body,error-message, no, `null`
body,success-message, no, `null`
body,users-email-set-reply-to,no,`null`
-->

#### 2.2.3 - Response Example

See the note in the section below "[About Customized Messages in Responses](#about-customized-messages-in-responses)"

##### 2.2.3.1 - `{200 OK}`

```json

{"success-message":"foo"}

```

##### 2.2.3.2 - `{500 Internal Server Error}`

```json

{"error-message":"foo"}

```

3 - Miscellaneous Notes
---------------------------------------------------------------------

### 3.1 - About Customized Messages in Responses

The message returned by responses—whether a 200's "success-message" or a 500's "error-message" default to a value hard-coded in this package. But, they can both be customized. There are two ways to customize these messages:

1. by supplying an installation-specific default in the config file (this will override the hard-coded default). 
2. Second, by supplying a request-specific value in the request body parameters (this will override any defaults). 

See the flowchart below ↓

```
supplied in body params? → yes → use that
↓
no
↓
supplied in config? → yeah → use that
↓
no
↓
use the one package default
```

<!-- todo: link to file with package default. Define it as a static of the class to make it easier to find at the top of the file-->
<!-- todo: link to file with package default. Define it as a static of the class to make it easier to find at the top of the file-->
<!-- todo: link to file with package default. Define it as a static of the class to make it easier to find at the top of the file-->
<!-- todo: link to file with package default. Define it as a static of the class to make it easier to find at the top of the file-->
<!-- todo: link to file with package default. Define it as a static of the class to make it easier to find at the top of the file-->
<!-- todo: link to file with package default. Define it as a static of the class to make it easier to find at the top of the file-->


Misc notes to incorporate
----------------------------------------------

If you provide a a "general.blade.php" file in the directory returned by config('mailora.views-directory')...


todo: write about config('mailora.mailables-namespace')


todo: need a way to specify laravel root path in case different than standard. ex: drumeo


