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
			$next_msg = $peers[$uuid]["WeHave"] + 1;
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


	public function propagate()
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
		}*/
		$q = $this->get_random_peer();
	}


	public function receive_message()
	{
		if ($post = json_decode(trim(file_get_contents('php://input')), true))
		{
			$json = $this->get_messages();
			$peers = $this->get_peers();

			if (isset($post['Rumor']))
			{
				// 
				//  Save a Rumor message
				//
				
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
				$get_uuid = explode(":", $post['Rumor']['MessageID']);
				$uuid = $get_uuid[0];

				// If this peer is already in our system, increment its state
				if (isset($peers[$uuid]))
				{
					$peers[$uuid]['WeHave'] = $get_uuid[1];
				}

				// Otherwise it is a new peer and we only have the latest 
				else
				{
					$peers[$uuid] = array('WeHave' => $get_uuid[1], 'EndPoint' => $post['EndPoint']);
				}

				// Save peer data
				$fh = fopen("peers.json", 'w') or die("Error opening output file");
				fwrite($fh, json_encode($peers));
				fclose($fh);
			}

			else if (isset($post['Want']))
			{
				var_dump("Want");
				/*
					{"Want": {"ABCD-1234-ABCD-1234-ABCD-125A": 3,
				              "ABCD-1234-ABCD-1234-ABCD-129B": 5,
				              "ABCD-1234-ABCD-1234-ABCD-123C": 10
				             } ,
				     "EndPoint": "https://example.com/gossip/asff3"
				    }
				*/

				foreach ($post['Want'] as $want_request => $last_msg)
				{

				}
			}
		}


		/*
		
		Each node will also provide an HTTP endpoint that responds to POST of valid messages in the following way:

		elsif ( isWant(t) ) {
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
	}


	public function test_want()
	{
		$msgs = $this->get_messages();
		$peers = $this->get_peers();

		$example = array(
			'Want' => array(
				"ABCD-1234-ABCD-1234-ABCD-125A" => 0,
				"ABCD-1234-ABCD-1234-ABCD-129B" => 5,
				"ABCD-1234-ABCD-1234-ABCD-123C" => 10),
			'EndPoint' => 'www.lds.org');

		foreach ($example['Want'] as $want_request => $last_msg)
		{
			$formatted = str_replace("-", "", strtolower($want_request));

			// Only do something if we have data for that peer
			if (isset($peers[$formatted]) && $peers[$formatted]['WeHave'] > $last_msg)
			{
				$endpoint = $peers[$formatted]['EndPoint'];
				// var_dump($we_have . " - " . $last_msg);

				// Check all messages...
				foreach ($msgs as $message)
				{
					$msg_id = explode(":", json_decode($message)->MessageID);

					// If the message UUID is the one we want, and the sequence count is greater 
					// than what the requester already has.
					if ($msg_id[0] == $formatted && $msg_id[1] > $last_msg)
					{
						// Send the messages to the provided endpoint
						var_dump($msg_id);
					}
				}				
			}
		}
	}


	public function get_random_peer()
	{
		$peers = $this->get_peers();

		if ($peers === NULL)
			return NULL;

		$keys = array_keys($this->get_peers());
		$index = rand(0, count($keys) - 1);

		return array('uuid' => $keys[$index], 'EndPoint' => $peers[$keys[$index]]['EndPoint']);
	}

	public function prepare_message()
	{
		$msg_type = rand(0, 1);
		if ($msg_type == 0)
		{
			// rumor
		}

		else
		{
			// want
		}
	}

	public function test_send()
	{
		$url = "";
		$message = json_encode(array('test' => "I am testing this"));

		var_dump($this->send($url, $message));
	}

	public function send($endpoint, $message)
	{
		$ch = curl_init($endpoint);

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
	}

	public function test_send_endpoint()
	{
		if ($post = json_decode(trim(file_get_contents('php://input')), true))
		{
			var_dump($post);
		}

		else echo "Didn't decode JSON";
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */