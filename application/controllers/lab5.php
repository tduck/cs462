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
		$uuid = $this->get_uuid();
		$peers = $this->get_peers();
		$next_msg = 0;

		if (isset($peers[$uuid]))
		{
			$next_msg = $peers[$uuid]["State"] + 1;
		}

		$this->load->view("header");
		$this->load->view("view_messages", 
			array("messages" => $this->get_messages(), 
				"peers" => $peers,
				"uuid" => $uuid,
				"next_msg" => $next_msg));
	}


	public function add_peer()
	{
		if ($post = $this->input->post())
		{
			var_dump($post);
		}

		// 
		// Peers
		//
		$json = $this->get_peers();

		$get_uuid = explode(":", $post['Rumor']['MessageID']);
		$uuid = $get_uuid[0];

		// If this peer is already in our system, increment its state
		if (!isset($json[$uuid]))
		{
			// $json[$uuid] = array('State' => 0, 'EndPoint' => $post['EndPoint']);
		}

		// Save peer data
		$fh = fopen("peers.json", 'w') or die("Error opening output file");
		fwrite($fh, json_encode($json));
		fclose($fh);

		// $this->index();
	}


	public function get_uuid()
	{
		if (!file_exists('uuid.txt'))
		{
			$uuid = uniqid();
			file_put_contents('uuid.txt', $uuid);
			return $uuid;
		}

		else
		{
			return file_get_contents('uuid.txt');
		}
	}


	public function get_messages()
	{
		if (!file_exists('messages.json'))
		{
			file_put_contents('messages.json', json_encode(array()));
		}

		return json_decode(file_get_contents('messages.json'), true);
	}


	public function get_peers()
	{
		if (!file_exists('peers.json'))
		{
			file_put_contents('peers.json', json_encode(array()));
		}

		$peers = json_decode(file_get_contents('peers.json'), true);
		return $peers;
	}


	public function receive_message()
	{
		if ($post = json_decode(trim(file_get_contents('php://input')), true))
		{
			if (isset($post['Rumor']))
			{
				// 
				//  Save a Rumor message
				//
				$json = $this->get_messages();

				if ($json === NULL)
					$json = array();

				// Add message to collection
				$json[] = json_encode($post['Rumor']);
				

				/* {"Rumor" : {"MessageID": "ABCD-1234-ABCD-1234-ABCD-1234:5" ,
				                "Originator": "Phil",
				                "Text": "Hello World!"
				                },
				     "EndPoint": "https://example.com/gossip/13244"
				    }
				*/

				// Write updated rumor message data back to file
				$fh = fopen("messages.json", 'w') or die("Error opening output file");
				fwrite($fh, json_encode($json));
				fclose($fh);


				// 
				// Peers
				//
				$json = $this->get_peers();

				$get_uuid = explode(":", $post['Rumor']['MessageID']);
				$uuid = $get_uuid[0];

				// If this peer is already in our system, increment its state
				if (isset($json[$uuid]))
				{
					$json[$uuid]['State'] = $get_uuid[1];
				}

				// Otherwise it is a new peer with state 0
				else
				{
					$json[$uuid] = array('State' => 0, 'EndPoint' => $post['EndPoint']);
				}

				// Save peer data
				$fh = fopen("peers.json", 'w') or die("Error opening output file");
				fwrite($fh, json_encode($json));
				fclose($fh);
			}

			else if (isset($post['Want']))
			{
				var_dump("Want");
			}
		}


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
		// return json_encode($post);
	}


	public function lookup_url($uuid)
	{
		// $peers = $this->get_peers();
		// if (!isset($peers[$uuid]))
		// 	return null;

		// else return $peers[$uuid]['EndPoint'];
	}

	public function propagate()
	{

	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */