<?php

/*
Plugin Name: Propay Application
Description: Creates Databases and adds API calls to propay database
Version: 0.5.0
Author: Ian Foulk
*/

include 'classes/class_Propay_Protectpay_API.php';
include 'classes/class_Propay_Protectpay_DB.php';
include 'email/create.php';

function create_dbs() {
	$propay_db = new ProPay_ProtectPay_DB();
	$propay_db->create_tables();
}

register_activation_hook( __FILE__, 'create_dbs' );

function Tsys_create_merchant($input_data) {
	$propay_api = new ProPay_ProtectPay_API();
	$propay_db = new ProPay_ProtectPay_DB();
	$results = array(
		'is_valid' => true,
		'error_code' => '',
		'merchant' => ''
		);
	
	$result_api_pro = $propay_api->create_Propay_account($input_data);
	if ( "00" != $result_api_pro->Status ) {
		error_log(print_r($result_api_pro,true));
		$results['is_valid'] = false;
		$results['error_code'] = $result_api_pro->Status;
		return $results;
	}
		
	$result_api_protect = $propay_api->create_merchant_id($result_api_pro->AccountNumber);
	if ( "SUCCESS" != $result_api_protect->RequestResult->ResultValue ) {
		error_log(print_r($result_api_protect, true));
		$results['is_valid'] = false;
		$results['error_code'] = $result_api_protect->RequestResult->ResultCode;
		return $results;
	}
	
	$propay_db->insert_new_merchant(array(
		'email' => $input_data['email'],
		'propayid' => $result_api_pro->AccountNumber,
		'merchantid' => $result_api_protect->ProfileId
		));
		
	$results['merchant'] = $result_api_protect;
	
	return $results;
}

function Tsys_find_create_payer($input_data) {
	$propay_api = new ProPay_ProtectPay_API();
	$propay_db = new ProPay_ProtectPay_DB();
	$results = array(
		'is_valid' => true,
		'error_message' => '',
		'payerid' => ''
		);
	
	$search = array(
		'email' => $input_data['email'],
		'merchantid' => $input_data['merchant_id']
		);
	$payer = $propay_db->find_payer_by_email_merchanit($search);
	
	if( empty($payer[0]['payerid']) ) {
		$payer = $propay_api->create_payerid($input_data);
		if( "SUCCESS" != $payer->RequestResult->ResultValue ) {
			error_log(print_r($payer, true));
			$results['is_valid'] = false;
			$results['error_message'] = 'The payer account could not be made.  Please confirm all information and try again.';
			$results['payerid'] = $payer;
			return $results;
		}
		
		$merchantid = $input_data['merchant_id'];
		$propay_db->insert_new_payer(array(
			'email' => $input_data['email'],
			'merchantid' => $merchantid,
			'payerid' => $payer->ExternalAccountID
		));
		
		$results['payerid'] = $payer->ExternalAccountID;
		
	} else {
	
		$results['payerid'] = $payer[0]['payerid'];
		
	}
	
	return $results;
}

function Tsys_create_payment_hpp_token($input_data) {
	$propay_api = new ProPay_ProtectPay_API();
	$propay_db = new ProPay_ProtectPay_DB();
	$results = array(
		'is_valid' => true,
		'error_message' => '',
		'hpp_result' => ''
	);
	
	$result_api = $propay_api->create_hosted_transaction_identifier($input_data['merchant_id'], $input_data['payerid'], $input_data);
	if( "SUCCESS" != $result_api->Result->ResultValue ) {
		error_log(print_r($result_api, true));
		$results['is_valid'] = false;
		$results['error_message'] = 'The payment token could not be made.  Please confirm all information and try again.';
		return $results;
	}
	
	$propay_db->insert_new_payment($input_data, $result_api->HostedTransactionIdentifier);	
	$results['hpp_result'] = $result_api;
	
	return $results;
}

function Tsys_check_update_hpp_result($input_data) {
	$propay_api = new ProPay_ProtectPay_API();
	$propay_db = new ProPay_ProtectPay_DB();
	$results = array(
		'is_valid' => true,
		'error_message' => '',
		'hpp_result' => ''
	);
	
	$payments = $propay_db->find_latest_payments($input_data['payer_id']);
		
	$result_api = $propay_api->get_HPP_result($payments[0]['hpptoken']);
	
	$results['hpp_result'] = $result_api;
	
	$result = substr($_GET['payer_id'],24);
	
	if ( 'Cancel' == $result ) {
		$update = array(
			'result' => 'failed',
			'paymenttoken' => $result_api->HostedTransaction->TransactionHistoryId
			);
	} else {
		$update = array(
			'result' => $result_api->Result->ResultValue .' Code: '. $result_api->Result->ResultCode .' Message: '. $result_api->Result->ResultMessage,
			'paymenttoken' => $result_api->HostedTransaction->TransactionHistoryId
			);
	}
	
	$search = array (
		'column' => 'hpptoken',
		'value' => $payments[0]['hpptoken']
		);
	$propay_db->update_payments($search,$update);
	
	return $results;
}

function Tsys_email_receipt($input_data) {
	$propay_db = new ProPay_ProtectPay_DB();
	$results = array(
		'is_valid' => true,
		'error_message' => '',
		'email_result' => ''
	);
	
	$search = array(
		'column' => 'payerid',
		'value' => $input_data['payer_id']
	);
	$payer = $propay_db->find_payer($search);
	$payments = $propay_db->find_latest_payments($input_data['payer_id']);
	
	$message  = create_email($payer[0], $payments[0], $input_data);

    $resutls['email_result'] = wp_mail($payer[0]['email'], sprintf(__('[%s] Payment Receipt'), $input_data['merchant_name']), $message, "Content-Type: text/html\r\n");
	
	return $results;
}

function Tsys_void_payment($input_data) {
	$propay_api = new ProPay_ProtectPay_API();
	$propay_db = new ProPay_ProtectPay_DB();
	$results = array(
		'is_valid' => true,
		'error_message' => '',
		'void_result' => ''
	);
	
	$search = array(
		'column' => 'id',
		'value' => $input_data['id']
		);
	
	$result_db = $propay_db->find_payment($search);
	
	$result_api = $propay_api->void_transaction($input_data['merchant_id'], $result_db[0]['paymenttoken']);
	
	$results['void_result'] = $result_api;
	
	if( '00' == $result_api->RequestResult->ResultCode ) {
		$update = array(
			'result' => 'VOIDED'
			);
		$search = array (
			'column' => 'id',
			'value' => $input_data['id']
			);
		$propay_db->update_payments($search,$update);
	}
	
	return $results;
}

function Tsys_refund_payment($input_data) {
	$propay_api = new ProPay_ProtectPay_API();
	$propay_db = new ProPay_ProtectPay_DB();
	$results = array(
		'is_valid' => true,
		'error_message' => '',
		'refund_result' => ''
	);
	
	$search = array(
		'column' => 'id',
		'value' => $input_data['id']
		);
	
	$result_db = $propay_db->find_payment($search);
	
	$result_api = $propay_api->refund_transaction($input_data['merchant_id'], $result_db[0]['paymenttoken']);
	
	$results['refund_result'] = $result_api;
	
	if( '00' == $result_api->RequestResult->ResultCode ) {
		$update = array(
			'result' => 'REFUNDED'
			);
		$search = array (
			'column' => 'id',
			'value' => $input_data['id']
			);
		$propay_db->update_payments($search,$update);
	}
	
	return $results;
}
	