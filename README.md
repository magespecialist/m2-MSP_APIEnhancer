# MSP APIEnhancer

This module replaces **MSP_APIBoost**.

### Install procedure

```
composer require msp/apienhancer
php bin/magento setup:upgrade
```

**NOTE**: If you are using Varnish see below

### Features

- Internal cache support
- Varnish external support (see below)
- Products and categories automatic invalidation
- Support for group based catalog rules

### Magento2 REST-API features enhancements:

- FIX: Catalog rules are not applied in REST-API

**Varnish users should read below**

## Why you may need it?

While using Magento 2 REST API for decoupled frontend (e.g.: a ReactJS or AngularJS frontends), you need to access catalog, search and products.

Every time you perform a REST API call, no matter if you have a FPC configured or not, Magento will **compute your request as it was the first one**.

With this simple module you will be able to cache **API REST requests** and get up to a 50x performance boost.

## Important: For Varnish users

In order to correctly handle cache invalidation and contents variation you should apply small variations to your varnish VCL file.

Add the following code to your Varnish configuration file at the beginning of **vcl_hash** section:

```hash_data(regsub(std.tolower(req.http.Authorization), "^bearer\s\x22(\w+?):\w+?\x22", "\1"));```

Implementation example:

```
import std
...
sub vcl_hash {
    hash_data(regsub(std.tolower(req.http.Authorization), "^bearer\s\x22(\w+?):\w+?\x22", "\1"));
    ...
}
...
