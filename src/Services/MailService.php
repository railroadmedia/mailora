<?php

namespace Railroad\Mailora\Services;

use Exception;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * MailService constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->ensureConfigSet();
    }

    /**
     * @throws Exception
     */
    private function ensureConfigSet(){
        $recipientSafety = config('mailora.safety-recipient');
        $senderAddress = config('mailora.defaults.sender-address');
        $senderName = config('mailora.defaults.sender-name');
        $recipientAddress = config('mailora.defaults.recipient-address');
        $nameOfProductionEnv = config('mailora.name-of-production-env');
        $approvedRecipients = config('mailora.approved-recipients');
        $approvedRecipientDomains = config('mailora.approved-recipient-domains');

        if(
            empty($recipientSafety) ||
            empty($senderAddress) ||
            empty($senderName) ||
            empty($recipientAddress) ||
            empty($nameOfProductionEnv) ||
            (empty($approvedRecipients) && empty($approvedRecipientDomains)) // only one of these required
        ){
            $notSet = [];

            if(empty($recipientSafety)){
                $notSet[] = 'safety-recipient';
            }
            if(empty($senderAddress)){
                $notSet[] = 'sender-address';
            }
            if(empty($senderName)){
                $notSet[] = 'sender-name';
            }
            if(empty($recipientAddress)){
                $notSet[] = 'recipient-address';
            }
            if(empty($nameOfProductionEnv)){
                $notSet[] = 'name-of-production-env';
            }
            if((empty($approvedRecipients) && empty($approvedRecipientDomains))){
                if(empty($approvedRecipients)){
                    $notSet[] = 'approved-recipients';
                }
                if(empty($approvedRecipientDomains)){
                    $notSet[] = 'approved-recipient-domains';
                }
            }
            throw new Exception(
                'Required Mailora config (mailora.defaults) values not set (' . implode(', ', $notSet) . ')'
            );
        }
    }

    /**
     * @param $input array
     * @param $returnExceptionObjectOnFailure null|bool
     *
     * @return bool|Exception
     */
    public function send($input, $returnExceptionObjectOnFailure = false){
        $email = $this->getMailable($input);

        if($email === false){
            return false;
        }

        $this->setSender($input, $email);
        if(!$this->checkAndSetRecipient($input, $email)){
            $this->error('Unauthorized recipient attempted. ($input: ' . json_encode($input) . ' )');
            return false;
        };
        $this->setSubject($input, $email);
        $this->setReplyTo($input, $email);

        // if no message defined, make sure email doesn't break
        $input['message'] = !empty($input['message']) ? $input['message'] : '';

        try{
            Mail::send($email);
        }catch(Exception $exception){
            $this->error(
                'Email failed with message: "' . $exception->getMessage() . '". Email input ' .
                '(passed through json_encode): "' . json_encode($input) . '"'
            );
            return $returnExceptionObjectOnFailure ? $exception : false;
        }
        return true;
    }

    // -----------------------------------------------------------

    /**
     * @param $input
     *
     * @return Mailable|bool $email
     */
    private function getMailable($input)
    {
        $emailClass = null;
        $type = $this->getEmailType($input);
        $view = $this->getView($type, $input);
        $emailClass = $this->getEmailClass($type);

        if(!$emailClass || !$view){
            return false;
        }

        return new $emailClass($input, $view);
    }

    private function setSender($input, Mailable &$email)
    {
        $senderAddress = config('mailora.defaults.sender-address');
        $senderName = config('mailora.defaults.sender-name');

        if (!empty($input['sender-address'])) {
            $senderAddress = $input['sender-address'];
            $senderName = null;

            if(!empty($input['sender-name'])){
                $senderName = $input['sender-name'];
            }
        }

        $email->from($senderAddress, $senderName);
    }

    private function checkAndSetRecipient($input, Mailable &$email)
    {
        $approvedRecipients = config('mailora.approved-recipients');
        if($approvedRecipients){
            $approvedRecipients = explode(' ', $approvedRecipients);
        }
        $approvedRecipientDomains = config('mailora.approved-recipient-domains');
        if($approvedRecipientDomains){
            $approvedRecipientDomains = explode(' ', $approvedRecipientDomains);
        }

        // PART 1 - determine value to set as recipient

        // 1.1. default to default
        $recipientAddress = config('mailora.defaults.recipient-address');
        $recipientName = config('mailora.defaults.recipient-name');

        // 1.2 if input provided, use that
        if(!empty($input['recipient-address'])){
            $recipientAddress = $input['recipient-address'];
            $recipientName = null; // unset because do not want to use default name with request-provided address
            if(!empty($input['recipient-name'])){
                $recipientName = $input['recipient-name'];
            }
        }

        // 1.3 if not prod, discard previous and use safety
        $production = app()->environment() === config('mailora.name-of-production-env');
        if(!$production) {
            $recipientAddress = config('mailora.safety-recipient');
            $recipientName = null; // unset because do not want to incorrectly add name to email
        }

        // PART 2 - set it

        // 2.1 ensure allowed

        $approved = false;

        if(!empty($approvedRecipientDomains)){
            foreach($approvedRecipientDomains as $approvedRecipientDomain){
                $regexPattern = '^[A-Za-z0-9._%+-]+@' . $approvedRecipientDomain . '$';
                $match = preg_match($regexPattern, $recipientAddress);
                if($match){
                    $approved = true;
                }
            }
        }

        if(!$approved){
            foreach($approvedRecipients as $approvedRecipient){
                if($recipientAddress === $approvedRecipient){
                    $approved = true;
                }
            }
        }

        if(!$approved){
            return false;
        }

        // 2.2 set it if allowed

        if ($recipientName) {
            $email->to($recipientAddress, $recipientName);
        }else{ // must use *else*, or else will set *two* recipients, one with name, one without.
            $email->to($recipientAddress);
        }

        return true;
    }

    private function setSubject($input, Mailable &$email){
        $subject = 'General Inquiry - Subject not specified';

        if(config('mailora.defaults.subject')){
            $subject = config('mailora.defaults.subject');
        }

        if(!empty($input['subject'])){
            $subject = $input['subject'];
        }

        $email->subject($subject);
    }

    private function setReplyTo($input, Mailable &$email){
        if(!empty($input['reply-to'])){
            $email->replyTo($input['reply-to']);
        }else{
            $user = auth()->user();

            $requestDoesNotSpecify = true;
            $requestSaysToAllow = null;

            if(!empty($input['users-email-set-reply-to'])){
                $requestSaysToAllow = $input['users-email-set-reply-to'] === 1;
                $requestDoesNotSpecify = false;
            }

            if($requestDoesNotSpecify === true){
                $setUserAsReplyTo = config('mailora.defaults.users-email-set-reply-to');
            }else{
                $setUserAsReplyTo = $requestSaysToAllow;
            }

            if($user && $setUserAsReplyTo){
                $email->replyTo($user->email);
            }
        }
    }

    private function dashesToCamelCase($string, $capitalizeFirstCharacter = false)
    {
        $str = str_replace('-', '', ucwords($string, '-'));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }

    private function error($message){
        error_log($message);

        if(config('mailora.admin')){
            $adminEmailAddressToSendMessageTo = config('mailora.admin');
            // todo: send email with $message
        }
    }

    private function ensureSlashes(&$string, $backslashes = false){
        $slash = $backslashes ? '\\' : '/';

        if($string){
            $startsWithForwardSlash = substr($string, 0, 1) === $slash;
            if(!$startsWithForwardSlash){
                $string = $slash . $string;
            }
            $endsWithForwardSlash = substr($string, -1) === $slash;
            if(!$endsWithForwardSlash){
                $string = $string . $slash;
            }
        }
    }

    private function getEmailType($input){
        $type = config('mailora.defaults.type') ?? 'general';
        if(!empty($input['type'])){
            $type = $input['type'];
        }
        return $type;
    }

    private function getView($type, $input)
    {
        $view = 'mailora::general';
        if(!file_exists($view)){
            $this->error('package general view file not found at ' . $view);
        }

        $customViewsDirectory = config('mailora.views-directory') ?? '/resources/views/emails';
        $this->ensureSlashes($customViewsDirectory);
        $customPotentialView = base_path() . $customViewsDirectory . '/' . $type . '.blade.php';

        if (file_exists($customPotentialView)) {
            $view = $customPotentialView;
        }else{
            if($type !== 'general'){
                $message = 'Custom type specified does have corresponding custom view. Email not sent. ';
                $message .= json_encode($input);
                $this->error($message);
                return false;
            }
        }

        return $view;
    }

    private function getEmailClass($type)
    {
        $potentialClass = null;

        // default to native version of Mailable class
        $emailClass = '\Railroad\Mailora\Mail\General';

        if(!class_exists($emailClass)){
            $this->error('package general Mailable class ( ' . $emailClass . ') not found');
        }

        // get name of class to look for
        $potentialNamespace = config('mailora.mailables-namespace');
        $this->ensureSlashes($customNamespace, true);
        $potentialClass = $potentialNamespace . $this->dashesToCamelCase($type, true);

        // override default with custom if it exists
        if(class_exists($potentialClass)){
            $emailClass = $potentialClass;
        }

        if(!$emailClass){
            $this->error('$emailClass ( ' . var_export($emailClass, true) .
                ') was not defined in \Railroad\Mailora\Services\MailService::getMailable');
            return false;
        }

        return $emailClass;
    }
}