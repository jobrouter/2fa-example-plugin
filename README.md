# JR 2FA Example Plugin
This repository includes a sample project to illustrate the usage of the JobRouter® Authentication Factor API.

It can be used as a starting point for developing a custom 2nd authentication factor for the JobRouter® 2FA workflow, and also provides a 
practical reference and examples for the API methods and functionality.

---

* [Project status](#project-status)
* [General usage](#general-usage)
    * [Naming conventions](#naming-conventions)
    * [Project structure](#project-structure)
    * [Plugin class](#plugin-class)
    * [Authenticator class](#authenticator-class)
    * [Internationalization](#internationalization-i18n)

---

## Project status

:fire: The _Authentication Factor API_ is still in a very early alpha state. Before moving into a mature beta phase,
that API is likely to change and custom plugins may stop working.\
We encourage everyone to experiment with it and give us feedback about it.

## Requirements

* Composer 2

## General usage
Clone this repository and run `composer install`, or create an empty repository and install the API stubs 
as documented in the [Authentication Factor API](https://github.com/jobrouter/authentication-factor-api) package.

Create a new directory for your custom plugin and implement the required classes according to the example project and this documentation.

The finished plugin must then be included in the `/plugins` directory of your JobRouter®.\
(E.g.: `/jobrouter/plugins/com.example.authentication`)

### Naming convention
Your plugin is identified by an *author* (e.g. your company, developer or publisher) and a *plugin name* which should be used consistently
throughout your files and code (exemplified here with the author *Example* and plugin name *Authentication*):

- Plugin root directory: `com.<author>.<plugin-name>` (`com.example.authentication`)
- Plugin class namespace: `<Author>\<PluginName>` (`Example\Authentication`)
- Authenticator class namespace: `<Author>\<PluginName>\Factor` (`Example\Authentication\Factor`)
- Prefix for language constants: `CONST_AUTHOR_PLUGIN_NAME_` (`CONST_EXAMPLE_AUTHENTICATION_`)

### Project structure
```
2fa-example-plugin
├─ com.example.authentication       -> plugin root
|  ├─ Factor
|  |  └─ ExampleAuthenticator.php   -> authenticator class
|  ├─ languages
|  |  ├─ english.php                -> English translations
|  |  |  ...                        -> additional translations
|  └─ Plugin.php                    -> plugin class
├─ vendor
|  ├─ jobrouter
|  |  └─ authentication-factor-api  -> Authentication Factor API package
|  |     |  ...
|  |  ...
├─ README.md
|  ...
```

### Plugin class
The `Plugin` class registers your custom plugin within an internal registry so it can be used in your JobRouter®.
Refer to the example `Plugin.php` file for details on its class structure and implementation.

This class must provide a public `load` method. Instantiate an `ExtensionTypeOperator` object using the `$registry` object passed in the
method call, then call `registerTranslations` on this object with the path to your plugin `/languages` directory as a parameter, and 
`registerAuthenticationFactor` with the following parameters:

- The internal identifier of your Authenticator (e.g. `example_authenticator`)
- Fully qualified class name of your Authenticator (e.g. `Example\\Authentication\\Factor\\ExampleAuthenticator`)

> :exclamation: Please note that stubs are not provided for `PluginInterface`, `ExtensionRegistry` and `ExtensionTypeOperator` as they are not part of the
> public API and may change in the future. You may have to update projects based on this version of the API to reflect these changes in 
> upcoming releases.

### Authenticator class
The `Authenticator` class contains the logic and execution of your custom authentication method. The provided `ExampleAuthenticator.php`
demonstrates this with an example of 2FA via e-mail. All methods in this example are available via the Authentication Factor API.

In this example, a random numeric PIN is generated, sent to the user via e-mail and saved in a session variable, which is then checked against 
the PIN entered by the user in the next step of the authentication process. You will also find examples of the Exception types available for 
the 2FA process and how to work with the `Runtime`, `Logger` and `User` objects provided by the API.

Refer to the documentation of the API stubs for further descriptions on the provided methods 
and those required by the `AuthenticationFactorInterface`.

### Internationalization (i18n)
You can use language constants in your classes to display strings according to the current user's language. Create a `/languages/<language>.php`
file for any language you want to support and define your constants there. Take care to follow the naming convention described above
to avoid potential conflicts.

The priority in which languages are loaded is user language -> system language -> English as fallback, in case none of the other languages are found
in `/languages`. 
