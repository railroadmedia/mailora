<?php

namespace Railroad\Mailora\Services;

use App\Mail\General;
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
        $recipientSafety = config('mailora.defaults.safety-recipient');
        $senderAddress = config('mailora.defaults.sender-address');
        $senderName = config('mailora.defaults.sender-name');
        $recipientAddress = config('mailora.defaults.recipient-address');
        $nameOfProductionEnv = config('mailora.defaults.name-of-production-env');

        if(
            empty($recipientSafety) ||
            empty($senderAddress) ||
            empty($senderName) ||
            empty($recipientAddress) ||
            empty($nameOfProductionEnv)
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
        $email = $this->makeEmailObject($input);

        if($email === false){
            return false;
        }

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
     * @param $input array
     * @return Mailable|bool
     */
    private function makeEmailObject($input)
    {
        $email = $this->getEmailInstance($input);

        if($email === false){
            return false;
        }

        $this->setSender($input, $email);
        $this->setRecipient($input, $email);
        $this->setSubject($input, $email);
        $this->setReplyTo($input, $email);

        // if no message defined, make sure email doesn't break
        $input['message'] = !empty($input['message']) ? $input['message'] : '';

        return $email;
    }

    /**
     * @param $input
     *
     * @return Mailable|bool $email
     */
    private function getEmailInstance($input)
    {
        $emailClass = null;
        $customNamespace = $this->getCustomNamespace();
        $customViewsDirectory = $this->getCustomViewsDirectory();
        $type = $this->getEmailType($input);
        $view = $this->getView($customViewsDirectory, $type, $input);
        $emailClass = $this->getEmailClass($customNamespace, $type);

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

    private function setRecipient($input, Mailable &$email)
    {
        // PART 1 - determine value to set as recipient

        // 1.1. default to default
        $recipientAddress = $email->to(config('mailora.defaults.recipient-address'));

        // 1.2 if input provided, use that
        if(!empty($input['recipient-address'])){
            $recipientAddress = $input['recipient-address'];
        }

        // 1.3 if not prod, discard previous and use safety
        $production = app()->environment() === config('mailora.defaults.name-of-production-env');
        if(!$production) {
            $recipientAddress = config('mailora.defaults.safety-recipient');
        }

        // PART 2 - set it

        if (!empty($input['recipient-name'])) {
            $email->to($recipientAddress, $input['recipient-name']);
        }else{ // must use else, or else will set *two* recipients, one with name, one without.
            $email->to($recipientAddress);
        }
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
            if($user && config('mailora.defaults.users-email-set-reply-to')){
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

    private function getCustomViewsDirectory(){
        $customViewsDirectory = config('mailora.views-directory');
        $this->ensureSlashes($customViewsDirectory);
        return base_path() . $customViewsDirectory;
    }

    private function getCustomNamespace(){
        $customNamespace = config('mailora.mailables-namespace');
        $this->ensureSlashes($customNamespace, true);
        return $customNamespace;
    }

    private function getEmailType($input){
        $type = config('mailora.defaults.type');
        if(!empty($input['type'])){
            $type = $input['type'];
        }
        return $type;
    }

    private function getView($customViewsDirectory, $type = 'general', $input)
    {
        $view = base_path() . '/vendor/railroad/mailora/resources/views/general.blade.php';
        if(!file_exists($view)){
            $this->error('package general view file not found at ' . $view);
        }

        // 2.1.3. overwrite view with custom one if provided
        $customPotentialView = $customViewsDirectory . '/' . $type . '.blade.php';
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

    private function getEmailClass($customNamespace, $type = 'general')
    {
        $potentialClass = null;

        // default to native version of Mailable class
        $emailClass = '\Railroad\Mailora\Mail\General';

        if(!class_exists($emailClass)){
            $this->error('package general Mailable class ( ' . $emailClass . ') not found');
        }

        // default to standard namespace
        $namespace = 'App\Mail\\';

        // default to laravel standard namespace
        $emailClass = $namespace . 'General';

        // use custom namespace if provided
        if($customNamespace) {
            $namespace = $customNamespace;
        }

        if($type === 'general'){
            $potentialClass = $namespace . 'General';
        }else{
            $potentialClass = $namespace . $this->dashesToCamelCase($type, true);
        }

        // set to custom if provided
        if(class_exists($potentialClass)){
            $emailClass = $potentialClass;
        }

        if(!$emailClass){
            $this->error('$emailClass ( ' . var_export($emailClass, true) .
                ') was not defined in \Railroad\Mailora\Services\MailService::getEmailInstance');
            return false;
        }

        return $emailClass;
    }
}