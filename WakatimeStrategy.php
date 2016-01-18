<?php
/**
 * Wakatime strategy for Opauth
 *
 * Based on work by U-Zyn Chua (http://uzyn.com)
 *
 * More information on Opauth: http://opauth.org
 *
 * @copyright    Copyright © 2014 Timm Stokke (http://timm.stokke.me)
 * @link         http://opauth.org
 * @package      Opauth.WakatimeStrategy
 * @license      MIT License
 */

/**
 * Wakatime strategy for Opauth
 *
 * @package			Opauth.Wakatime
 */
class WakatimeStrategy extends OpauthStrategy {

	/**
	 * Compulsory config keys, listed as unassociative arrays
	 */
	public $expects = array('client_id', 'client_secret');

	/**
	 * Optional config keys, without predefining any default values.
	 */
	public $optionals = array('redirect_uri', 'scope', 'state');

	/**
	 * Optional config keys with respective default values, listed as associative arrays
	 * eg. array('scope' => 'email');
	 */
	public $defaults = array(
		'redirect_uri' => '{complete_url_to_strategy}oauth2callback'
	);

	/**
	 * Auth request
	 */
	public function request() {
		$url = 'https://api.wakatimeapp.com/oauth2/authorize';
		$params = array(
			'client_id' => $this->strategy['client_id'],
			'redirect_uri' => $this->strategy['redirect_uri'],
			'response_type' => 'code',
		);

		foreach ($this->optionals as $key) {
			if (!empty($this->strategy[$key])) $params[$key] = $this->strategy[$key];
		}

		$this->clientGet($url, $params);
	}

	/**
	 * Internal callback, after OAuth
	 */
	public function oauth2callback() {
		if (array_key_exists('code', $_GET) && !empty($_GET['code'])) {
			$code = $_GET['code'];
			$url = 'https://api.wakatimeapp.com/oauth2/token';

			$params = array(
				'code' => $code,
				'client_id' => $this->strategy['client_id'],
				'client_secret' => $this->strategy['client_secret'],
				'redirect_uri' => $this->strategy['redirect_uri'],
				'grant_type' => 'authorization_code',
			);

			if (!empty($this->strategy['state'])) $params['state'] = $this->strategy['state'];

			$response = $this->serverPost($url, $params, null, $headers);
			$results = json_decode($response,true);

			if (!empty($results) && !empty($results['access_token'])) {
				$user = $this->user($results['access_token']);

				$this->auth = array(
					'uid' => $user['user']['id'],
					'info' => array(
						'name' => $user['user']['first_name'].' '.$user['user']['last_name'],
						'image' => $user['company']['base_uri'].$user['user']['avatar_url'],
					),
					'credentials' => array(
						'token' => $results['access_token'],
						'refresh_token' =>  $results['refresh_token'],
						'expires_in' =>  $results['expires_in'],
					),
					'raw' => $user
				);

				$this->mapProfile($user, 'user.first_name', 'info.first_name'); // look into setting full name here
				$this->mapProfile($user, 'user.last_name', 'info.last_name');
				$this->mapProfile($user, 'user.email', 'info.email');
				$this->mapProfile($user, 'company.base_uri', 'info.urls.base_uri');

				$this->callback();
			}
			else {
				$error = array(
					'code' => 'access_token_error',
					'message' => 'Failed when attempting to obtain access token',
					'raw' => array(
						'response' => $response,
						'headers' => $headers
					)
				);

				$this->errorCallback($error);
			}
		}
		else {
			$error = array(
				'code' => 'oauth2callback_error',
				'raw' => $_GET
			);

			$this->errorCallback($error);
		}
	}

	/**
	 * Queries Wakatime API for user info
	 *
	 * @link         https://github.com/wakatimehq/api/blob/master/Authentication/OAuth%202.0.md#for-server-side-applications
	 * @param string $access_token
	 * @return array Parsed JSON results
	 */
	private function user($access_token) {

		$options['http']['header'] = "Content-Type: application/json";
		$options['http']['header'] .= "\r\nAccept: application/json";

		$user = $this->serverGet('https://api.wakatimeapp.com/account/who_am_i', array('access_token' => $access_token), $options, $headers);

		if (!empty($user)) {
			return $this->recursiveGetObjectVars(json_decode($user));
		}
		else {
			$error = array(
				'code' => 'userinfo_error',
				'message' => 'Failed when attempting to query Wakatime API for user information',
				'raw' => array(
					'response' => $user,
					'headers' => $headers
				)
			);

			$this->errorCallback($error);
		}
	}
}
