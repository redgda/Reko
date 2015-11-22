<?php

class Reko
{
    protected $data;
    protected $mailer;
    protected $min_potential;

    public function __construct(PHPMailer $mailer, $data_dir='data')
    {
        $this->mailer = $mailer;
        $this->data_dir = __DIR__ . '/' . $data_dir;
    }

    public function load($data)
    {
        $this->data = $data;
    }

    public function set_min_potential($value)
    {
        $this->min_potential = $value;
    }

    public function notify($emails)
    {
        $emails = explode(',', $emails);
        if (!count($emails))
        {
            Throw new InvalidArgumentException("At least one email address is required");
        }

        foreach ($this->data as $i)
        {
            $potential = floatval((str_replace(',', '.', $i['potential'])));
            if ($potential > $this->min_potential)
            {
                $to_send[] = $i;
            }
        }

        if ($to_send)
        {
            $content = '';
            $subject_names = '';

            foreach ($to_send as $i)
            {
                //@todo check if this recomendations has been sent eariler
                $content .= implode($i, ' ') . "\n";
                $subject_names .= "{$i['name']} ";
            }

            $subject = "Nowe rekomendacje z ATTrader.pl: $subject_names";

            foreach ($emails as $email) 
            {
                $this->send($email, $subject, $content);
            }

            return count($to_send);
        }

    }

    public function send($email, $subject, $content)
    {
        // @todo check if it is cleared after each send()
        $this->mailer->AddAddress($email);
        $this->mailer->Subject = $subject;
        $this->mailer->Body = $content;
        $sent = $this->mailer->Send();
    }
}
