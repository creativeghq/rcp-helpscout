<?php

/*
 * Handles the response to Help Scout queries
 *
 */
class PW_RCP_Help_Scout_Plugin_Handler {

	private $input   = false;
	private $api_key = '';
	private $secret  = '';
	private $email   = '';

	public function __construct() {
		global $rcp_options;

		if( ! empty( $rcp_options['hs_api_key'] ) ) {
			$this->api_key = trim( $rcp_options[ 'hs_api_key' ] );
		}

		if( ! empty( $rcp_options['hs_secret_key'] ) ) {
			$this->secret = trim( $rcp_options[ 'hs_secret_key' ] );
		}

		if( ! empty( $rcp_options['hs_email'] ) ) {
			$this->email = trim( $rcp_options[ 'hs_email' ] );
		}
	}

	/**
	 * Returns the requested HTTP header.
	 *
	 * @param string $header
	 * @return bool|string
	 */
	private function getHeader( $header ) {
		if ( isset( $_SERVER[$header] ) ) {
			return $_SERVER[$header];
		}
		return false;
	}

	/**
	 * Retrieve the JSON input
	 *
	 * @return bool|string
	 */
	private function getJsonString() {
		if ( $this->input === false ) {
			$this->input = @file_get_contents( 'php://input' );
		}
		return $this->input;
	}

	/**
	 * Generate the signature based on the secret key, to compare in isSignatureValid
	 *
	 * @return bool|string
	 */
	private function generateSignature() {
		$str = $this->getJsonString();
		if ( $str ) {
			return base64_encode( hash_hmac( 'sha1', $str, $this->secret, true ) );
		}
		return false;
	}

	/**
	 * Returns true if the current request is a valid webhook issued from Help Scout, false otherwise.
	 *
	 * @return boolean
	 */
	private function isSignatureValid() {

		$signature = $this->generateSignature();

		if ( ! $signature || ! $this->getHeader( 'HTTP_X_HELPSCOUT_SIGNATURE' ) ) {
			return false;
		}

		return $signature == $this->getHeader( 'HTTP_X_HELPSCOUT_SIGNATURE' );
	}

	/**
	 * Create a response.
	 *
	 * @return array
	 */
	public function getResponse() {

		$ret = array( 'html' => '' );

		if ( ! $this->isSignatureValid() ) {
			return array( 'html' => 'Invalid signature' );
		}

		$data = json_decode( $this->input, true );

		// do some stuff
		$ret['html'] = $this->fetchHtml( $data );

		// Used for debugging
		// $ret['html'] = '<pre>'.print_r($data,1).'</pre>' . $ret['html'];

		return $ret;
	}

	/**
	 * Generate output for the response.
	 *
	 * @param $data
	 * @return string
	 */
	private function fetchHtml( $data ) {

		$emails = array();

		if ( isset( $data['customer']['emails'] ) && is_array( $data['customer']['emails'] ) ) {

			if( ( $key = array_search( $this->email, $data['customer']['emails'] ) ) !== false ) {
			    unset( $data['customer']['emails'][ $key ] );
			}

			$emails = $data['customer']['emails'];

		} else {

			if ( $data['customer']['email'] == $this->email ) {
				return sprintf( __( 'Cannot query member account. E-mail from %s', 'rcp-help-scout' ), $this->email );
			}

			$emails[] = $data['customer']['email'];

		}

		foreach( $emails as $email ) {

			$user = get_user_by( 'email', $email );
			if( $user ) {
				break;
			}

		}


		if( ! $user ) {

			return sprintf( __( 'No member found for %s', 'rcp-help-scout' ), $email );

		}

		$member = new RCP_Member( $user->ID );

		if( $member->get_subscription_id() ) {

			$rcpinfo = array();

			if ( $member->is_active() ) {
				$rcp = sprintf( '<strong style="color: green;">' . __( 'Valid Subscription', 'rcp-help-scout' ) . '</strong> %s', $subscription );
			} elseif( 'cancelled' == $member->get_status() ) {
				$rcp = sprintf( '<strong style="color: gray;">' . __( 'Subscription Cancelled', 'rcp-help-scout' ) . '</strong> %s', $subscription );
			} elseif( $member->is_expired() ) {
				$rcp = sprintf( '<strong style="color: red;">' . __( 'Subscription Expired', 'rcp-help-scout' ) . '</strong> %s', $subscription );
			}

			$rcpinfo[] = $rcp;
			$rcpinfo[] = sprintf( '<strong>' . __( 'Status', 'rcp-help-scout' ) . '</strong>: %s', $member->get_status() );
			$rcpinfo[] = sprintf( '<strong>' . __( 'Subscription Level', 'rcp-help-scout' ) . '</strong>: %s', $member->get_subscription_name() );
			$rcpinfo[] = sprintf( '<strong>' . __( 'Expiration', 'rcp-help-scout' ) . '</strong>: %s', $member->get_expiration_date() );
			$rcpinfo[] = sprintf( '<a href="%s">' . __( 'View Member Details', 'rcp-help-scout' ) . '</a>', admin_url( 'admin.php?page=rcp-members&edit_member=' . $member->ID ) );

			$html .= '<h4 class="toggleBtn"><i class="icon-gear"></i> '. __( 'Subscription', 'rcp-help-scout' ) .'</h4>';
			$html .= '<ul><li>';
			$html .= implode( '</li><li>', $rcpinfo );
			$html .= '</li></ul>';

		}

		return $html;
	}
}