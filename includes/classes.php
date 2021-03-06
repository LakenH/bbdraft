<?php
session_start([
    'cookie_lifetime' => 86400,
]);

//REMOVE BELOW IN PPRODUCTION
ini_set('display_errors', true);
error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
//REMOVE ABOVE IN PRODUCTION

if (isset($_SESSION["name"]) && isset($_SESSION["id"])) {
	$name = $_SESSION["name"];
	$id = $_SESSION["id"];
} else {
	$name = null;
	$id = null;
}
class RedditAuth {
	private $client;
	private $redirectUrl;
	private $authorizeUrl;
	private $accessTokenUrl;
	private $connection;
	private $mysqli;
	
	public function __construct() {
		require_once("includes/OAuth2/Client.php");
		require_once("includes/OAuth2/GrantType/IGrantType.php");
		require_once("includes/OAuth2/GrantType/AuthorizationCode.php");
		include_once "/var/www/secure_includes/data.php";
		include_once "includes/connection.php";
    
		$this->client = new OAuth2\Client($redditClient, $redditClientSecret, OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
		$this->client->setCurlOption(CURLOPT_USERAGENT,$userAgent);
		$this->redirectUrl = $redditRedirectUrl;
		$this->accessTokenUrl = $redditAccessTokenUrl;
		$this->authorizeUrl = $authorizeUrl;
		
		$this->connection = new Connection();
		$this->mysqli = $this->connection->createConnection();
	}
	public function getUrl() {
		$_SESSION["state"] = substr(md5(rand()), 0, 7);
		$authUrl = $this->client->getAuthenticationUrl($this->authorizeUrl, $this->redirectUrl, array("scope" => "identity", "duration" => "permanent", "state" => $_SESSION["state"]));
		return $authUrl;
	}
	public function authorizeUser() {
		if (!isset($_GET["code"])) {
			$authUrl = $this->getUrl();
			echo '<script>window.location.replace("' . $authUrl . '");</script>';
		} elseif ($_GET["state"] == $_SESSION["state"]) {
			echo "2";
			$params = array("code" => $_GET["code"], "redirect_uri" => $this->redirectUrl);
			$response = $this->client->getAccessToken($this->accessTokenUrl, "authorization_code", $params);

			$accessTokenResult = $response["result"];
			$this->client->setAccessToken($accessTokenResult["access_token"]);
			$this->client->setAccessTokenType(OAuth2\Client::ACCESS_TOKEN_BEARER);

			$response = $this->client->fetch("https://oauth.reddit.com/api/v1/me.json");
			$username = $response["result"]["name"];
			
			$statement = $this->mysqli->prepare("SELECT COUNT(identifier) as usercount FROM bbdraft_users WHERE identifier=?");
			$statement->bind_param("s", $username);
			$statement->execute();
			$statement->bind_result($userExists);
			$statement->fetch();
			$statement->close();
			
			if ($userExists > 0) {
				$this->bindUser($username);
			} else {
				$this->registerUser($username);
			}
		} 
		if ($_GET["state"] !== $_SESSION["state"]) {
			http_response_code(500);
			echo "Given state did not match stored state!";
		}
	}
	private function bindUser($username) {
		$statement = $this->mysqli->prepare("SELECT id, display_name FROM bbdraft_users WHERE identifier=?");
		$statement->bind_param("s", $username);
		$statement->execute();
		$statement->bind_result($userId, $displayName);
		$statement->fetch();
		$statement->close();
		
		$_SESSION["id"] = $userId;
		$_SESSION["name"] = $displayName;
		
		echo "<h3>Welcome back, <strong>" . $_SESSION["name"] . "</strong>!</h3>";
		echo "<script>window.location.replace('dashboard.php');</script>";
	}
	private function registerUser($username) {
		$provider = "reddit";
		
		$statement = $this->mysqli->prepare("INSERT INTO bbdraft_users (identifier, provider, display_name) VALUES (?,?,?)");
		$statement->bind_param('sss', $username, $provider, $username);
		$statement->execute();
		$statement->close();
		
		$statement = $this->mysqli->prepare("SELECT id, display_name FROM bbdraft_users WHERE identifier=?");
		$statement->bind_param("s", $username);
		$statement->execute();
		$statement->bind_result($userId, $displayName);
		$statement->fetch();
		$statement->close();
		
		$_SESSION["id"] = $userId;
		$_SESSION["name"] = $displayName;
		
		echo "<h3>Welcome to BBDraft, <strong>" . $_SESSION["name"] . "</strong>!</h3>";
		echo "<script>window.location.replace('welcome.php');</script>";
	}
}
class getData {
	private $connection;
	private $mysqli;
	
