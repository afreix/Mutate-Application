<?php

use Symfony\Component\Process\Process;

/** Handbrake controller
 *
 * @package Slimfra
 * @author Andrew Freix
 */
class Handbrake extends \Slimfra\Controller {

	private $input_filePath = null;
	private $output_file = null;
	
	public function __construct() {
	    //Constructor-----doing work
	}
	
	/**
     * Returns a string with the code calling the handbrake Classic preset
	 */
	public function usePreset($preset) {
	    $this->input_filePath = $this->app['request']->query->get('input');
	    $this->output_file = $this->app['request']->query->get('output');
	    $commandLine = $this->app['handbrake.path']."\HandbrakeCLI -i ".$this->input_filePath." -o ".$this->output_file." --preset='".$preset."'";
		$process = new Process($commandLine);
		$output = "";
		$i = substr($this->input_filePath, 6);
		$o = substr($this->output_file, 7);
		$filename = "errors\\".str_replace('.','_',$i)."_to_".str_replace('.','_', $o).".txt";
		if (file_exists($filename)) {
		    file_put_contents($filename, "");
		}
		$result = $process->run(function ($type, $buffer) use($output, $filename) {
			if ('err' === $type) {
				//echo 'ERR > '.$buffer.'<br>';
				$output = 'ERR > '.$buffer."\n";
			} else {
			    //echo 'OUT > '.$buffer.'<br>';
				$output = 'OUT > '.$buffer."\n";
			}
			file_put_contents($filename, $output, FILE_APPEND);
		});
		echo $result;
	} 
	
    /**
     * Returning a string will automatically create a text response with 200 OK headers.
     */
    public function renderText() {
        return "Andrew Test Number one!1!1!1!1!1!1!";
    }

    /**
     * Returning a rendered template will automatically create a 200 OK html response.
     */
    public function renderHtml($name = "World") {
        return $this['twig']->render('hello.html.twig', array(
            'name' => $name,
            'title' => "Welcome!",
            'app_name' => $this['app_name'],
        ));
    }
    
    /**
     * Return a JSON object response.  Will automatically create a 200 OK application/json response.
     */
    public function renderJson($name = "World") {
        return $this->app->json(array('name' => $name));
    }
    
    /**
     * You can always build the response object yourself, to have complete control!
     */
    public function customXmlResponse($name) {
        $response = new \Symfony\Component\HttpFoundation\Response("<root><name>$name</name></root>", 203);
        $response->setPublic();
        $response->setMaxAge(100);
        $response->headers->set('content-type', 'application/xml');
        
        return $response;
    }
}

