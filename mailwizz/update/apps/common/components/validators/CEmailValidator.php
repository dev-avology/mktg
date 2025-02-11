<?php declare(strict_types=1);
/**
 * CEmailValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CEmailValidator validates that the attribute value is a valid email address.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.validators
 * @since 1.0
 */
class CEmailValidator extends CValidator
{
    /**
     * @var string the regular expression used to validate the attribute value.
     * @see http://www.regular-expressions.info/email.html
     */
    public $pattern='/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';
    /**
     * @var string the regular expression used to validate email addresses with the name part.
     * This property is used only when {@link allowName} is true.
     * @see allowName
     */
    public $fullPattern='/^[^@]*<[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?>$/';
    /**
     * @var boolean whether to allow name in the email address (e.g. "Qiang Xue <qiang.xue@gmail.com>"). Defaults to false.
     * @see fullPattern
     */
    public $allowName=false;
    /**
     * @var boolean whether to check the MX record for the email address.
     * Defaults to false. To enable it, you need to make sure the PHP function 'checkdnsrr'
     * exists in your PHP installation.
     * Please note that this check may fail due to temporary problems even if email is deliverable.
     */
    public $checkMX=false;
    /**
     * @var boolean whether to check port 25 for the email address.
     * Defaults to false. To enable it, ensure that the PHP functions 'dns_get_record' and
     * 'fsockopen' are available in your PHP installation.
     * Please note that this check may fail due to temporary problems even if email is deliverable.
     */
    public $checkPort=false;
    /**
     * @var null|int timeout to use when attempting to open connection to port in checkMxPorts. If null (default)
     * use default_socket_timeout value from php.ini. If not null the timeout is set in seconds.
     * @since 1.1.19
     */
    public $timeout;
    /**
     * @var boolean whether the attribute value can be null or empty. Defaults to true,
     * meaning that if the attribute is empty, it is considered valid.
     */
    public $allowEmpty=true;
    /**
     * @var boolean whether validation process should care about IDN (internationalized domain names). Default
     * value is false which means that validation of emails containing IDN will always fail.
     * @since 1.1.13
     */
    public $validateIDN=true;

    /**
     * Validates a static value to see if it is a valid email.
     * This method is provided so that you can call it directly without going through the model validation rule mechanism.
     *
     * Note that this method does not respect the {@link allowEmpty} property.
     *
     * @param mixed $value the value to be validated
     * @return boolean whether the value is a valid email
     * @since 1.1.1
     * @see https://github.com/yiisoft/yii/issues/3764#issuecomment-75457805
     */
    public function validateValue($value)
    {
        if (is_string($value) && $this->validateIDN) {
            $value=$this->encodeIDN($value);
        }
        // make sure string length is limited to avoid DOS attacks
        $valid=is_string($value) && strlen($value)<=254 && (preg_match($this->pattern, $value) || $this->allowName && preg_match($this->fullPattern, $value));
        $domain = '';
        if ($valid) {
            $domain=rtrim(substr($value, strpos($value, '@')+1), '>');
        }
        if ($valid && $this->checkMX && function_exists('checkdnsrr')) {
            $valid=checkdnsrr($domain, 'MX');
        }
        if ($valid && $this->checkPort && function_exists('fsockopen') && function_exists('dns_get_record')) {
            $valid=$this->checkMxPorts($domain);
        }
        return $valid;
    }

    /**
     * Returns the JavaScript needed for performing client-side validation.
     * @param CModel $object the data object being validated
     * @param string $attribute the name of the attribute to be validated.
     * @return string the client-side validation script.
     * @see CActiveForm::enableClientValidation
     * @since 1.1.7
     */
    public function clientValidateAttribute($object, $attribute)
    {
        if ($this->validateIDN) {
            Yii::app()->getClientScript()->registerCoreScript('punycode');
            // punycode.js works only with the domains - so we have to extract it before punycoding
            $validateIDN='
var info = value.match(/^(.[^@]+)@(.+)$/);
if (info)
	value = info[1] + "@" + punycode.toASCII(info[2]);
';
        } else {
            $validateIDN='';
        }

        $message=$this->message!==null ? $this->message : Yii::t('yii', '{attribute} is not a valid email address.');
        $message=strtr($message, [
            '{attribute}'=>$object->getAttributeLabel($attribute),
        ]);

        $condition="!value.match({$this->pattern})";
        if ($this->allowName) {
            $condition.=" && !value.match({$this->fullPattern})";
        }

        return "
$validateIDN
if(" . ($this->allowEmpty ? "jQuery.trim(value)!='' && " : '') . $condition . ') {
	messages.push(' . json_encode($message) . ');
}
';
    }

    /**
     * Validates the attribute of the object.
     * If there is any error, the error message is added to the object.
     * @param CModel $object the object being validated
     * @param string $attribute the attribute being validated
     * @return void
     */
    protected function validateAttribute($object, $attribute)
    {
        $value=$object->$attribute;
        if ($this->allowEmpty && $this->isEmpty($value)) {
            return;
        }

        if (!$this->validateValue($value)) {
            $message=$this->message!==null ? $this->message : Yii::t('yii', '{attribute} is not a valid email address.');
            $this->addError($object, $attribute, $message);
        }
    }

    /**
     * Retrieves the list of MX records for $domain and checks if port 25
     * is opened on any of these.
     * @since 1.1.11
     * @param string $domain domain to be checked
     * @return boolean true if a reachable MX server has been found
     */
    protected function checkMxPorts($domain)
    {
        $records=dns_get_record($domain, DNS_MX);
        if ($records===false || empty($records)) {
            return false;
        }
        $timeout=is_int($this->timeout) ? $this->timeout : ((int)ini_get('default_socket_timeout'));
        usort($records, [$this, 'mxSort']);
        foreach ($records as $record) {
            $handle=@fsockopen($record['target'], 25, $errno, $errstr, $timeout);
            if ($handle!==false) {
                if (FileSystemHelper::isStreamResource($handle)) {
                    fclose($handle);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Determines if one MX record has higher priority as another
     * (i.e. 'pri' is lower). Used by {@link checkMxPorts}.
     * @since 1.1.11
     * @param array $a first item for comparison
     * @param array $b second item for comparison
     * @return int
     */
    protected function mxSort($a, $b)
    {
        return (int)$a['pri']-(int)$b['pri'];
    }

    /**
     * Converts given IDN to the punycode.
     * @param string $value IDN to be converted.
     * @return string resulting punycode.
     * @since 1.1.13
     */
    private function encodeIDN($value)
    {
        if (preg_match_all('/^(.*)@(.*)$/', $value, $matches)) {
            $value = $matches[1][0] . '@' . IDNHelper::encode($matches[2][0]);
        }
        return $value;
    }
}
