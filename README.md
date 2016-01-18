Opauth-Wakatime
=============
[Opauth][1] strategy for Wakatime authentication.

Implemented based on https://github.com/wakatimehq/api

Getting started
----------------
1. Install Opauth-Wakatime:

   Using git:
   ```bash
   cd path_to_opauth/Strategy
   git clone https://github.com/t1mmen/opauth-wakatime.git Wakatime
   ```

  Or, using [Composer](https://getcomposer.org/), just add this to your `composer.json`:

   ```bash
   {
       "require": {
           "t1mmen/opauth-wakatime": "*"
       }
   }
   ```
   Then run `composer install`.


2. Create Wakatime application at https://platform.wakatimeapp.com/oauth2_clients

3. Configure Opauth-Wakatime strategy with at least `Client ID` and `Client Secret`.

4. Direct user to `http://path_to_opauth/wakatime` to authenticate

Strategy configuration
----------------------

Required parameters:

```php
<?php
'Wakatime' => array(
	'client_id' => 'YOUR CLIENT ID',
	'client_secret' => 'YOUR CLIENT SECRET'
)
```

License
---------
Opauth-Wakatime is MIT Licensed
Copyright © 2014 Timm Stokke (http://timm.stokke.me)

[1]: https://github.com/opauth/opauth
