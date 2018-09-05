<?php
class ProPay_ProtectPay_API {
	
	/*Creds*/
	private $propay_key;
	private $protectpay_key;
	private $certStr;
	private $termId;
	
	/*Set Credentials Removed for security */
	function __construct() {
		$this->propay_key = "";
		$this->protectpay_key = "";
		$this->certStr = "";
		$this->termId = "";
	}
	
	/**
     * Runs a curl call
     *
     * @param str $endpoint
	 * @param array $data
	 * @param str $type
	 * @param str $key_to_use
     *
     * @return object
     */
	public function make_curl_call($endpoint, $data, $type, $key_to_use) {
		
		$key = $this->protectpay_key;
		
		if ( 'Pro' == $key_to_use ) {
			$key = $this->propay_key;
		}
		
		$key_encode = base64_encode($key);
		$Auth = "Authorization:Basic ".$key_encode;
		$header = array(
			"Content-Type:application/json",
			$Auth
		);
				
		$dataF = json_encode($data);
		
		$url = "https://xmltestapi.propay.com/". $endpoint;
		
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		if ( !empty($data) ) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $dataF);
		}
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$outputstring = curl_exec($ch);
		$output = json_decode($outputstring);
		
		return $output;
	}
	
	/**
     * Creates a Propay account
	 *
	 * @param array $inputed_data
     *
     * @return array
     */
	public function create_Propay_account($inputed_data) {
		$data = array(
			"PersonalData" => array(
				"FirstName" => $inputed_data['first_name'],
				"LastName" => $inputed_data['last_name'],
				"DateOfBirth" => $inputed_data['dob'],
				"SocialSecurityNumber" => $inputed_data['ssn'],
				"SourceEmail" => $inputed_data['email'],
				"PhoneInformation" => array (
					"DayPhone" => $inputed_data['phone'],
					"EveningPhone" => $inputed_data['phone']
				),
				"InternationalSignUpData" => null,
				"NotificationEmail" => $inputed_data['note_email']
			),
			"SignupAccountData" => array (
				"Tier" => "Test"
			),
			"BusinessData" => array(
				"BusinessLegalName" => $inputed_data['business_name'],
				"DoingBusinessAs" => $inputed_data['dba'],
				"EIN" => $inputed_data['ein'],
				"WebsiteURL" => $inputed_data['business_url'],
				"BusinessDescription" => $inputed_data['business_description'],
				"MonthlyBankCardVolume" => $inputed_data['business_monthly_volume'],
				"AverageTicket" => $inputed_data['business_average_ticket'],
				"HighestTicket" => $inputed_data['business_highest_ticket']
			),
			"CreditCardData" => array(
				"NameOnCard" => $inputed_data['cc_name'],
				"CreditCardNumber" => $inputed_data['cc_number'],
				"ExpirationDate" => $inputed_data['cc_exp']
			),
			"Address" => array( 
				"Address1" => $inputed_data['address_1'],
				"Address2" => $inputed_data['address_2'],
				"City" => $inputed_data['address_city'],
				"State" => $inputed_data['address_state'],
				"Country" => $inputed_data['address_country'],
				"Zip" => $inputed_data['address_zip']
			),
			"BusinessAddress" => array(
				"Address1" => $inputed_data['business_address_1'],
				"Address2" => $inputed_data['business_address_2'],
				"City" => $inputed_data['business_address_city'],
				"State" => $inputed_data['business_address_state'],
				"Country" => $inputed_data['business_address_country'],
				"Zip" => $inputed_data['business_address_zip']
			),
			"BankAccount" => array (
				"AccountCountryCode" => $inputed_data['bank_country_code'],
				"BankAccountNumber" => $inputed_data['bank_number'],
				"RoutingNumber" => $inputed_data['bank_routing_number'],
				"AccountOwnershipType" => $inputed_data['bank_ownership_type'],
				"BankName" => $inputed_data['bank_name'],
				"AccountType" => $inputed_data['bank_type'],
				"AccountName" => $inputed_data['bank_account_name'],
				"Description" => $inputed_data['bank_discription']
			),
		);
		
		$results = $this->make_curl_call("propayapi/signup", $data, "PUT", "Pro");
				
		return $results;
	}
	
	/**
     * Creates a Protectpay merchant account linked to a Propay account
	 *
	 * @param string $propay_id
     *
     * @return object
     */
	public function create_merchant_id ($propay_id) {
		$data = array(
			"ProfileName" => "",
			"PaymentProcessor" => "LegacyProPay",
			"ProcessorData" => array(
				array(
					"ProcessorField" => "certStr",
					"Value" => $this->certStr
				),
				array(
					"ProcessorField" => "accountNum",
					"Value" => $propay_id //change to reference Session or $this
				),
				array(
					"ProcessorField" => "termId",
					"Value" => $this->termId
				),
			)
		);
		
		$results = $this->make_curl_call("protectpay/MerchantProfiles", $data, "PUT", "Protect");
		
		return $results;
	}
	
	/**
     * Creates a Protectpay payer account linked to a Protectpay merchant account
	 *
	 * @param string $inputed_data
     *
     * @return object
     */
	public function create_payerid ($inputed_data) {
		$data = array(
			"Name" => $inputed_data['payer_id'],
			"EmailAddress" => $inputed_data['email'],
			"ExternalId1" => $inputed_data['ext1'],
			"ExternalId2" => $inputed_data['ext2']
		);
		
		$results = $this->make_curl_call("protectpay/Payers", $data, "PUT", "Protect");
		
		return $results;
	}
	
	/**
     * Creates a Protectpay HostedTransactionIdentifier
	 *
	 * @param string $merchantid
	 * @param string $payerid
	 * @param array $inputed_data
     *
     * @return object
     */	
	public function create_hosted_transaction_identifier($merchantid, $payerid, $inputed_data) {
		
		$data = array(
			"PayerAccountId" => $payerid,
			"MerchantProfileId" => $merchantid,
			"Amount" => $inputed_data['amount'],
			"CurrencyCode" => $inputed_data['currency_code'],
			"InvoiceNumber" => $inputed_data['invoice'],
			"Comment1" => "Created by ".$inputed_data['date']." on ". $inputed_data['payer_location'],
			"CardHolderNameRequirementType" => 1,
			"SecurityCodeRequirementType" => 1,
			"AvsRequirementType" => 1, //do we need avs?
			"AuthOnly" => true,
			"ProcessCard" => true,
			"StoreCard" => false,
			"CssUrl" => $inputed_data['style'],
			"Address1" => "3400 N Ashton Blvd",
			"City" => "Lehi",
			"Country" => "USA",
			"Name" => "John Smith",
			"State" => "UT",
			"ZipCode" => "84043",
			"ReturnURL" =>  $inputed_data['return_address'].'?payer_id='.$payerid,
			"PaymentTypeId" => "0"
		);
		
		$results = $this->make_curl_call("protectpay/HostedTransactions", $data, "PUT", "Protect");
		
		return $results;
	}
	
	/**
     * Returns the result of a Hosted Transaction
	 *
	 * @param string $hpptoken
     *
     * @return object
     */	
	public function get_HPP_result($hpptoken) {
		$url = "protectpay/HostedTransactionResults/". $hpptoken;
		$results = $this->make_curl_call($url, array(), "GET", "Protect");
		
		return $results;
	}
	
	/**
	 * Void a transation by transaction history id
	 *
	 * @param string $merchantid
	 * @param string $tranhid
     *
     * @return object
     */	
	public function void_transaction($merchantid, $tranhid) {
		$url = "ProtectPay/VoidedTransactions/";
		$data = array (
			"MerchantProfileId" => $merchantid,
			"TransactionHistoryId" => $tranhid
			);
		$results = $this->make_curl_call($url, $data, "PUT", "Protect");
		
		return $results;
	}
	
	/**
	 * Refund a transation by transaction history id
	 *
	 * @param string $merchantid
	 * @param string $tranhid
     *
     * @return object
     */	
	public function refund_transaction($merchantid, $tranhid) {
		$url = "ProtectPay/RefundTransaction/";
		$data = array (
			"MerchantProfileId" => $merchantid,
			"TransactionHistoryId" => $tranhid
			);
		$results = $this->make_curl_call($url, $data, "PUT", "Protect");
		
		return $results;
	}
}
?> 