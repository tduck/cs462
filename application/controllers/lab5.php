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

		if (isset($peers[site_url('lab5/receive_message')]))
		{
			$next_msg = $peers[site_url('lab5/receive_message')]["WeHave"] + 1;
		}

		$this->load->view("header");
		$this->load->view("view_messages", 
			array("messages" => $this->get_ordered_messages(), 
				"peers" => $peers,
				"uuid" => $uuid,
				"next_msg" => $next_msg));
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

		echo "Random peer: \n<pre>";
		var_dump($q);
		echo "</pre>";

		$message = $this->prepare_message($q);

		echo "Message: \n<pre>";
		var_dump($message);
		echo "</pre>";

		$url = $q['EndPoint'];

		// $this->send($url, $message);
	}


	public function receive_message()
	{
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Headers: "Origin, X-Requested-With, Content-Type, Accept"');

		if ($post = json_decode(trim(file_get_contents('php://input')), true))
		{
			$json = $this->get_messages();
			$ordered_json = $this->get_ordered_messages();
			$peers = $this->get_peers();

			if (isset($post['Rumor']))
			{
				$message_id = explode(":", $post['Rumor']['MessageID']);
				$sender_uuid = str_replace("-", "", strtolower($message_id[0]));
				$message_index = $message_id[1];

				if (isset($json[$sender_uuid]))
				{
					$json[$sender_uuid][$message_index] = json_encode($post['Rumor']);
				}

				else
				{
					$json[$sender_uuid] = array($message_index => json_encode($post['Rumor']));
				}

				$ordered_json["" . microtime(true)] = json_encode($post['Rumor']);
				
				// Write updated rumor message data back to file
				$fh = fopen("messages.json", 'w') or die("Error opening output file");
				fwrite($fh, json_encode($json));
				fclose($fh);

				$fh = fopen("ordered_messages.json", 'w') or die("Error opening output file");
				fwrite($fh, json_encode($ordered_json));
				fclose($fh);

				// Check the peer who sent the message
				// If this peer is already in our system, update its state
				// We know we have this message, and so do they
				if (isset($peers[$post['EndPoint']]))
				{
					$peers[$post['EndPoint']]['WeHave'] = $message_index;
					$peers[$post['EndPoint']]['TheyHave'][$sender_uuid] = $message_index;
					$peers[$post['EndPoint']]['UUID'] = $sender_uuid;
				}

				// Otherwise it is a new peer and we only have the message we just got from them.
				else
				{
					$peers[$post['EndPoint']] = array('UUID' => $sender_uuid,
						'WeHave' => $message_index, 
						'TheyHave' => array($sender_uuid => $message_index),
						'Originator' => $post['Rumor']['Originator']);
				}

				// Save peer data
				$fh = fopen("peers.json", 'w') or die("Error opening output file");
				fwrite($fh, json_encode($peers));
				fclose($fh);
			}

			else if (isset($post['Want']))
			{
				/*
					{"Want": {"ABCD-1234-ABCD-1234-ABCD-125A": 3,
				              "ABCD-1234-ABCD-1234-ABCD-129B": 5,
				              "ABCD-1234-ABCD-1234-ABCD-123C": 10
				             } ,
				     "EndPoint": "https://example.com/gossip/asff3"
				    }
				*/

				$url = $post['EndPoint'];

				foreach ($post['Want'] as $want_request => $last_msg)
				{
					$formatted = str_replace("-", "", strtolower($want_request));

					// $message = $this->prepare_message(
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
		echo microtime(true) . "\n";
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

			$peer = isset($peers[$formatted]) ? $peers[$formatted] : NULL;

			var_dump($peer);

			$msg = $this->prepare_message($peer);

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
		/*if ( isWant(t) ) {
		    work_queue = addWorkToQueue(t)
		    foreach w work_queue {
		      s = prepareMsg(state, w) -- prepare a message for the "wanted" peer
		      <url> = getUrl(w)	-- message will be sent to the "wanted" peer's URL
		      send(<url>, s)
		      state = update(state, s)
		    }
		}

		The functions can be described as follows:

		getPeer()—selects a neighbor from a list of peers.
		prepareMessage()—return a message to propagate to a specific neighbor; randomly choose message type (rumor or want) and which message.
		update()— update state of who has been send what.
		send()—make HTTP POST to send message*/
	}


	public function prepare_message($peer)
	{
		$msg = "";

		$msg_type = rand(0, 1);

		// If we don't have the peer in our system, we will only
		// send want messages until we get data about them.
		if ($peer == NULL)
			$msg_type == 1;

		if ($msg_type == 0)
		{
			$msg_array = array("Rumor" => array(), "EndPoint" => site_url('lab5/receive_message'));



			$msg = json_encode($msg_array);
			// rumor
		}

		else
		{
			$msg_array = array("Want" => array(), "EndPoint" => site_url('lab5/receive_message'));


			
			$msg = json_encode($msg_array);
			// want
		}

		return $msg;
	}

	public function test_send()
	{
		$url = "http://54.67.54.63/index.php/lab5/test_send_endpoint";
		$message = json_encode(array('test' => "I am testing this"));

		var_dump($this->send($url, $message));
	}


	public function send($endpoint, $message)
	{
		$ch = curl_init($endpoint);

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

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


	public function get_ordered_messages()
	{
		if (!file_exists('ordered_messages.json'))
		{
			file_put_contents('ordered_messages.json', json_encode(array()));
		}

		return json_decode(file_get_contents('ordered_messages.json'), true);
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


	public function get_random_peer()
	{
		$peers = $this->get_peers();

		if ($peers === NULL)
			return NULL;

		$keys = array_keys($this->get_peers());
		$index = rand(0, count($keys) - 1);

		return $peers[$keys[$index]];
	}


	public function lookup_peer($uuid)
	{
		$peers = $this->get_peers();
		var_dump($peers);
	}


	public function add_peer()
	{
		if ($post = $this->input->post())
		{
			// 
			// Peers
			//
			$json = $this->get_peers();

			// If this peer isn't already in our system, add it
			if (!isset($json[$post['url']]))
			{
				$json[$post['url']] = array("Originator" => $post['peer_name'], "WeHave" => "-1");
			}

			// Save peer data
			$fh = fopen("peers.json", 'w') or die("Error opening output file");
			fwrite($fh, json_encode($json));
			fclose($fh);

			// $this->index();
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */