<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lab5 extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */

	public function index()
	{
		if (!file_exists('messages.json'))
		{
			$fh = fopen('messages.json', 'a');
			fclose($fh);
		}

		$json = json_decode(file_get_contents('messages.json'), true);

		$this->load->view("header");
		$this->load->view("view_messages", array("messages" => $json));
	}

	public function add_peer()
	{
		if ($post = $this->input->post())
		{

		}

		// $this->index();
	}

	public function receive_message()
	{
		if ($post = $this->input->post())
		{
			/*
			Propagating Rumors
			Each node will run the following message propagation algorithm:

			while true {
			  q = getPeer(state)                    
			  s = prepareMsg(state, q)       
			  <url> = lookup(q)
			  send (<url>, s)                 
			  sleep n
			}
			Each node will also provide an HTTP endpoint that responds to POST of valid messages in the following way:

			t = getMessage();
			if (  isRumor(t)  ) {
			     store(t)
			} elsif ( isWant(t) ) {
			    work_queue = addWorkToQueue(t)
			    foreach w work_queue {
			      s = prepareMsg(state, w)
			      <url> = getUrl(w)
			      send(<url>, s)
			      state = update(state, s)
			    }
			}

			The functions can be described as follows:

			getPeer()—selects a neighbor from a list of peers.
			prepareMessage()—return a message to propagate to a specific neighbor; randomly choose message type (rumor or want) and which message.
			update()— update state of who has been send what.
			send()—make HTTP POST to send message
			*/

			echo json_encode($post);

			// return json_encode($post);
		}
	}

	public function propagate()
	{

	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */