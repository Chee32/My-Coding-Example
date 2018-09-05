<?php

class ProPay_ProtectPay_DB {
	
	private $wpdb;

	public function __construct()
	{
		global $wpdb;
		$this->wpdb = $wpdb;
	}
	
	/**
     * Creates databases needed for Propay
     *
     */
	public function create_tables() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$charset_collate = $this->wpdb->get_charset_collate();
		
		$table_name = $this->wpdb->prefix."_Merchants";
		if($this->wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql_merchants = "CREATE TABLE ".$table_name." (
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			  email varchar(255) NOT NULL,
			  propayid varchar(20) NOT NULL,
			  merchantid varchar(20) NOT NULL,
			  PRIMARY KEY  (id)
			) $charset_collate;";

			dbDelta( $sql_merchants );
		}

		
		$table_name = $this->wpdb->prefix."_Payers";
		if($this->wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql_payers = "CREATE TABLE ".$table_name." (
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  merchantid varchar(20) NOT NULL,
			  payerid varchar(20) NOT NULL,
			  email varchar(255) NOT NULL,
			  PRIMARY KEY  (id)
			) $charset_collate;";
		
			dbDelta( $sql_payers );
		}
		
		$table_name = $this->wpdb->prefix."_Payments";
		if($this->wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql_payments = "CREATE TABLE ".$table_name." (
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			  invoice varchar(255) NOT NULL,
			  amount varchar(20) NOT NULL,
			  payerid varchar(20) NOT NULL,
			  hpptoken varchar(255) NOT NULL,
			  paymenttoken varchar(255) NOT NULL,
			  result varchar(255) NULL,
			  PRIMARY KEY  (id)
			) $charset_collate;";

			dbDelta( $sql_payments );
		}
	}

	/**
     * Inserts a new merchant into the merchant table
	 *
	 * @param array $inputed_data
     *
     * @return array
     */
	public function insert_new_merchant($input_data) {	
		$table_name = $this->wpdb->prefix . '_Merchants';
		
		$this->wpdb->insert( 
			$table_name, 
			array( 
				'time' => current_time( 'mysql' ), 
				'email' => $input_data['email'],
				'propayid' => $input_data['propayid'], 
				'merchantid' => $input_data['merchantid'], 
			) 
		);
	}

	/**
     * Inserts a new payer into the payer table
	 *
	 * @param array $inputed_data
     *
     * @return array
     */
	public function insert_new_payer($input_data) {
		
		$table_name = $this->wpdb->prefix . '_Payers';
		
		$this->wpdb->insert( 
			$table_name, 
			array( 
				'merchantid' => $input_data["merchantid"],
				'payerid' => $input_data["payerid"],
				'email' => $input_data["email"]
			) 
		);
	}

	/**
     * Searches the payer database for a created payer based on email and merchant id
	 *
	 * @param array $search
     *
     * @return array
     */
	public function find_payer_by_email_merchanit($search) {
		
		$table_name = $this->wpdb->prefix . '_Payers';
		
		$sql = "SELECT * FROM  $table_name WHERE email = '". $search['email'] ."' AND merchantid = '". $search['merchantid'] ."'";

		$result = $this->wpdb->get_results( $sql, 'ARRAY_A' );
		
		return $result;
	}
	
	/**
     * Gets a row from the payers database
	 *
	 * @param array $search
     *
     * @return array
     */
	public function find_payer($search) {
		
		$table_name = $this->wpdb->prefix . '_Payers';
		
		$sql = "SELECT * FROM  $table_name WHERE ". $search['column'] ." = '". $search['value'] ."'";

		$result = $this->wpdb->get_results( $sql, 'ARRAY_A' );
		
		return $result;
	}
	
	/**
     * Inserts a new payment into the payments table
	 *
	 * @param array $inputed_data
     *
     * @return array
     */
	public function insert_new_payment($input_data, $hpptoken) {	
		$table_name = $this->wpdb->prefix . '_Payments';
		
		$this->wpdb->insert( 
			$table_name, 
			array( 
				'time' => current_time( 'mysql' ),
				'invoice' => $input_data['invoice'],
				'amount' => $input_data['amount'],
				'payerid' => $input_data['payerid'],
				'hpptoken' => $hpptoken
			) 
		);
	}
	
	/**
     * Searches the payment database for the latest created payment based on payerid
	 *
	 * @param string $search
     *
     * @return array
     */
	public function find_latest_payments($search) {
		
		$table_name = $this->wpdb->prefix . '_Payments';
		
		$sql = "SELECT * FROM $table_name WHERE payerid = '". $search ."' ORDER BY ID DESC LIMIT 1";

		$result = $this->wpdb->get_results( $sql, 'ARRAY_A' );
		
		return $result;
	}
	
	/**
     * Updates the payments database based on $search
	 *
	 * @param array $search
	 * @param array $updates
     *
     * @return array
     */
	public function update_payments($search, $updates) {
		
		$table_name = $this->wpdb->prefix . '_Payments';
		
		$sql = "UPDATE $table_name SET";
		
		foreach( $updates as $column => $value) {
			$sql .= " $column = '$value',";
		}
		$sql = rtrim($sql, ',');
		
		$sql .= " WHERE ". $search['column'] ." = '". $search['value'] ."'";

		$result = $this->wpdb->get_results( $sql, 'ARRAY_A' );
		
		return $result;
	}
	
	/**
     * Gets a row from the payments database
	 *
	 * @param array $search
     *
     * @return array
     */
	public function find_payment($search) {
		
		$table_name = $this->wpdb->prefix . '_Payments';
		
		$sql = "SELECT * FROM  $table_name WHERE ". $search['column'] ." = '". $search['value'] ."'";

		$result = $this->wpdb->get_results( $sql, 'ARRAY_A' );
		
		return $result;
	}
}
	
	