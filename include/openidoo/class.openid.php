<?php

/*
* OpenID Client by iDOO
* Created by www.idoo.com
*
* Thank you for your interest for our scripts.
* This class was written to integrate an OpenID simple registration on your site.
* The script requires CURL module and PHP version 4 or 5.
*
* @category	OpenID Client
* @author		Jonathan Piat
* @copyright	2007 iDOO - www.idoo.com
* @copyright	2007 iEUROP S.A.S.
* @license		http://www.gnu.org/licenses/gpl.html
* @version		1.0
*
* OpenID Client by iDOO is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* OpenID Client by iDOO is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA.
*/

class OpenID_Login
{
	var $openid_identity;
	var $openid_server;
	var $openid_return_to;
	var $openid_trust_root;
	var $openid_required_fields;
	var $openid_optional_fields;

	function OpenID_Return_To($URL_Return_To) {
		$this->openid_return_to = '';
		if (isset($URL_Return_To) && ($URL_Return_To)) {
			$URL_Return_To_tmp = $URL_Return_To;
			if (!((strpos($URL_Return_To_tmp, 'http://') !== false) or (strpos($URL_Return_To_tmp, 'https://') !== false))) {
				$URL_Return_To = 'http://'.$URL_Return_To_tmp;
			}
			$this->openid_return_to = $URL_Return_To;
		} else {
			if (isset($_SERVER['SERVER_NAME']) && isset($_SERVER['PHP_SELF'])) {
				$this->openid_return_to = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
			}
		}
	}

	function OpenID_Trust_Root($URL_Trust_Root) {
		$this->openid_trust_root = '';
		if (isset($URL_Trust_Root) && ($URL_Trust_Root)) {
			$URL_Trust_Root_tmp = $URL_Trust_Root;
			if (!((strpos($URL_Trust_Root_tmp, 'http://') !== false) or (strpos($URL_Trust_Root_tmp, 'https://') !== false))) {
				$URL_Trust_Root = 'http://'.$URL_Trust_Root_tmp;
			}
			$this->openid_trust_root = $URL_Trust_Root;
		} else {
			if (isset($_SERVER['SERVER_NAME'])) {
				$this->openid_trust_root = 'http://'.$_SERVER['SERVER_NAME'].'/';
			}
		}
	}

	function OpenID_Identity($URL_identity) {
		$openid_identity = '';
		$URL_identity_tmp = $URL_identity;
		if (!((strpos($URL_identity_tmp, 'http://') !== false) or (strpos($URL_identity_tmp, 'https://') !== false))) {
			$URL_identity = 'http://'.$URL_identity_tmp;
		}
		if (isset($URL_identity) && ($URL_identity != 'http://') && ($URL_identity != 'https://')) {
			$URL_identity_parse = parse_url($URL_identity);
			if (isset($URL_identity_parse['scheme'])) $openid_identity .= $URL_identity_parse['scheme'].'://'; else $openid_identity .= 'http://';
			if (isset($URL_identity_parse['host'])) $openid_identity .= $URL_identity_parse['host'];
			if (isset($URL_identity_parse['port'])) $openid_identity .= ':'.$URL_identity_parse['port'];
			if (isset($URL_identity_parse['path'])) $openid_identity .= $URL_identity_parse['path']; else $openid_identity .= '/';
			if (isset($URL_identity_parse['query'])) $openid_identity .= '?'.$URL_identity_parse['query'];
			$this->openid_identity = $openid_identity;
		} else {
			$this->openid_identity = '';
		}
	}
	
	function OpenID_Required_Fields($required_fields) {
		$required_fields = str_replace(' ', '', $required_fields);
		$required_fields = str_replace(';', ',', $required_fields);
		$this->openid_required_fields = $required_fields;
		return $this->openid_required_fields;
	}
	
	function OpenID_Optional_Fields($optional_fields) {
		$optional_fields = str_replace(' ', '', $optional_fields);
		$optional_fields = str_replace(';', ',', $optional_fields);
		$this->openid_optional_fields = $optional_fields;
		return $this->openid_optional_fields;	
	}
	
	function OpenID_Get_Response($response) {
		$a = array();
		$response = explode("\n", $response);
		foreach($response as $line) {
			$line = trim($line);
			if (isset($line) && ($line)) {
				if (strpos($line, ':') !== false) {
					list($key, $value) = explode(":", $line, 2);
					$a[trim($key)] = trim($value);
				}
			}
		}
	 	return $a;
	}

	function OpenID_Test_Server() {
		$curl = curl_init($this->openid_identity);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$curl_response = curl_exec($curl);
		curl_close($curl);

		preg_match_all('/<link[^>]*rel="openid.server"[^>]*href="([^"]+)"[^>]*\/?>/i', $curl_response, $curl_matches1);
		preg_match_all('/<link[^>]*href="([^"]+)"[^>]*rel="openid.server"[^>]*\/?>/i', $curl_response, $curl_matches2);
		$curl_servers = array_merge($curl_matches1[1], $curl_matches2[1]);
		preg_match_all('/<link[^>]*rel="openid.delegate"[^>]*href="([^"]+)"[^>]*\/?>/i', $curl_response, $curl_matches3);
		preg_match_all('/<link[^>]*href="([^"]+)"[^>]*rel="openid.delegate"[^>]*\/?>/i', $curl_response, $curl_matches4);
		$curl_delegates = array_merge($curl_matches3[1], $curl_matches4[1]);

		if (count($curl_delegates) == 1) {
			$this->openid_identity = $curl_delegates[0];
		}

		if (count($curl_servers) == 0) {
			return false;
		} else {
			$this->openid_server = $curl_servers[0];
			return true;
		}
	}

	function OpenID_Send_Request() {
		$openid_request = $this->openid_server;
		$openid_request .= '?openid.mode=checkid_setup';
		$openid_request .= '&openid.identity='.urlencode($this->openid_identity);
		$openid_request .= '&openid.return_to='.urlencode($this->openid_return_to);
		$openid_request .= '&openid.trust_root='.urlencode($this->openid_trust_root);
		$openid_request .= '&openid.sreg.required='.urlencode($this->openid_required_fields);
		$openid_request .= '&openid.sreg.optional='.urlencode($this->openid_optional_fields);
		header("Location: ".$openid_request);
	}

	function OpenID_Validation() {
		$openid_validation = 'openid.assoc_handle='.urlencode($_GET['openid_assoc_handle']);
		$openid_validation .= '&openid.signed='.urlencode($_GET['openid_signed']);
		$openid_validation .= '&openid.sig='.urlencode($_GET['openid_sig']);
		$openid_validation .= '&openid.identity='.urlencode($this->openid_identity);
		$openid_validation .= '&openid.mode=check_authentication';
		if (isset($_GET['openid_sreg_nickname'])) $openid_validation .= '&openid.sreg.nickname='.urlencode($_GET['openid_sreg_nickname']);
		if (isset($_GET['openid_sreg_email'])) $openid_validation .= '&openid.sreg.email='.urlencode($_GET['openid_sreg_email']);
		if (isset($_GET['openid_sreg_fullname'])) $openid_validation .= '&openid.sreg.fullname='.urlencode($_GET['openid_sreg_fullname']);
		if (isset($_GET['openid_sreg_dob'])) $openid_validation .= '&openid.sreg.dob='.urlencode($_GET['openid_sreg_dob']);
		if (isset($_GET['openid_sreg_gender'])) $openid_validation .= '&openid.sreg.gender='.urlencode($_GET['openid_sreg_gender']);
		if (isset($_GET['openid_sreg_postcode'])) $openid_validation .= '&openid.sreg.postcode='.urlencode($_GET['openid_sreg_postcode']);
		if (isset($_GET['openid_sreg_country'])) $openid_validation .= '&openid.sreg.country='.urlencode($_GET['openid_sreg_country']);
		if (isset($_GET['openid_sreg_language'])) $openid_validation .= '&openid.sreg.language='.urlencode($_GET['openid_sreg_language']);
		if (isset($_GET['openid_sreg_timezone'])) $openid_validation .= '&openid.sreg.timezone='.urlencode($_GET['openid_sreg_timezone']);
		if (isset($_GET['openid_op_endpoint'])) $openid_validation .= '&openid.op_endpoint='.urlencode($_GET['openid_op_endpoint']);
		if (isset($_GET['openid_response_nonce'])) $openid_validation .= '&openid.response_nonce='.urlencode($_GET['openid_response_nonce']);
		if (isset($_GET['openid_return_to'])) $openid_validation .= '&openid.return_to='.urlencode($_GET['openid_return_to']);

		$curl = curl_init($this->openid_server);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $openid_validation);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$curl_response = curl_exec($curl);
		curl_close($curl);

		$data = $this->OpenID_Get_Response($curl_response);
		if (isset($data['is_valid']) && ($data['is_valid'] == "true")) {
			return true;
		} else {
			return false;
		}
	}
}

?>