<?php

/**
 * Openbiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.service
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: emailService.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

use Openbiz\Object\MetaObject;

/**
 * The email service provides access to a \Zend_Mail object in conjunction with a predefined email account.
 * One can simply request the configured email object and work directly with the \Zend_Mail API
 * or use one of several convenience functions.
 * Additional functions bundled with the class are to run email through a template filter
 * or to log attempted emails to a text file or DB Table.
 *
 * @package   openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class emailService extends MetaObject
{

    /**
     * An array of Account objects
     *
     * @var array
     */
    public $accounts = null;

    /**
     * Error Message returned by mail object
     *
     * @var string
     */
    private $_errorMessage;

    /**
     * The default account to be used when issuing email
     *
     * @var unknown_type
     */
    protected $useAccount;

    /**
     * The mail object generated by \Zend_Mail
     *
     * @var \Zend_Mail
     */
    private $_mail;

    /**
     * Is logging enabled?
     *
     * @param boolean
     */
    private $_logEnabled = null;

    /**
     * How should log entries be stored?  Can either use the default logging service or
     *  a BizDataObject in order to log a DB table
     *
     * @param boolean
     */
    private $_logType = 'DEFAULT';

    /**
     * In the case of DB logging, what BizDataObject object should be used to store log entries?
     *
     * @param object
     */
    private $_logObject;

    /**
     * Initialize emailService with xml array metadata
     *
     * @param array $xmlArr
     * @return void
     */
    function __construct(&$xmlArr)
    {
        $this->readMetadata($xmlArr);
        $this->_constructMail();
        $acct = $this->accounts->current();
        if (!$acct)
            return; //TODO Throw exception
        $this->useAccount($acct->objectName);
    }

    /**
     * Read array meta-data, and store to meta-object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetadata(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->accounts = new MetaIterator($xmlArr["PLUGINSERVICE"]["ACCOUNTS"]["ACCOUNT"], "EmailAccount");
        $this->_logEnabled = $xmlArr["PLUGINSERVICE"]["LOGGING"]["ATTRIBUTES"]["ENABLED"];
        if ($this->_logEnabled) {
            $this->_logType = $xmlArr["PLUGINSERVICE"]["LOGGING"]["ATTRIBUTES"]["TYPE"];
            $this->_logObject = $xmlArr["PLUGINSERVICE"]["LOGGING"]["ATTRIBUTES"]["OBJECT"];
        }
    }

    /**
     * Get error message
     *
     * @return string
     */
    public function getErrorMsg()
    {
        return $this->_errorMessage;
    }

    /**
     * Will construct the initial mail object
     *
     * @return void
     */
    private function _constructMail()
    {
        $this->_mail = new \Zend_Mail('utf-8');
    }

    /**
     * Will set the Default Transport object for the mail object based on the supplied accountname
     *
     * @param string $accountName
     * @return boolean TRUE/FALSE
     */
    public function useAccount($accountName)
    {
        //If a mail object exists, overwrite with new object
        if ($this->_mail)
            $this->_constructMail();

        $this->useAccount = $accountName;
        $account = $this->accounts->get($accountName);

        if ($account->isSMTP == "Y") {
            if ($account->sMTPAuth == "Y") {
                $config = array('auth' => 'login', 'username' => $account->username, 'password' => $account->password, "port" => $account->port, 'ssl' => $account->sSL);
            } else {
                $config = array();
            }
            $mailTransport = new \Zend_Mail_Transport_Smtp($account->host, $config);
            $this->_mail->setDefaultTransport($mailTransport);
        } else {
            require_once 'Zend/Mail/Transport/Sendmail.php';
            $mailTransport = new \Zend_Mail_Transport_Sendmail();
            $this->_mail->setDefaultTransport($mailTransport);
        }
        $this->_mail->setFrom($account->fromEmail, $account->fromName);
        return $this;
    }

    /**
     * A convenience function that will issue an email based on the parameter provided
     * Will log email attempts but will NOT run the body through a template
     *
     * @param array $TOs
     * @param array $CCs
     * @param array $BCCs
     * @param array $subject
     * @param array $body
     * @param array $attachments
     * @param boolean $isHTML
     * @return boolean $result - TRUE on success, FALSE on failure
     */
    public function sendEmail($TOs = null, $CCs = null, $BCCs = null, $subject, $body, $attachments = null, $isHTML = false)
    {
        $mail = $this->_mail;
        // add TO addresses
        if ($TOs) {
            foreach ($TOs as $to) {
                if (is_array($to)) {
                    $mail->addTo($to['email'], $to['name']);
                } else {
                    $mail->addTo($to);
                }
            }
        }
        // add CC addresses
        if ($CCs) {
            foreach ($CCs as $cc) {
                if (is_array($cc)) {
                    $mail->AddCC($cc['email'], $cc['name']);
                } else {
                    $mail->AddCC($cc);
                }
            }
        }
        // add BCC addresses
        if ($BCCs) {
            foreach ($BCCs as $bcc) {
                if (is_array($bcc)) {
                    $mail->AddBCC($bcc['email'], $bcc['name']);
                } else {
                    $mail->AddBCC($bcc);
                }
            }
        }
        // add attachments
        if ($attachments)
            foreach ($attachments as $att)
                $mail->CreateAttachment(file_get_contents($att));
        $mail->setSubject($subject);
        $body = str_replace("\\n", "\n", $body);
        if ($isHTML == TRUE) {
            $mail->setBodyHTML($body);
        } else {
            $mail->setBodyText($body);
        }

        try {
            $result = $mail->Send(); //Will throw an exception if sending fails
            $this->logEmail('Success', $subject, $body, $TOs, $CCs, $BCCs);
            return TRUE;
        } catch (Exception $e) {
            $result = "ERROR: " . $e->getMessage();
            $this->logEmail($result, $subject, $body, $TOs, $CCs, $BCCs);
            return FALSE;
        }
    }

    /**
     * Log that an email attemp was made.
     * We assume it was successfull, since \Zend_Mail throws an exception otherwise
     *
     * @param string $subject
     * @param array $To
     * @param array $CCs
     * @param array $BCCs
     * @return mixed boolean|string|void
     */
    public function logEmail($result, $subject, $body = NULL, $TOs = NULL, $CCs = NULL, $BCCs = NULL)
    {
        //Log the email attempt
        $recipients = '';
        // add TO addresses
        if ($TOs) {
            foreach ($TOs as $to) {
                if (is_array($to)) {
                    $recipients .= $to['name'] . "<" . $to['email'] . ">;";
                } else {
                    $recipients .= $to . ";";
                }
            }
        }
        // add CC addresses
        if ($CCs) {
            foreach ($CCs as $cc) {
                if (is_array($cc)) {
                    $recipients .= $cc['name'] . "<" . $cc['email'] . ">;";
                } else {
                    $recipients .= $cc . ";";
                }
            }
        }
        // add BCC addresses
        if ($BCCs) {
            foreach ($BCCs as $bcc) {
                if (is_array($bcc)) {
                    $recipients .= $bcc['name'] . "<" . $bcc['email'] . ">;";
                } else {
                    $recipients .= $bcc . ";";
                }
            }
        }
        if ($this->_logType == 'DB') {
            $account = $this->accounts->get($this->useAccount);
            $sender_name = $account->fromName;
            $sender = $account->fromEmail;

            // Store the message log
            $boMessageLog = Openbiz::getObject($this->_logObject);
            $mlArr = $boMessageLog->newRecord();
            $mlArr["sender"] = $sender;
            $mlArr["sender_name"] = $sender_name;
            $mlArr["recipients"] = $recipients;
            $mlArr["subject"] = $subject;
            $mlArr["content"] = $body;
            $mlArr["result"] = $result;
            /*
             * as long as data could read from db, its no longer need to addslashes
              //Escape Data since this may contain quotes or other goodies
              foreach ($mlArr as $key => $value)
              {
              $mlArr[$key] = addslashes($value);
              }
             */
            $ok = $boMessageLog->insertRecord($mlArr);
            if (!$ok) {
                return $boMessageLog->getErrorMessage();
            } else {
                return TRUE;
            }
        } else {
            $back_trace = debug_backtrace();
            if ($result == 'Success') {
                $logNum = LOG_INFO;
            } else {
                $logNum = LOG_ERR;
            }
            Openbiz::$app->getLog()->log($logNum, "EmailService", "Sent email with subject - \"$subject\" and body - $body to - $recipients with result $result.", NULL, $back_trace);
        }
    }

}

