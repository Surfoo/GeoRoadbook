<?php

/**
 * WeasyPrint
 *
 * This class is a slim wrapper around WeasyPrint.
 */
class WeasyPrint {

    protected $bin = '/usr/local/bin/weasyprint';

    protected $input  = null;

    protected $error;

    public $command;
    /**
     * 
    */
    public function __construct($input)
    {
        $this->input = $input;
    }

    /**
     * Save the PDF to given filename (triggers PDF creation)
     *
     * @param string $filename to save PDF as
     * @return bool wether PDF was created successfully
     */
    public function saveAs($filename, $user_stylesheet)
    {
        return $this->createPdf($filename, $user_stylesheet);
    }

    /**
     * @return mixed the detailled error message including the WeasyPrint command or null if none
     */
    public function getError()
    {
        return $this->error;
    }

    protected function createPdf($filename, $user_stylesheet)
    {
        $cmd_user_stylesheet = '';
        if(!empty($user_stylesheet)) {
            $cmd_user_stylesheet = "-s " . escapeshellarg('data:text/css;base64,' . base64_encode($user_stylesheet));
        }
        $this->command = escapeshellarg($this->bin) . ' '. escapeshellarg($this->input).' ' . escapeshellarg($filename) . ' '. $cmd_user_stylesheet;
        // die($this->command);
        // we use proc_open with pipes to fetch error output
        $descriptors = array(
            2   => array('pipe','w'),
        );
        $process = proc_open($this->command , $descriptors, $pipes, null, null, array('bypass_shell'=>true));

        if(is_resource($process)) {

            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            $result = proc_close($process);

            if($result !== 0)
            {
                if (!file_exists($filename) || filesize($filename)===0)
                    $this->error = "Could not run command $this->command :\n$stderr";
                else
                    $this->error = "Warning: an error occured while creating the PDF.\n$stderr";
            }
        } else
            $this->error = "Could not run command $this->command ";

        return $this->error === null;
    }
}