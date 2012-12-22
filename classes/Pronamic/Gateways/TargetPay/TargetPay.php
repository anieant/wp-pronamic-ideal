<?php

/**
 * Title: TargetPay gateway
 * Description: 
 * Copyright: Copyright (c) 2005 - 2011
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0
 */
class Pronamic_Gateways_TargetPay_TargetPay {
	/**
	 * URL for issuers in Dutch language
	 * 
	 * @var string
	 */
	const URL_ISSUERS_NL = 'https://www.targetpay.com/ideal/issuers-nl.js';

	/**
	 * URL for issuers in English language
	 * 
	 * @var string
	 */
	const URL_ISSUERS_EN = 'https://www.targetpay.com/ideal/issuers-en.js';
	
	//////////////////////////////////////////////////

	/**
	 * URL for retrieving issuers in HTL format
	 * 
	 * @var string
	 */
	const URL_ISSUERS_HTML = 'https://www.targetpay.com/ideal/getissuers.php?format=html';
	
	/**
	 * URL for retrieving issuers in XML format
	 *
	 * @var string
	 */
	const URL_ISSUERS_XML = 'https://www.targetpay.com/ideal/getissuers.php?format=xml';
	
	//////////////////////////////////////////////////

	/**
	 * URL to start an transaction
	 * 
	 * @var string
	 */
	const URL_START_TRANSACTION = 'https://www.targetpay.com/ideal/start';

	/**
	 * URL to check an transaction
	 * 
	 * @var string
	 */
	const URL_CHECK_TRANSACTION = 'https://www.targetpay.com/ideal/check';
	
	//////////////////////////////////////////////////

	/**
	 * Token used by TargetPay to separate some values
	 * 
	 * @var string
	 */
	const TOKEN = ' |';
	
	//////////////////////////////////////////////////

	/**
	 * Status indicator for 'Ok'
	 * 
	 * @var string
	 */
	const STATUS_OK = '000000';

	/**
	 * Status indicator for 'No layout code'
	 * 
	 * @var string
	 */
	const STATUS_NO_LAYOUT_CODE = 'TP0001';
	
	//////////////////////////////////////////////////

	/**
	 * Constructs and initializes an TargetPay client object
	 */
	public function __construct() {
		
	}
	
	//////////////////////////////////////////////////

	public function start_transaction( $rtlo, $bank, $description, $amount, $returnurl, $reporturl ) {
		$url = add_query_arg( array(
			'rtlo'        => $rtlo,
			'bank'        => $bank,
			'description' => $description,
			'amount'      => $amount,
			'returnurl'   => $returnurl,
			'reporturl'   => $reporturl
		), self::URL_START_TRANSACTION );

		$response = wp_remote_get( $url );

		if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			$data = wp_remote_retrieve_body( $response );
	
			$result = new stdClass();
			$result->status         = strtok( $data, self::TOKEN );
			$result->transaction_id = strtok( self::TOKEN );
			$result->url            = strtok( self::TOKEN );
	
			if ( $result->status == self::STATUS_OK ) {
				return $result;
			}
		}
	}
	
	//////////////////////////////////////////////////

	public function check_status( $rtlo, $transaction_id, $once, $test ) {
		$url = add_query_arg( array(
			'rtlo'  => $rtlo,
			'trxid' => $rtlo,
			'once'  => $once ? '1' : '0',
			'test'  => $test ? '1' : '0'
		), self::URL_CHECK_TRANSACTION );

		$response = wp_remote_get( $url );

		if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			$data = wp_remote_retrieve_body( $response );

			$postion_space = strpos( $data, ' ' );

			if ( $position_space !== false ) {
				$status      = substr( $data, 0, $postion_space );
				$description = substr( $data, $postion_space + 1 );
			}
		}
	}
	
	//////////////////////////////////////////////////
	
	public function get_issuers() {
		$issuers = false;

		$url = self::URL_ISSUERS_XML;

		$response = wp_remote_get( $url );

		if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			$data = wp_remote_retrieve_body( $response );

			$xml = simplexml_load_string( $data );
			
			if ( $xml !== false ) {
				$issuers = array();

				foreach ( $xml->issuer as $xml_issuer ) {
					$id   = (string) $xml_issuer['id'];
					$name = (string) $xml_issuer;

					$issuers[$id] = $name;
				}
			}
		}

		return $issuers;
	}
}
