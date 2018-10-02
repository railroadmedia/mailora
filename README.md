Mailora
==============================================

Wrapper for Laravel's email functionality that adds HTTP API and front-end-dev-friendly view-creation and use.

Table of Contents:

- [Mailora](#mailora)
  * [1 - Installation and Configuration](#1---installation-and-configuration)
    + [1.1 - Installation](#11---installation)
    + [1.2 - Configuration](#12---configuration)
      - [1.2.1 Laravel-native 'config/mail.php'](#121-laravel-native--config-mailphp-)
      - [1.2.2  Package provided 'config/mailora.php'](#122--package-provided--config-mailoraphp-)
      - [1.2.3 - authentication middleware](#123---authentication-middleware)
    + [1.3 - Config for local-development](#13---config-for-local-development)
  * [2 - Features](#2---features)
    + [2.1 - send email with POST requests to endpoint](#21---send-email-with-post-requests-to-endpoint)
    + [2.2 - configure default values for common operations](#22---configure-default-values-for-common-operations)
    + [2.3 - all-you-can-eat view variables](#23---all-you-can-eat-view-variables)
    + [2.2 - Easy Custom Views](#22---easy-custom-views)
      - [2.2.1 Flowchart for class and view to use:](#221-flowchart-for-class-and-view-to-use-)
  * [3 - API Reference](#3---api-reference)
    + [3.1 - Send email from anywhere](#31---send-email-from-anywhere)
      - [3.1.1 - Request Example](#311---request-example)
      - [3.1.2 - Request Parameters](#312---request-parameters)
      - [3.1.3 - Response Example](#313---response-example)
        * [3.1.3.1 -  `{200 OK}`](#3131------200-ok--)
        * [3.1.3.2 - `{500 Internal Server Error}`](#3132-----500-internal-server-error--)

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


### 1.2 - Configuration

There are two files to configure. They are both in you application's "config" directory:

1. 'mail.php'
1. 'mailora.php'

See more details about each in the two sections below.

Note that both config files can use [Laravel's "env()" helper function](https://laravel.com/docs/master/helpers#method-env). Thus, you can override a value hardcoded in a config file at any time by supplying an environment variable. This can be useful for alternate configurations for local or staging environments.

You can then call these configuration values using [Laravel's config helper](https://laravel.com/docs/master/helpers#method-config)

Example:

```php
// retrieve a value directly using dot notation
$senderAddress = config('mail.defaults.sender-address');

// or retrieve an array and access items as needed.
$mail = config('mail.defaults');
$senderName = $mail['sender-name'];
```

#### 1.2.1 - Laravel-native 'config/mail.php'

This is a Laravel-native file to provide email-sending-service details. This file configures standard Laravel functionality and you can refer to [their documentation for details](https://laravel.com/docs/master/mail).

Supply the secret for your chosen email service as an environment variable, do not commit the actual value to a config file.

You can hard-code other non-sensitive values in the config file.

#### 1.2.2 - Package provided 'config/mailora.php'

This file is copied from this package to your application's "config" directory by running `artisan vendor:publish`.

| key                               | requires app-specific config values | notes regarding requirement                                                       | description                                                                                                                                                                                                                       | 
|-----------------------------------|-------------------------------------|-----------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------| 
| safety-recipient                  | yes                                 | nothing will work without this                                                    | email to send to when environment is not production                                                                                                                                                                               | 
| approved-recipients               | yes\*                               | required for public-route functionality                                           | list (array) of email addresses that emails can be sent to when publicly-available route is used                                                                                                                                  | 
| auth_middleware                   | yes\*                               | required for auth-protected-route functionality                                   | names of your applications authentication middleware behind which you wish to guard the totally open *not* publicly-accessible route. See next section for more detailed description                                              | 
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
approved-recipients,yes\*,required for public-route functionality,list (array) of email addresses that emails can be sent to when publicly-available route is used
auth_middleware,yes\*,required for auth-protected-route functionality,names of your applications authentication middleware behind which you wish to guard the totally open *not* publicly-accessible route. See next section for more detailed description
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

[Link to config file template](https://github.com/railroadmedia/mailora/blob/master/config/mailora.php).


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

...then in config/mailora.php you can have something like this:

```php
return [
    // ...
    'auth_middleware' => ['auth', 'auth-special'],
    // ...
];
```

### 1.3 - Config for local-development

Rather than mess around with changes to the config file, because Laravel's `env()` function is used, you can just provide environment variable to override anything hard-coded in the config files. Define values in a ".env" file in your application root. 

At a bare minimum, you provide "MAIL_SAFETY_RECIPIENT" so that you're sure of where emails are being sent. Emails will only be sent to that address when the enviroment is is anything other than "production" (or the value returned by config('mailora.name-of-production-env') if it's different than "production");

example:

```
MAIL_SAFETY_RECIPIENT=jonathan+mailora_dev_SAFETY_RECIPIENT@drumeo.com
```

Or supply many values:

```
MAILORA_SAFETY_RECIPIENT=joe+foo@foo.com
MAILORA_APPROVED_FROM_PUBLIC_RECIPIENTS=['joe+test89347832@foo.com']
MAILORA_NAME_OF_PROD_ENV='staging'
MAILORA_PUBLIC_FREE_FOR_ALL=true
MAILORA_DEFAULT_ADMIN=joe+bar@foo.com
MAIL_FROM_ADDRESS=joe+baz@foo.com
MAIL_FROM_NAME="Joe Black"
MAILORA_DEFAULT_RECIPIENT=joe+qux@foo.com
MAILORA_DEFAULT_TYPE='foo'
```

2 - Features
---------------------------------------------------------------------

### 2.1 - send email with POST requests to endpoint

To send an email, simply send a POST request as described in these docs. You'll receive a simple "sent" boolean value in response.


### 2.2 - configure default values for common operations

Always send to the same email address. Set that as the default in the configuration, and then on requests to send to that address, don't pass a "recipient-address" value. The default will be used.

In fact, **the endpoint has no required parameters**—you can place all required information in the configuration and only provide unique info when required.

### 2.3 - all-you-can-eat view variables

Any parameter you include in a request is available for use in views. Pass `'foo' : 'bar'`? In the view, `{{ $input['foo'] }}` will print `bar`. It's just *that* easy! 


### 2.2 - Easy Custom Views

If no 'type' value is provided in a request, the Mailora's General class and general.blade.php view will be used. If a type value is passed, Mailora will look for a class matching the CapitalizedCamelCase version of that value\*. Mailora will look for this class in the namespace returned by `config('mailora.mailables-namespace)`\*\*. If a class exists there, it will be used. If no class is used, Mailora's `General` class will be used. This class is simply uses whatever view supplied, and passes a `$input` parameter along to the view so that all values are retrieved from that.

This enables the following.

Regardless of what class is used—that is to say even if no `Mailable` class was found matching the ConvertedCapitalizedCamelCase 'type' value passed, Mailora will then look for a view file matching that type value. It looks for files in the directory described by the string returned by `config('mailora.views-directory')`\*\*. If one if found, that is used. If not, mailora's general.php is used\*\*\*.

**The upshot of all this is that new email-templates can be created with no back-end modifications required. *Simply create a view file, and supply and retrieve values from the `$input` parameter.***

\* the value `foo-bar-baz` would use the class `FooBarBaz` and the view file "foo-bar-baz.blade.php".

\*\* this is set—and can therefore be changed—in the mailora.php config file

\*\*\* Note that this file exists in "/your-application/vendor/railroad/mailora/resources/views/"

#### 2.2.1 - Flowchart for class and view to use

```
is type defined in request? → no → use 'general' view and 'General' Mailable class
↓
yes
↓
is type defined in request as 'general'? → yes → use 'general' view and 'General' Mailable class
↓
no
↓
is there a Mailable class that matches a CapitalizedCamelCase version of the 'type' value supplied? → yes → use that
↓
no
↓
use 'General'
↓
is there a view file matching the 'type' value passed? → no → use mailora's general.blade.php
↓
yes
↓
use that
```


3 - API Reference
------------------------------

There are two endpoints:

1. `POST /mail/` (Publicly accessible)
1. `POST /members/mail/` (User must be authenticated to access this endpoint)

See details below.

In addition to the parameters listed below, any other parameter can be passed, and it will be available in the view!

For example, if you have a `'foo' : 'bar'` item in data for a request, in the view, `{{ $input['foo'] }}` will print "bar".

Another example: `'foo' : 1` in the request will allow `{{ $input['foo'] ? 'true' : 'false' }}` in the view.


### 3.1 - Send email from anywhere

`POST /mail/`

Can be called from anywhere. Handy for sending emails from publicly-accessible support and sales pages.

Recipient cannot be specified. Will be sent to MAIL_FROM_ADDRESS unless MAIL_FROM_ADDRESS_PUBLIC provided. Though, you can set the "Sender name"<!-- change if below is implemented -->

User must be defined in config file "config/mailora.php". If user is not present there, email will not be send to intended recipient.
 
<!-- todo ↓ ↓ ↓ ? -->
<!-- but rather a "unauthorized_recipient" email will be sent to the address provided by config -->

#### 3.1.1 - Request Example

```javascript
let data = {
    'type' : 'foo',
    'sender-address' : 'bar@some-domain.com',
    'sender-name' : 'Baz Qux',
    'recipient-address' : 'quux@other-domain.com',
    'recipient-name' : 'Corge Uier',
    'subject' : 'Grault garply waldo',
    'reply-to' : 'bar@some-domain.com',
    'users-email-set-reply-to' : '',
    'message' : 'Fred plugh thud, mos henk. Def.',
};

$.ajax({
    url: 'https://www.foo.com/mailora/send' ,
    type: 'get',
    dataType: 'json',
    data: data,
    success: function(response) { /* handle error */ },
    error: function(response) { /* handle error */ }
});
```

#### 3.1.2 - Request Parameters

Provide all parameters in the request body.

**Note that no fields are *required*!!**

| key                      | required | default can be defined in `config('mailora.default')` | default hardcoded in package so needn't be provided by config | no default | description\|notes                                                                                                                   | 
|--------------------------|----------|-------------------------------------------------------|---------------------------------------------------------------|------------|--------------------------------------------------------------------------------------------------------------------------------------| 
| type                     | no       | yes                                                   | `'general'`                                                   |            |                                                                                                                                      | 
| sender-address           | no       | yes                                                   |                                                               |            | If not provided from request or config email will not be sent.                                                                       | 
| sender-name              | no       | yes                                                   |                                                               |            | See "Note 1" below                                                                                                                   | 
| recipient-address        | no       | yes                                                   |                                                               |            | If not provided from request or config email will not be sent.                                                                       | 
| recipient-name           | no       | yes                                                   |                                                               |            | See "Note 1" below                                                                                                                   | 
| subject                  | no       | yes                                                   | `'General Inquiry - Subject not specified'`                   |            |                                                                                                                                      | 
| reply-to                 | no       |                                                       |                                                               | yes        |                                                                                                                                      | 
| users-email-set-reply-to | no       | yes                                                   | `false`                                                       |            | If no reply-to param supplied in request but a logged in user is available the 'reply-to' will be set with that user's email address | 
| message                  | no       | yes                                                   | `''` (empty string)                                           |            |                                                                                                                                      | 

<!-- donatstudios.com/CsvToMarkdownTable
key,required,default can be defined in `config('mailora.default')`,default hardcoded in package so needn't be provided by config,no default,description\|notes
type,no,yes,`'general'`,,
sender-address,no,yes,,,If not provided from request or config email will not be sent.
sender-name,no,yes,,,See "Note 1" below
recipient-address,no,yes,,,If not provided from request or config email will not be sent.
recipient-name,no,yes,,,See "Note 1" below
subject,no,yes,`'General Inquiry - Subject not specified'`,,
reply-to,no,,,yes,
users-email-set-reply-to,no,yes,`false`,,If no reply-to param supplied in request but a logged in user is available the 'reply-to' will be set with that user's email address
message,no,yes,`''` (empty string),,
-->


Note 1: A provided name is not used unless address also provided from same source. For example: Say sender-address and sender-name are both set in the configuration file. If a request doesn't not specify request-address, then the addresss and name from the config will be used. However, if the request supplies an address bu no name, then that address (from the request) will be used, but not the name from config. The only time the name in the config is used, is when the address in the config is used. The only time a name is used when an address is provided in the request, is if a name is also provided in that request.


#### 3.1.3 - Response Example

##### 3.1.3.1 -  `{200 OK}`

```json

{"sent":1}

```

##### 3.1.3.2 - `{500 Internal Server Error}`

```json

{"sent":0}

```


-----------------------------------------------------------------------

<div style='text-align:center'>

*fin*
    
<span style='font-size:0.333333333333333333333333333333333em; color:lightgrey'>glhf</span>

</div>
