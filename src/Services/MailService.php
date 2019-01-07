<?php

namespace Railroad\Mailora\Services;

use Exception;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * @param bool $public
     * @throws Exception
     */
    private function ensureConfigSet($public = false)
    {
        $recipientSafety = config('mailora.safety-recipient');
        $senderAddress = config('mailora.defaults.sender-address');
        $recipientAddress = config('mailora.defaults.recipient-address');
        $nameOfProductionEnv = config('mailora.name-of-production-env');
        $approvedRecipients = config('mailora.approved-recipients');
        $approvedRecipientDomains = config('mailora.approved-recipient-domains');

        $failPublic = false;

        if ($public) {
            $failPublic = (empty($approvedRecipients) && empty($approvedRecipientDomains)); // only one of these required
        }

        if (
            empty($recipientSafety) ||
            empty($senderAddress) ||
            empty($recipientAddress) ||
            empty($nameOfProductionEnv) ||
            $failPublic
        ) {
            $notSet = [];
            if (empty($recipientSafety)) {
                $notSet[] = 'safety-recipient';
            }
            if (empty($senderAddress)) {
                $notSet[] = 'sender-address';
            }
            if (empty($recipientAddress)) {
                $notSet[] = 'recipient-address';
            }
            if (empty($nameOfProductionEnv)) {
                $notSet[] = 'name-of-production-env';
            }
            if ($failPublic) {
                if ((empty($approvedRecipients) && empty($approvedRecipientDomains))) {
                    if (empty($approvedRecipients)) {
                        $notSet[] = 'approved-recipients';
                    }
                    if (empty($approvedRecipientDomains)) {
                        $notSet[] = 'approved-recipient-domains';
                    }
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
     * @return bool|Exception
     * @throws Exception
     */
    public function sendPublic($input, $returnExceptionObjectOnFailure = false)
    {
        $this->ensureConfigSet(true);
        $email = $this->getMailable($input);

        if ($email === false) {
            return false;
        }

        $this->setSender($input, $email);
        if (!$this->checkAndSetRecipient($input, $email)) {
            $this->error('Unauthorized recipient attempted. ($input: ' . json_encode($input) . ' )');
            return false;
        };
        $this->setSubject($input, $email);
        $this->setReplyTo($input, $email);

        // if no message defined, make sure email doesn't break
        $input['message'] = !empty($input['message']) ? $input['message'] : '';

        try {
            Mail::send($email);
        } catch (Exception $exception) {
            $this->error(
                'Email failed with message: "' . $exception->getMessage() . '". Email input ' .
                '(passed through json_encode): "' . json_encode($input) . '"'
            );
            return $returnExceptionObjectOnFailure ? $exception : false;
        }
        return true;
    }

    /**
     * @param $input
     * @param bool $returnExceptionObjectOnFailure
     * @return bool|Exception
     * @throws Exception
     */
    public function sendSecure($input, $returnExceptionObjectOnFailure = false)
    {
        $this->ensureConfigSet();
        $email = $this->getMailable($input);

        if ($email === false) {
            return false;
        }

        $this->setSender($input, $email);
        if (!$this->checkAndSetRecipient($input, $email, false)) {
            $this->error('Unauthorized recipient attempted. ($input: ' . json_encode($input) . ' )');
            return false;
        };
        $this->setSubject($input, $email);
        $this->setReplyTo($input, $email);

        // if no message defined, make sure email doesn't break
        $input['message'] = !empty($input['message']) ? $input['message'] : '';

        try {
            Mail::send($email);
        } catch (Exception $exception) {
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

        if (!$emailClass || !$view) {
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

            if (!empty($input['sender-name'])) {
                $senderName = $input['sender-name'];
            }
        }

        $email->from($senderAddress, $senderName);
    }

    private function checkAndSetRecipient($input, Mailable &$email, $public = true)
    {
        $approvedRecipients = config('mailora.approved-recipients');
        $approvedRecipientDomains = config('mailora.approved-recipient-domains');

        // PART 1 - determine value to set as recipient

        // 1.1. default to default
        $recipientAddress = config('mailora.defaults.recipient-address');
        $recipientName = config('mailora.defaults.recipient-name');

        // 1.2 if input provided, use that
        if (!empty($input['recipient-address'])) {
            $recipientAddress = $input['recipient-address'];
            $recipientName = null; // unset because do not want to use default name with request-provided address
            if (!empty($input['recipient-name'])) {
                $recipientName = $input['recipient-name'];
            }
        }

        // 1.3 if not prod, discard previous and use safety
        $production = app()->environment() === config('mailora.name-of-production-env');
        if (!$production) {
            $recipientAddress = config('mailora.safety-recipient');
            $recipientName = null; // unset because do not want to incorrectly add name to email
        }

        // PART 2 - set it

        // 2.1 ensure allowed

        $approved = true;

        if ($public) {
            $approved = false;

            // part 1 of 2 - first check for approved domains
            if (!empty($approvedRecipientDomains)) {
                foreach ($approvedRecipientDomains as $approvedRecipientDomain) {
                    $regexPattern = '^[A-Za-z0-9._%+-]+@' . $approvedRecipientDomain . '$^';
                    $match = preg_match($regexPattern, $recipientAddress);
                    if ($match) {
                        $approved = true;
                    }
                }
            }

            // part 2 of 2 - if still no approval after trying the approved domains, then check for specific approved addresses
            if (!$approved) {
                foreach ($approvedRecipients as $approvedRecipient) {
                    if ($recipientAddress === $approvedRecipient) {
                        $approved = true;
                    }
                }
            }
        }

        if (!$approved) {
            return false;
        }

        // 2.2 set it if allowed

        if ($recipientName) {
            $email->to([$recipientAddress, $recipientName]);
        } else { // must use *else*, or else will set *two* recipients, one with name, one without.
            $email->to([$recipientAddress]);
        }

        return true;
    }

    private function setSubject($input, Mailable &$email)
    {
        $subject = 'General Inquiry - Subject not specified';

        if (config('mailora.defaults.subject')) {
            $subject = config('mailora.defaults.subject');
        }

        if (!empty($input['subject'])) {
            $subject = $input['subject'];
        }

        $email->subject($subject);
    }

    private function setReplyTo($input, Mailable &$email)
    {
        if (!empty($input['reply-to'])) {
            $email->replyTo($input['reply-to']);
        } else {
            $user = auth()->user();

            $requestDoesNotSpecify = true;
            $requestSaysToAllow = null;

            if (!empty($input['users-email-set-reply-to'])) {
                $requestSaysToAllow = $input['users-email-set-reply-to'] === 1;
                $requestDoesNotSpecify = false;
            }

            if ($requestDoesNotSpecify === true) {
                $setUserAsReplyTo = config('mailora.defaults.users-email-set-reply-to');
            } else {
                $setUserAsReplyTo = $requestSaysToAllow;
            }

            if ($user && $setUserAsReplyTo) {
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

    private function error($message)
    {
        error_log($message);

        if (config('mailora.admin')) {
            $adminEmailAddressToSendMessageTo = config('mailora.admin');
            // todo: send email with $message
        }
    }

    private function ensureSlashes(&$string, $backslashes = false, $omitFirstSlash = false, $omitLastSlash = false)
    {
        $slash = $backslashes ? '\\' : '/';

        if ($string) {
            if (!$omitFirstSlash) {
                $startsWithForwardSlash = substr($string, 0, 1) === $slash;
                if (!$startsWithForwardSlash) {
                    $string = $slash . $string;
                }
            }
            if (!$omitLastSlash) {
                $endsWithForwardSlash = substr($string, -1) === $slash;
                if (!$endsWithForwardSlash) {
                    $string = $string . $slash;
                }
            }
        }
    }

    private function getEmailType($input)
    {
        $type = config('mailora.defaults.type') ?? 'general';
        if (!empty($input['type'])) {
            $type = $input['type'];
        }
        return $type;
    }

    private function getView($type, $input)
    {
        $view = 'mailora::general';

        $viewsRootDir = config('mailora.views-root-directory') ?? '/resources/views/';
        $viewsEmailDir = config('mailora.views-email-directory') ?? 'emails';

        $this->ensureSlashes($viewsRootDir);
        $this->ensureSlashes($viewsEmailDir, false, true);

        $customPotentialViewPathTruncated = $viewsEmailDir . $type;
        $customPotentialViewPathFull = base_path() . $viewsRootDir . $customPotentialViewPathTruncated . '.blade.php';

        if (file_exists($customPotentialViewPathFull)) {
            $view = $customPotentialViewPathTruncated;
        } else {
            if ($type !== 'general') {
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

        if (!class_exists($emailClass)) {
            $this->error('package general Mailable class ( ' . $emailClass . ') not found');
        }

        // get name of class to look for
        $potentialNamespace = config('mailora.mailables-namespace');
        $this->ensureSlashes($customNamespace, true);
        $potentialClass = $potentialNamespace . $this->dashesToCamelCase($type, true);

        // override default with custom if it exists
        if (class_exists($potentialClass)) {
            $emailClass = $potentialClass;
        }

        if (!$emailClass) {
            $this->error(
                '$emailClass ( ' . var_export($emailClass, true) .
                ') was not defined in \Railroad\Mailora\Services\MailService::getMailable'
            );
            return false;
        }

        return $emailClass;
    }
}