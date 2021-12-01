<?php

namespace Railroad\Mailora\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportContact extends Mailable
{
    use Queueable, SerializesModels;

    public $input;
    public $view;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($input, $view)
    {
        $this->input = $input;
        $this->view = $view;

        $this->input = $input;
        if(!empty(current_user())){
            $this->input['studentName'] = current_user()->getDisplayName();
            $this->input['studentEmail'] = current_user()->getEmail();
            $this->input['studentId'] = current_user()->getId();
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view($this->view);
    }
}