/**
 * An object that stores email account data from the service config file.
 *
 * @package   openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class EmailAccount extends MetaObject
{

    public $objectName;
    public $host;
    public $port;
    public $sSL;
    public $fromName;
    public $fromEmail;
    public $isSMTP;
    public $sMTPAuth;
    public $username;
    public $password;

    /**
     * Load the supplied array of config data into the object
     *
     * @param array $xmlArr
     */
    public function __construct($xmlArr)
    {
        $this->objectName = $xmlArr["ATTRIBUTES"]["NAME"];
        $this->host = $xmlArr["ATTRIBUTES"]["HOST"];
        $this->port = $xmlArr["ATTRIBUTES"]["PORT"];
        $this->sSL = $xmlArr["ATTRIBUTES"]["SSL"];
        $this->fromName = $xmlArr["ATTRIBUTES"]["FROMNAME"];
        $this->fromEmail = $xmlArr["ATTRIBUTES"]["FROMEMAIL"];
        $this->isSMTP = $xmlArr["ATTRIBUTES"]["ISSMTP"];
        $this->sMTPAuth = $xmlArr["ATTRIBUTES"]["SMTPAUTH"];
        $this->username = $xmlArr["ATTRIBUTES"]["USERNAME"];
        $this->password = $xmlArr["ATTRIBUTES"]["PASSWORD"];
    }

}
