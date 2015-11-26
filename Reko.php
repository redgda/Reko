<?php

class Reko
{
    protected $data;
    protected $mailer;
    protected $min_potential;

    //repo:
    protected $index = array();

    public function __construct(PHPMailer $mailer, $data_dir='data')
    {
        $this->mailer = $mailer;
        $this->data_dir = __DIR__ . '/' . $data_dir;
        //@todo inject repository
        $this->history_file = $this->data_dir . '/history.dat';
        $this->load_history();
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

        $to_send = [];
        foreach ($this->data as $i)
        {
            $potential = floatval((str_replace(',', '.', $i['potential'])));
            if ($potential > $this->min_potential && $this->is_new($i))
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
                $this->save_in_history($i);
                $content .= implode($i, ' ') . "\n";
                $subject_names .= "{$i['name']} ";
            }

            $subject = "Nowe rekomendacje z ATTrader.pl: $subject_names";

            foreach ($emails as $email)
            {
                $this->send($email, $subject, $content);
            }

        }

        return count($to_send);
    }

    protected function is_new($record)
    {
        $key = md5(json_encode($record));
        return !in_array($key, $this->index);
    }

    protected function save_in_history($record)
    {
        $line = json_encode($record) . "\n";
        file_put_contents($this->history_file, $line, FILE_APPEND);
    }

    protected function load_history()
    {
        if (!file_exists($this->history_file))
        {
            return;
        }

        $lines = file($this->history_file, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line)
        {
            $this->index[] = md5($line);
        }
    }

    protected function send($email, $subject, $content)
    {
        // @todo check if it is cleared after each send()
        $this->mailer->AddAddress($email);
        $this->mailer->Subject = $subject;
        $this->mailer->Body = $content;
        $sent = $this->mailer->Send();
    }
}
