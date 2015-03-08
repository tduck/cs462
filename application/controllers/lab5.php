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

		echo "Random peer: \n<pre>\n";
		var_dump($q);
		echo "</pre>\n";

		$message = $this->prepare_message($q);

		echo "Message: \n<pre>\n";
		var_dump($message);
		echo "</pre>";

		// An empty JSON message means that the peer has everything we have
		// (This prevents infinite loops)
		if ($message != "[]")
		{
			$this->send($q['EndPoint'], $message);
		}

		echo json_encode($this->get_ordered_messages());
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
					// extra check against infinite loops
					if (!isset($json[$sender_uuid][$message_index]))
					{
						$json[$sender_uuid][$message_index] = json_encode($post['Rumor']);
						$ordered_json["" . microtime(true)] = json_encode($post['Rumor']);
					}
				}

				else
				{
					$json[$sender_uuid] = array($message_index => json_encode($post['Rumor']));
					$ordered_json["" . microtime(true)] = json_encode($post['Rumor']);
				}
				
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
				}

				// Otherwise it is a new peer and we only have the message we just got from them.
				else
				{
					$peers[$post['EndPoint']] = array('WeHave' => $message_index, 
						'TheyHave' => array($sender_uuid => $message_index));
				}

				$peers[$post['EndPoint']]['EndPoint'] = $post['EndPoint'];
				$peers[$post['EndPoint']]['UUID'] = $sender_uuid;
				$peers[$post['EndPoint']]['Originator'] = $post['Rumor']['Originator'];

				// Save peer data
				$fh = fopen("peers.json", 'w') or die("Error opening output file");
				fwrite($fh, json_encode($peers));
				fclose($fh);
			}

			else if (isset($post['Want']))
			{
				$this->process_want($post);
			}		
		}
	}


	public function test_want()
	{
		$msg = json_decode(json_encode(array(
            'Want' => array(
                "ABCD-1234-ABCD-1234-ABCD-125A" => 0,
                "ABCD-1234-ABCD-1234-ABCD-129B" => 5,
                "ABCD-1234-ABCD-1234-ABCD-123C" => 10),
            'EndPoint' => 'http://localhost:8080/cs462/index.php/lab5/receive_message')));

		$this->process_want($msg);
	}


	public function process_want($post)
	{
		$example = $post;

		$as_array = array('EndPoint' => $post['EndPoint']);

		foreach ($example['Want'] as $requested_uuid => $last_msg)
		{
			$formatted_uuid = str_replace("-", "", strtolower($requested_uuid));
			$as_array['Want'][$formatted_uuid] = "" . $last_msg;
			unset($example['Want'][$requested_uuid]);
		}

		// Save peer data
		$peers = $this->get_peers();
		$peers[$as_array['EndPoint']]['EndPoint'] = $as_array['EndPoint'];
		$peers[$as_array['EndPoint']]['TheyHave'] = $as_array['Want'];

		$fh = fopen("peers.json", 'w') or die("Error opening output file");
		fwrite($fh, json_encode($peers));
		fclose($fh);

		foreach ($as_array['Want'] as $requested_uuid => $last_msg)
		{
			$peer = $this->lookup_peer($requested_uuid);

			// Only do something if we have data for that peer
			if ($peer != NULL)
			{
				$msg = $this->prepare_message($peer);

				if ($msg != "[]")
				{
					$this->send($peer['EndPoint'], $msg);

					$msg_array = json_decode($msg);

					if (isset($msg_array->Rumor))
					{
						$peer = $peers[$msg_array->EndPoint];

						$just_sent = explode(":", $msg_array->Rumor->MessageID);
						$peer['TheyHave'][$just_sent[0]] = "" . $just_sent[1];

						$peers[$msg_array->EndPoint] = $peer;
					}
				}
			}			
		}

		$fh = fopen("peers.json", 'w') or die("Error opening output file");
		fwrite($fh, json_encode($peers));
		fclose($fh);
	}


	public function prepare_message($peer)
	{
		$msg_array = array();

		$stored_messages = $this->get_messages();

		$msg_type = rand(0, 1);

		// If we don't have the peer's UUID in our system, we will only
		// send want messages until we get their UUID.
		if ($peer == NULL)
			$msg_type == 1;

		// rumor
		// Find the next rumor the peer doesn't have and give it to them.
		// If they have everything we have, this will return an empty array.
		if ($msg_type == 0)
		{			
			foreach ($peer['TheyHave'] as $uuid => $last_received)
			{
				if (isset($stored_messages[$uuid]))
				{
					$uuid_messages = $stored_messages[$uuid];
					if (isset($uuid_messages[intval($last_received) + 1]))
					{
						$to_send = json_decode($uuid_messages[intval($last_received) + 1]);

						$msg_array = array("EndPoint" => site_url('lab5/receive_message'));
						$msg_array['Rumor'] = array("Originator" => $to_send->Originator,
							"Text" => $to_send->Text,
							"MessageID" => $to_send->MessageID);

						return json_encode($msg_array);
					}
				}
			}
		}
			
		// want
		else
		{
			$msg_array = array("Want" => array(), "EndPoint" => site_url('lab5/receive_message'));

			foreach ($stored_messages as $uuid => $uuid_messages)
			{
				krsort($uuid_messages);
				$msg_array["Want"][$uuid] = key($uuid_messages);
			}
		}

		return json_encode($msg_array);
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


	public function ordered_messages_json()
	{
		return json_encode($this->get_ordered_messages());
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

		foreach ($peers as $peer)
		{
			if (isset($peer["UUID"]) && $peer["UUID"] == $uuid)
			{
				return $peer;
			}
		}

		return NULL;
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
				$json[$post['url']] = array("EndPoint" => $post['url'], 
					"Originator" => $post['peer_name'], 
					"WeHave" => "-1",
					"TheyHave" => array($this->get_uuid() => "-1"));
			}
			else if (!isset($json[$post['url']]["Originator"]))
			{
				$json[$post['url']]["Originator"] = $post['peer_name'];
			}

			// Save peer data
			$fh = fopen("peers.json", 'w') or die("Error opening output file");
			fwrite($fh, json_encode($json));
			fclose($fh);

			$this->index();
		}
	}


	public function reset()
	{
		$fileArray = array(
		    "peers.json",
		    "messages.json",
		    "ordered_messages.json"
		);

		foreach ($fileArray as $value) {
		    if (file_exists($value)) {
		        unlink($value);
		    } else {
		        // code when file not found
		    }
		}

		redirect(site_url('lab5'));
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */