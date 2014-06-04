<?php

return array(

	'oauth' => array(
		'username' 			=> '',
		'password' 			=> '', // Password and security token
		'client_id' 		=> '', // Consumer key
		'client_secret' 	=> '', // Cosumer Secret
		'redirect_uri'		=> 'http://www.google.com',
		'grant_type'		=> 'password',
		),

	'endpoint' => array(
		'url' 			 	=> 'https://cs17.salesforce.com',
		'apiversion' 	 	=> 'v24.0',
		//'oauth_endpoint' 	=> 'https://login.salesforce.com/services/oauth2/token',
		'oauth_endpoint' 	=> 'https://test.salesforce.com/services/oauth2/token',
		),

);