	public function __construct() {
		include_once "includes/connection.php";
		$this->connection = new Connection();
		$this->mysqli = $this->connection->createConnection();
	}
	public function getLeague() {
		$statement = $this->mysqli->prepare("SELECT league FROM bbdraft_users WHERE id = ?");
		$statement->bind_param('i', $_SESSION['id']);
		$statement->execute();
		$statement->bind_result($league);
		$statement->fetch();
		$statement->close();
		return $league;
	}
	public function getLeagueOwner() {
		$league = $this->getLeague();
		
		$statement = $this->mysqli->prepare("SELECT owner FROM bbdraft_leagues WHERE code = ?");
		$statement->bind_param('s', $league);
		$statement->execute();
		$statement->bind_result($leagueOwner);
		$statement->fetch();
		$statement->close();
		
		return $leagueOwner;
	}
	public function getLeagueName() {
		$league = $this->getLeague();
		
		$statement = $this->mysqli->prepare("SELECT name FROM bbdraft_leagues WHERE code = ?");
		$statement->bind_param('s', $league);
		$statement->execute();
		$statement->bind_result($leagueName);
		$statement->fetch();
		$statement->close();
		
		return $leagueName;
	}
}
class Logout {
	public function __construct() {
		unset($_SESSION["id"]);
		unset($_SESSION["name"]);
		echo "<script>window.location.replace('index.php');</script>";
	}
}
class LeagueTasks {
	private $connection;
	private $mysqli;
	
	public function __construct() {
		include_once "includes/connection.php";
		$this->connection = new Connection();
		$this->mysqli = $this->connection->createConnection();
	}
	public function joinPublic() {
		$league = $_POST["publicLeague"];
		
		//need to check that they chose an actual public league option, and didn't edit the options with their browser's dev tools
		//going to use a prepared statement here, because we can't trust this data
		$statement = $this->mysqli->prepare("SELECT COUNT(code) FROM bbdraft_leagues WHERE code = ?");
		$statement->bind_param('s', $league);
		$statement->execute();
		$statement->bind_result($leagueExists);
		$statement->fetch();
		$statement->close();
		
		if ($leagueExists > 0) {
			$statement = $this->mysqli->prepare("UPDATE bbdraft_users SET league = ? WHERE id = ?");
			$statement->bind_param('si', $league, $_SESSION["id"]);
			$statement->execute();
			$statement->close();
			
			echo "<script>window.location.replace('dashboard.php');</script>";
		} else {
			http_response_code(500);
			echo "Not a valid public league!";
		}
	}
	public function createPrivate() {
		$leagueName = $_POST["leagueName"];
		$nameValid = false;
		$ownerValid = false;
		
		if (preg_match("/^[a-z0-9 ]+$/i", $leagueName) && strlen($leagueName) > 0 && strlen($leagueName) <= 30) {
			$nameValid = true;
		}
		
		$statement = $this->mysqli->prepare("SELECT COUNT(owner) FROM bbdraft_leagues WHERE owner = ?");
		$statement->bind_param('i', $_SESSION["id"]);
		$statement->execute();
		$statement->bind_result($leagueOwned);
		$statement->fetch();
		$statement->close();
		if ($leagueOwned == 0) {
			$ownerValid = true;
		}
		
		if ($nameValid == true && $ownerValid == true) {
			//create code, which is an 8 characters long
			$code = substr(str_shuffle("1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ"), -8);
			
			$statement = $this->mysqli->prepare("INSERT INTO bbdraft_leagues (code, name, owner) VALUES (?,?,?)");
			$statement->bind_param('sss', $code, $leagueName, $_SESSION["id"]);
			$statement->execute();
			$statement->close();
			
			$statement = $this->mysqli->prepare("UPDATE bbdraft_users SET league = ? WHERE id = ?");
			$statement->bind_param('si', $code, $_SESSION["id"]);
			$statement->execute();
			$statement->close();
			
			echo "<script>window.location.replace('league.php');</script>";
		}
		
		if ($nameValid == false) {
			$message = "League name is invalid! League names must be 0-30 characters and can only contain letters, numbers, and spaces.";
			return $message;
		}
		if ($ownerValid == false) {
			$message = "You already own a league! If this is a mistake, contact us.";
			return $message;
		}
	}
}
?>