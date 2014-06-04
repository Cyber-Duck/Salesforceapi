<?php namespace Cyberduck\Salesforceapi;

use Config;
use Log;
use Session;

class Salesforceapi {


	public static function checkuser()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, Config::get('salesforceapi::endpoint.oauth_endpoint'));
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POST, count(Config::get('salesforceapi::oauth')));
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(Config::get('salesforceapi::oauth')));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$result = json_decode(curl_exec($ch));

		if(curl_error($ch)):
   			Log::error(curl_getinfo($ch).curl_error($ch));
		else:
			if(static::curl_status_check($ch, $result)):
				$this->set_session($result);
			else:
				Log::error('UH oh');
				return FALSE;
			endif;
		endif;
		
	}

	public function execute_request($url, $http_method=FALSE, $data=FALSE) {

		$ch = curl_init();

		if($http_method == "PATCH" || $http_method == "POST"):
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $http_method);
			curl_setopt($ch, CURLOPT_POSTFIELDS, 	$data);
		endif;

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					"Authorization: OAuth " . Session::get('access_token'),
					"Content-type: application/json"));

		$result = json_decode(curl_exec($ch));

        // debug
        print_r($result);

		//Request Error Handling
		if(curl_error($ch)):
			Log::error(curl_getinfo($ch).curl_error($ch));
		else:
			if($this->curl_status_check($ch, $result)):
				return $result;
			else:
                echo "Error code: ".curl_getinfo($ch, CURLINFO_HTTP_CODE);
				return FALSE;
			endif;
		endif;
	}

	public function set_session($result) {

		if(isset($result->access_token)):
			Session::put('access_token', $result->access_token);
		endif;
	}

	public function get_record($object_name, $record_id, $field_list = FALSE) {

		$request = $this->sf['salesforce_url'].'/services/data/'.$this->sf['salesforce_apiversion'].'/sobjects/'.trim($object_name).'/'.trim($record_id);

		if($field_list):
			$request .= '?fields='.implode(",",$field_list);
		endif;

		return $this->execute_request($request);
	}

	public function get_query($query, $additional_query_flag = FALSE) {

		$request = Config::get('salesforce.endpoint.url').'/services/data/'.Config::get('salesforce.endpoint.apiversion').'/query/';

		if($query && !$additional_query_flag) {
            // for initial call, without the flag
			$request .= '?q='.urlencode(trim($query));

        } else if($query && $additional_query_flag) {
            // for additional query results, as described on http://www.salesforce.com/us/developer/docs/api_rest/Content/resources_query.htm
            $request = Config::get('salesforce.endpoint.url').$query;

        }

        return $this->execute_request($request);
        
	}

	private static function process_error($result, $code) 
	{

		if(is_array($result)):
			Log::error($code.": LEVEL1: process_error(): ".$result[0]->errorCode.' : '.$result[0]->message);
		endif;

		if(isset($result->error)):
			Log::error($code.": LEVEL2: process_error(): ".$result->error.' : '.$result->error_description);
		endif;
	}

	private static function curl_status_check($ch, $result) 
	{
		switch(curl_getinfo($ch, CURLINFO_HTTP_CODE)):

			case "404":
				Log::error("Class: Salesforce - The requested resource could not be found. Check the URI for errors, and verify that there are no sharing issues.");
				break;

			case "300":
				Log::error("Class: Salesforce - The value used for an external ID exists in more than one record. The response body contains the list of matching records.");
				break;

			case "400":
				static::process_error($result, "400");
				break;

			case "415":
				Log::error("The entity specified in the request is in a format that is not supported by specified resource for the specified method.");
				break;

			case "401":
				static::process_error($result, "401");
				break;

			case "500":
				Log::error("Class: Salesforce - An error has occurred within Force.com, so the request could not be completed.");
				break;

			case "200":
            case "201":
            case "204":
            	Log::error('Salesforce OKAY!');
				return TRUE;
				break;

		endswitch;
	}

}
