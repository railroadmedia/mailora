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

(I'm not sure what to do regarding specifying which version to use. Maybe just use "dev-master"?)

run `php artisan vendor:publish`

This will copy view files, the configuration file, and the routes file from the package into your application. Commit these additions.

<!-- todo: is that everything? (views files, config file, route file) -->
<!-- todo: is that everything? (views files, config file, route file) -->
<!-- todo: is that everything? (views files, config file, route file) -->
<!-- todo: is that everything? (views files, config file, route file) -->
<!-- todo: is that everything? (views files, config file, route file) -->
<!-- todo: is that everything? (views files, config file, route file) -->


### 1.2 - Config for production

#### 1.2.1 - secrets

Define these in your environmental variables as there is no handling for for them via the config file. They shouldn't committed, thus there's no consideration for that.

Provide the required values to your applications config/mail.php file. See Laravel documention for details.

Don't commit your MAIL_PASSWORD, but rather supply it as an environmental variable.

Supply as environmental variables to keep secret:

* "MAIL_PASSWORD"

Hardcode these in the config file:

* MAIL_DRIVER
* MAIL_USERNAME
* MAIL_SAFETY_RECIPIENT
* MAIL_DEFAULT_RECIPIENT


(remember that because the config file uses Laravel's "env()" helper function, you can override a hardcoded value at any time by supplying an environmental variable. This can be useful for alternate configurations for local or staging environments.

#### 1.2.2 - non-sensitive

Provide values for the following fields. 

They are defaults when no other value is provided by the request (or any programmatic means on route to the sending—in a specialized "Mailable" class type for example). This is useful if many there are many places in your application  are sending emails destined for single email address. In such cases, you don't have to then specify the recipient-address. Just leave it blank knowing the Mailora installation is configured to send everything without a recipient-address to that address.

| field                           | example                                | notes                | 
|---------------------------------|----------------------------------------|----------------------| 
| recipient-address               | "support@your-domain.com"              |                      | 
| recipient-address-public        | "support+pub@your-domain.com"          |                      | 
| admin                           | "dev+email-system-msg@your-domain.com" |                      | 
| subject                         | "General Inquiry"                      |                      | 
| sender-address                  | "system@your-domain.com"               |                      | 
| sender-name                     | "Your Domain"                          | (See "Note 1" below) | 
| sender-public                   | "system+pub@your-domain.com"           | (See "Note 2" below) | 
| success-message                 | "Email friggen sent!"                  |                      | 
| error-message                   | "Oh noes!"                             |                      | 
| production                      | "staging"                              | (See "Note 3" below) | 
| public-free-for-all             | `false`                                | (See "Note 4" below) | 
| approved-from-public-recipients | (see example in "Note 5" below)        |                      | 

<!-- donatstudios.com/CsvToMarkdownTable
field,example,notes
recipient-address,"support@your-domain.com"
recipient-address-public,"support+pub@your-domain.com"
admin,"dev+email-system-msg@your-domain.com"
subject,"General Inquiry"
sender-address,"system@your-domain.com"
sender-name,"Your Domain",(See "Note 1" below)
sender-public,"system+pub@your-domain.com",(See "Note 2" below)
success-message,"Email friggen sent!", 
error-message,"Oh noes!",
production,"staging",(See "Note 3" below)
public-free-for-all,`false`,(See "Note 4" below)
approved-from-public-recipients,(see example in "Note 5" below)
-->

Note 1: If sender-name provided but not sender-address then sender-name will not be used. This is so that an unintended use of a "name" on an unrelated.

Note 2: This option exists to offer the option to provide the option to easily inform the user that email was sent from publicly-accessible endpoint. If this is not provided it will default to the "sender-address". It is not very important but it may be handy in some cases.

Note 3: if environment is anything other than "production", this must be set, otherwise emails will **not** be sent to any email other than that supplied by the "MAIL_SAFETY_RECIPIENT" environmental variable. If that env-var is not set then no emails will send.

Note 4: If set to boolean `true`, public endpoint can then take a "recipient-address" body param that is not in the "approved-from-public-recipients" list. 

Note 5: Example for "approved-from-public-recipients": `['foo@your-domain.com', 'bar@your-domain.com']`

<!-- todo: yeah? is this really? so? Maybe if not just delete this one -->
<!-- todo: yeah? is this really? so? Maybe if not just delete this one -->
<!-- todo: yeah? is this really? so? Maybe if not just delete this one -->
<!-- todo: yeah? is this really? so? Maybe if not just delete this one -->
<!-- todo: yeah? is this really? so? Maybe if not just delete this one -->
<!-- todo: yeah? is this really? so? Maybe if not just delete this one -->

In the /config/mailora.php file installed in your application by running `composer install`, replace the empty strings with values for use in production.

```php
<?php

return [
    // 0. REQUIRED For authentication-protected route - See documentation for details
    'auth_middleware' => [],

    // 1. Some required, some optional...
    'defaults' => [

        // 1.0 REQUIRED (from either here, or provided by environmental variable)
        'safety-recipient' =>   env('MAIL_SAFETY_RECIPIENT',    ''), // REQUIRED!
        'sender-address' =>     env('MAIL_FROM_ADDRESS',        ''), // REQUIRED!
        'sender-name' =>        env('MAIL_FROM_NAME',           ''), // REQUIRED!
        'recipient-address' =>  env('MAIL_DEFAULT_RECIPIENT',   ''), // REQUIRED!

        // 1.1 REQUIRED... To make "public" route work
        'approved-from-public-recipients' => env( 'MAIL_APPROVED_FROM_PUBLIC_RECIPIENTS', []),

        // 1.2 Optional - receive emails of errors
        'admin' => env('MAIL_DEFAULT_ADMIN', null),

        // 1.3 Optional - application-specific defaults for text shown to users
        'subject' => env('MAIL_DEFAULT_TYPE', null),
        'success-message' => null,
        'error-message' => null,

        // 1.4 Optional - Advanced, see documentation for details
        'production' => env( 'MAIL_NAME_TO_TREAT_LIKE_PROD', 'production'),
        'public-free-for-all' => env( 'MAIL_PUBLIC_FREE_FOR_ALL', false),

        // ----------------------------------------- leave out for now
        // ----------------------------------------- leave out for now
        // not necessary and unnecessarily complex - leave out for now
        // ----------------------------------------- leave out for now
        // ----------------------------------------- leave out for now
        // 1.5 add "+from-public" tag to end of default FROM and TWO email.
        // 'recipient-address-public' => env('MAIL_DEFAULT_RECIPIENT_PUBLIC', null),
        // 'sender-public' => env('MAIL_FROM_ADDRESS_PUBLIC', null),
    ],
];
```

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

* MAIL_SAFETY_RECIPIENT
* MAIL_DEFAULT_RECIPIENT
* MAIL_DEFAULT_RECIPIENT_PUBLIC
* MAIL_DEFAULT_TYPE
* MAIL_FROM_ADDRESS
* MAIL_DEFAULT_SENDER_NAME
* MAIL_FROM_ADDRESS_PUBLIC

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

| path\|query\|body |  key              |  required |  default                                                      |  description\|notes                                | 
|-------------------|-------------------|-----------|---------------------------------------------------------------|----------------------------------------------------| 
| body              | recipient-address |  no       | value returned by `config('mailora.defaults.recipient')`      |                                                    | 
| body              | subject           |  no       | value returned by `config('mailora.defaults.subject')`        |                                                    | 
| body              | sender-address    |  no       | value returned by `config('mailora.defaults.sender-address')` |                                                    | 
| body              | sender-name       |  no       | value returned by `config('mailora.defaults.sender-name')`    |  will not be used unless "sender-address" provided | 
| body              | reply-to          |  no       |  `null`                                                       |                                                    | 
| body              | type              |  no       |  'general'                                                    |                                                    | 
| body              | error-message     |  no       |  `null`                                                       |                                                    | 
| body              | success-message   |  no       |  `null`                                                       |                                                    | 

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
