<?php

//******************************************************************************
// Operational Constants
//******************************************************************************

// Gateway server connection information
define('SAGE_URL_VAULT', 'https://va.eftsecure.net/web_services/wsVault/wsVault.asmx/');
define('SAGE_URL_VAULT_BANKCARD', 'https://va.eftsecure.net/web_services/wsVault/wsVaultBankcard.asmx/');
define('SAGE_URL_EFT_BANKCARD', 'https://www.sagepayments.net/web_services/vterm_extensions/transaction_processing.asmx/');
define('SAGE_TIMEOUT', 10);

//******************************************************************************
// Sage Payment Class
//******************************************************************************

/** Define a "No Error" constant for socket operations. */
if (! defined('NO_ERROR')) define('NO_ERROR', 0, true);

/**
 * Access to the Sage Payment Solutions EFT and Vault gateways.
 *
 * In addition, here are some test credit card numbers to use with the data
 * (do not include the hyphens):
 * @li VISA - 4111-1111-1111-1111
 * @li American Express - 3714-4963-5392-376
 * @li Discover - 6011-0009-9302-6909
 *
 * @link https://va.eftsecure.net/web_services/wsVault/wsVault.asmx
 * @link https://va.eftsecure.net/web_services/wsVault/wsVaultBankcard.asmx
 * @link https://www.sagepayments.net/web_services/vterm_extensions/transaction_processing.asmx
 *
 * @link http://www.mombu.com/php/php/t-32919-curlopenssl-random-crashes-with-fsockopenssl-under-heavy-load-871833.html
 *
 * @author Jerry D'Antonio
 */
class SagePayment {

    ////////////////////////////////////////////////////////////////////////////
    // Member Data
    ////////////////////////////////////////////////////////////////////////////

    // Gateway server connection information
    private static $UrlVault = SAGE_URL_VAULT;
    private static $UrlVaultBankcard = SAGE_URL_VAULT_BANKCARD;
    private static $UrlEftBankcard = SAGE_URL_EFT_BANKCARD;
    private static $Timeout = SAGE_TIMEOUT;

    // Gateway authentication information
    public $m_id;
    public $m_key;

    // cURL handle
    private $Curl;

    // Error variables
    private $Error;
    private $Errno;
    private $Response;

    ////////////////////////////////////////////////////////////////////////////
    // Construction and Destruction
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Constructor that sets the authentication values.
     *
     * @param params An associative array containing two required values
     *        @li m_id The M_id authentication value
     *        @li m_key The M_key authentication value
     *
     * @author Jerry D'Antonio
     */
    public function __construct(/*array*/ $params) {

        // set the Vault info
        $this->m_id = $params['m_id'];
        $this->m_key = $params['m_key'];

        // "zero" the cURL handle
        $this->Curl = false;

        // reset everyting else
        $this->reset();
    }

    /**
     * Destructor. Closes the cURL session.
     *
     * @author Jerry D'Antonio
     */
    public function __destruct() {
        if ($this->Curl) curl_close($this->Curl);
    }

    /**
     * Reset the cURL session and set common options. Creates a new session if
     * one does not exists.
     *
     * @return Boolean indicating the success of the initialization.
     *
     * @author Jerry D'Antonio
     */
    public function reset() {

        // close an open session if it exists
        if ($this->Curl) curl_close($this->Curl);

        // reset the error variables
        $this->Error = "";
        $this->Errno = NO_ERROR;
        $this->Response = "";

        // initialize a new session
        $this->Curl = curl_init();

        // set common options on success
        if ($this->Curl) {
            curl_setopt($this->Curl, CURLOPT_POST, TRUE);
            curl_setopt($this->Curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($this->Curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($this->Curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($this->Curl, CURLOPT_CONNECTTIMEOUT, self::$Timeout);
        }

        // return a boolean
        if ($this->Curl) {
            return(true);
        } else {
            return(false);
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    // Accessor Methods
    ////////////////////////////////////////////////////////////////////////////

    /**
     * The error message returned by the last socket operation.
     *
     * Accessor method for the read-only property.
     *
     * @return The string value of the property.
     *
     * @author Jerry D'Antonio
     */
    public function getError() {
        return($this->Error);
    }

    /**
     * The error number returned by the last socket operation.
     *
     * Accessor method for the read-only property.
     *
     * @return The integer value of the property.
     *
     * @author Jerry D'Antonio
     */
    public function getErrno() {
        return($this->Errno);
    }

    /**
     * The response text returned by the last Vault operation.
     *
     * Accessor method for the read-only property.
     *
     * @return The string value of the property.
     *
     * @author Jerry D'Antonio
     */
    public function getResponse() {
        return($this->Response);
    }

    /**
     * Convert this object to a string.
     *
     * The output multiple lines incorporating Errno, Error, and Message.
     *
     * @return A string representation of this object.
     *
     * @author Jerry D'Antonio
     */
    public function toString() {
        return(sprintf("Error Number: %d\nError Message: %s\nResponse Text:\n%s\n",
                       $this->getErrno(), $this->getError(), $this->getResponse()));
    }

    /**
     * Cast this object as a string.
     *
     * @return A string representing this object.
     *
     * @see toString
     *
     * @author Jerry D'Antonio
     */
    public function __toString() {
        return($this->toString());
    }

    ////////////////////////////////////////////////////////////////////////////
    // Utility Functions
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Send an HTTP request to the Vault.
     *
     * @param service The name of the Vault service that is being requested.
     * @param data_array An optional associative array containing a list of
     *        data field names and the associated values. This data will be
     *        added to the Vault request.
     *
     * @return The Errno returned by the Vault.
     *
     * @post The Error, Errno, and Response data members will be set appropriately.
     *
     * @author Jerry D'Antonio
     */
    private function SendRequest(/*string*/ $url, /*string*/ $service, /*array*/ $post_fields = NULL) {

        if (! is_array($post_fields)) $post_fields = array();

        // set the M_ID and M_KEY
        $post_fields['M_ID'] = $this->m_id;
        $post_fields['M_KEY'] = $this->m_key;

        // set the URL
        curl_setopt($this->Curl, CURLOPT_URL, $url.$service);

        // set the POST data
        $post_data = '';
        foreach ($post_fields as $key => $value) $post_data .= "{$key}={$value}&";
        curl_setopt($this->Curl, CURLOPT_POSTFIELDS, $post_data);

        // execute the cURL command
        $this->Response = curl_exec($this->Curl);
        $this->Errno = curl_errno($this->Curl);
        $this->Error = curl_error($this->Curl);

        // return errno
        return($this->Errno);
    }

    ////////////////////////////////////////////////////////////////////////////
    // EFT and Vault Service Functions
    ////////////////////////////////////////////////////////////////////////////

    /**
     * VERIFY_SERVICE command to verify that the EFT Gateway is operational.
     *
     * @return Boolean value indicating the status of the Gateway.
     *
     * @post The Error, Errno, and Response data members will be set appropriately.
     *
     * @author Jerry D'Antonio
     */
    public function EftVerifyService() {

        // set the return value - presume error
        $ok = false;

        // send the request
        $this->SendRequest(self::$UrlEftBankcard, 'VERIFY_BANKCARD_SERVICE');

        // parse the response
        if ($this->getErrno() == NO_ERROR) {
            $xml = simplexml_load_string($this->getResponse());
            if ($xml !== FALSE) {
                $xml = $xml->children('diffgr', TRUE)->children();
                $ok = strtolower($xml->NewDataSet->Table1->SERVICE_INDICATOR) == 'true';
            }
        }

        // return the status of the Vault
        return($ok);
    }

    /**
     * VERIFY_SERVICE command to verify that the Vault is operational.
     *
     * This is the only Vault command that is not a web service.
     *
     * @return Boolean value indicating the status of the Vault.
     *
     * @post The Error, Errno, and Response data members will be set appropriately.
     *
     * @author Jerry D'Antonio
     */
    public function VaultVerifyService() {

        // set the return value - presume error
        $ok = false;

        // send the request
        $this->SendRequest(self::$UrlVault, 'VERIFY_SERVICE');

        // parse the response
        if ($this->getErrno() == NO_ERROR) {
            $xml = simplexml_load_string($this->getResponse());
            $ok = strtolower(strval($xml)) == 'true';
        }

        // return the status of the Vault
        return($ok);
    }

    /**
     * INSERT_CREDIT_CARD_DATA command to add a new credit card to the Vault.
     *
     * @param cardnumber The credit card number. Dashes will be removed.
     * @param exp_month Month that the credit card expires. Will be left padded
     *        if necessary.
     * @param exp_year Year that the credit card expires (2 or 4 digit). Will
     *        be truncated and left padded as necessary.
     *
     * @return An associative array containing the response from the request
     *         or FALSE on failure.
     *
     * @post The Error, Errno, and Response data members will be set appropriately.
     *
     * @author Jerry D'Antonio
     */
    public function VaultInsertCreditCardData(/*string*/ $cardnumber,
        /*string*/ $exp_month, /*string*/ $exp_year) {

        $data = array();

        // set the service string
        $url = self::$UrlVault;
        $service = "INSERT_CREDIT_CARD_DATA";

        // add the card number to the data array
        $data["CARDNUMBER"] = $cardnumber;

        // add the expiration date to the data array
        $exp_month = str_pad($exp_month, 2, "0", STR_PAD_LEFT);
        $exp_year = str_pad($exp_year, 4, "0", STR_PAD_LEFT);
        $data["EXPIRATION_DATE"] = $exp_month . substr($exp_year, -2);

        // send the request
        $this->SendRequest($url, $service, $data);

        // return the status of the Vault
        if ($this->getErrno() == NO_ERROR) {
            return($this->parseGenericResponseToArray($this->getResponse()));
        } else {
            return(false);
        }
    }

    public function VaultGetCreditCardData(/*string*/ $guid) {

        $data = array();

        // set the service string
        $url = self::$UrlVault;
        $service = "SELECT_DATA";

        // add the parameters to the data array
        $data["GUID"] = $guid;

        // send the request
        $this->SendRequest($url, $service, $data);

        // return the status of the Vault
        if ($this->getErrno() == NO_ERROR) {
            return($this->parseGenericResponseToString($this->getResponse()));
        } else {
            return(false);
        }
    }

    /**
     * VAULT_BANKCARD_SALE command to add a new credit card to the Vault.
     *
     * @param data An associative array with all necessary customer data
     *        @li m_id
     *        @li m_key
     *        @li guid
     *        @li first_name
     *        @li last_name
     *        @li address1
     *        @li address2
     *        @li city
     *        @li state
     *        @li zip
     *        @li country
     *        @li email
     *        @li phone
     *        @li fax
     *        @li customernum
     *        @li ordernum
     *        @li amount
     *        @li shipping
     *        @li tax
     *
     * @return An associative containing the response from the request
     *         or FALSE on failure.
     *
     * @pre The credit card must have been previously stored in the Vault.
     * @post The Error, Errno, and Response data members will be set appropriately.
     *
     * @see VaultInsertCreditCardData
     *
     * @author Jerry D'Antonio
     */
    public function VaultBankcardSale(/*array*/ $vars) {

        // set the service string
        $url = self::$UrlVaultBankcard;
        $service = "VAULT_BANKCARD_SALE";

        // add the card number to the data array
        $data["C_NAME"] = $vars['first_name'] . ' ' . $vars['last_name'];
        $data["C_ADDRESS"] = $vars['address1'] . ' ' . $vars['address2'];
        $data["C_CITY"] = $vars['city'];
        $data["C_STATE"] = $vars['state'];
        $data["C_ZIP"] = $vars['zip'];
        $data["C_COUNTRY"] = $vars['country'];
        $data["C_EMAIL"] = $vars['email'];
        $data["GUID"] = $vars['charge_card_alias'];
        $data["T_CUSTOMER_NUMBER"] = $vars['customernum'];
        $data["T_AMT"] = $vars['amount'];
        $data["T_SHIPPING"] = $vars['shipping'];
        $data["T_TAX"] = $vars['tax'];
        $data["T_ORDERNUM"] = $vars['ordernum'];
        $data["C_TELEPHONE"] = $vars['phone'];
        $data["C_FAX"] = $vars['fax'];
        $data["C_SHIP_NAME"] = $vars['first_name'] . ' ' . $vars['last_name'];
        $data["C_SHIP_ADDRESS"] = $vars['address1'] . ' ' . $vars['address2'];
        $data["C_SHIP_CITY"] = $vars['city'];
        $data["C_SHIP_STATE"] = $vars['state'];
        $data["C_SHIP_ZIP"] = $vars['zip'];
        $data["C_SHIP_COUNTRY"] = $vars['country'];

        // send the request
        $this->SendRequest($url, $service, $data);

        // return the status of the Vault
        if ($this->getErrno() == NO_ERROR) {
            return($this->parseGenericResponseToArray($this->getResponse()));
        } else {
            return(false);
        }
    }

    public function EftBankcardSale(/*array*/ $vars) {

        // set the service string
        $url = self::$UrlEftBankcard;
        $service = "BANKCARD_SALE";

        // add the card number to the data array
        $data["C_NAME"] = $vars['first_name'] . ' ' . $vars['last_name'];
        $data["C_ADDRESS"] = $vars['address1'] . ' ' . $vars['address2'];
        $data["C_CITY"] = $vars['city'];
        $data["C_STATE"] = $vars['state'];
        $data["C_ZIP"] = $vars['zip'];
        $data["C_COUNTRY"] = $vars['country'];
        $data["C_EMAIL"] = $vars['email'];
        $data["C_CARDNUMBER"] = $vars['card_number'];
        $data["C_CVV"] = $vars['card_cvv'];
        $data["T_CUSTOMER_NUMBER"] = $vars['customernum'];
        $data["T_AMT"] = $vars['amount'];
        $data["T_SHIPPING"] = $vars['shipping'];
        $data["T_TAX"] = $vars['tax'];
        $data["T_ORDERNUM"] = $vars['ordernum'];
        $data["C_TELEPHONE"] = $vars['phone'];
        $data["C_FAX"] = $vars['fax'];
        $data["C_SHIP_NAME"] = $vars['first_name'] . ' ' . $vars['last_name'];
        $data["C_SHIP_ADDRESS"] = $vars['address1'] . ' ' . $vars['address2'];
        $data["C_SHIP_CITY"] = $vars['city'];
        $data["C_SHIP_STATE"] = $vars['state'];
        $data["C_SHIP_ZIP"] = $vars['zip'];
        $data["C_SHIP_COUNTRY"] = $vars['country'];

        // add the expiration date to the data array
        $exp_month = str_pad($vars['card_exp_month'], 2, "0", STR_PAD_LEFT);
        $exp_year = str_pad($vars['card_exp_year'], 4, "0", STR_PAD_LEFT);
        $data["C_EXP"] = $exp_month . substr($exp_year, -2);

        // send the request
        $this->SendRequest($url, $service, $data);

        // return the status of the Vault
        if ($this->getErrno() == NO_ERROR) {
            return($this->parseGenericResponseToArray($this->getResponse()));
        } else {
            return(false);
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    // Helper Functions
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Perform simple pasring of an XML response from the Sage Vault.
     *
     * @param response The XML response from the Sage Vault
     *
     * @return An associative array with the data
     *         @li SUCCESS => TRUE or FALSE
     *         @li GUID
     *         @li MESSAGE
     *
     * @author Jerry D'Antonio
     */
    private function parseGenericResponseToArray(/*string*/ $response) {

        // collection of all property data.
        $data = array();

        // RegEx pattern for capturing the name and value of an XML element.
        $single_element_pattern = "/(<)(\w+)(>)([^<]+)(<\/\w+>)/i";

        preg_match_all($single_element_pattern, $response, $matches);

        for($i = 0; $i < count($matches[2]); $i++) {
            $data[strtoupper(trim($matches[2][$i]))] = trim($matches[4][$i]);
        }

        //echo '<p><ul>';
        //foreach ($data as $key => $val) {
        //    echo '<li>' . $key . "=" . $val . '</li>';
        //}
        //echo '</ul></p>';

        return($data);
    }

    /**
     * Perform simple pasring of an XML response from the Sage Vault.
     *
     * @param response The XML response from the Sage Vault
     *
     * @return A string representing the response data.
     *
     * @author Jerry D'Antonio
     */
    private function parseGenericResponseToString(/*string*/ $response) {

        // return data
        $data = '';

        // RegEx pattern for capturing string data in a Vault string response.
        $single_element_pattern = "/wsVault\">([^<]+)<\/string>/i";

        // attempt the match
        preg_match($single_element_pattern, $response, $matches);

        // if matched return data
        if (count($matches) > 1) {
            return($matches[1]);
        } else {
            return('');
        }
    }
}
?>
