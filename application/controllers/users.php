<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users extends CI_Controller {

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

	public function index($username = '', $start_session = TRUE)
	{
		$url = "https://foursquare.com/oauth2/authorize".
    	"?client_id=E0U33FDWASV50DS3R3DMAIRZF5OFL4UMWY3315JAMG0QHWDZ".
    	"&response_type=code&redirect_uri=http://54.67.35.44/index.php/users/oauth";

    	redirect($url);
	}

	public function create()
	{
		$data = array('post' => array()); 
		if ($post = $this->input->post())
		{
			$data['post'] = $post;
			$json = json_decode(file_get_contents('users.json'), true);

			if (isset($json[$post['username']]))
			{
				$data['message'] = 'Username already taken.';
			}

			else
			{
				$username = $post['username'];
				unset($post['username']);
				$json[$username] = $post;

				$fh = fopen("users.json", 'w')
				      or die("Error opening output file");
				fwrite($fh, json_encode($json));
				fclose($fh);

				$data['message'] = 'Account created successfully.';
			}
		}
		$this->load->view('header');
		$this->load->view('create_user', $data);
	}

	public function login()
	{
		if ($post = $this->input->post())
		{
			$data['username'] = $post['username'];
			$data['start_session'] = FALSE;

			$json = json_decode(file_get_contents('users.json'), true);
			if (isset($json[$post['username']]))
			{
				session_start();
				$_SESSION['username'] = $post['username'];
				$this->index($post['username']);
			}

			else
			{		
				$this->load->view('header');
				$this->load->view('login', array('message' => "Incorrect login information."));
			}			
		}

		else
		{
			$this->load->view('header');
			$this->load->view('login');
		}
	}

	public function logout()
	{
		session_start();
		unset($_SESSION['username']);
		unset($_SESSION['access_token']);
		session_destroy();
		$this->login();
	}

	public function oauth()
	{
		if ($get = $this->input->get())
		{
			if ($get['code'])
			{
				$url = "https://foursquare.com/oauth2/access_token".
				    "?client_id=E0U33FDWASV50DS3R3DMAIRZF5OFL4UMWY3315JAMG0QHWDZ".
				    "&client_secret=C0I1QMMR0S3COWUMOQGRPJB0PQYRGZAGTUBEQJKKQGU24QBE".
				    "&grant_type=authorization_code".
				    "&redirect_uri=http://54.67.35.44/index.php/users/oauth".
				    "&code=" . $get['code'];

				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$exec = curl_exec($ch);

				$access_token = json_decode($exec);
				session_start();

				$_SESSION['access_token'] = $access_token->access_token;

				// var_dump($_SESSION);

				$this->profile($_SESSION['username'], FALSE);
			}
		}

		else {
			redirect('users/login');
		}
	}

	public function profile($username = '', $start_session = TRUE)
	{
		date_default_timezone_set("America/Denver");
		if ($start_session === TRUE)
		{
			session_start();
			$start_session = FALSE;
		}
		
		$json = json_decode(file_get_contents('users.json'), true);
		$user_data = $json[$username];
		// var_dump($user_data);
		$data = array('username' => $username, 'user_data' => $user_data, 'start_session' => $start_session);

		if (isset($_SESSION['username']) && $username === $_SESSION['username'])
		{
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, 
				"https://api.foursquare.com/v2/users/self/checkins?oauth_token=" 
					. $_SESSION['access_token'] . "&v=20140806&m=foursquare");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			$response = json_decode(curl_exec($ch))->response;

			$data['count'] = $response->checkins->count;
			$data['checkins'] = json_decode(json_encode($response->checkins->items));

			if (count($data['checkins']) > 0)
			{
				$most_recent_checkin = $data['checkins'][0];
				$user_data['most_recent_checkin'] = $most_recent_checkin;
				$json[$username] = $user_data;

				// echo "<pre>";
				// var_dump($data['checkins']);
				// echo "</pre>";

				$fh = fopen("users.json", 'w')
				      or die("Error opening output file");
				fwrite($fh, json_encode($json));
				fclose($fh);
			}
		}

		else
		{
			$ch = curl_init();

			if (isset($user_data['most_recent_checkin']))
			{
				$data['most_recent'] = $user_data['most_recent_checkin'];
			}
		}

		$this->load->view("profile", $data);
	}

	public function authenticate()
	{

	}

	public function receive_push()
	{

	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
