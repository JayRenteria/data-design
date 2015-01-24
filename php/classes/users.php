<?php
/**
 * Small cross section of a reddit comment page
 *
 * This users class can be considered a small example of what reddit may store when new users sign up to the site. This can be extended
 * to show other features of the site
 *
 * @author Jay Renteria <jay@jayrenteria.com>
 **/
class Users {
	/**
	 * id for the user, this is the primary key
	 **/
	private $userId;
	/**
	 * the users email
	 **/
	private $email;
	/**
	 * the users username
	 **/
	private $username;

	/**
	 * constructor for this user class
	 *
	 * @param int $newUserId id of the user
	 * @param string $newEmail email of user
	 * @param string $newUsername string containing username
	 * @throws InvalidArgumentException it data types are not valid
	 * @throws RangeException if data values are out of bounds (e.g. strings too long, negative integers)
	 **/
	public function __construct($newUserId, $newEmail, $newUsername = null) {
		try {
			$this->setUserId($newUserId);
			$this->setEmail($newEmail);
			$this->setUsername($newUsername);
		} catch(InvalidArgumentException $invalidArgument) {
			// rethrow the exception to the caller
			throw(new InvalidArgumentException($invalidArgument->getMessage(), 0, $invalidArgument));
		} catch(RangeException $range) {
			// rethrow the exception to the caller
			throw(new RangeException($range->getMessage(), 0, $range));
		}
	}

	/**
	 * accessor method for the userId
	 *
	 * @return int value of user id
	 **/
	public function getUserId() {
		return ($this->userId);
	}

	/**
	 * mutator method for the userId
	 *
	 * @param int $newUserId new value of $userId
	 * @throws InvalidArgumentException if the $userId is not an integer
	 * @throws RangeException if the $userId is not positive
	 **/
	public function setUserId($newUserId) {
		// verify the user id is valid
		$newUserId = filter_var($newUserId, FILTER_VALIDATE_INT);
		if($newUserId === false) {
			throw(new InvalidArgumentException("user id is not a valid integer"));
		}

		// verify the user id is positive
		if($newUserId <= 0) {
			throw(new RangeException("user id is not positive"));
		}

		// convert and store the user id
		$this->userId = intval($newUserId);
	}

	/**
	 * accessor method for users email
	 *
	 * @return string value of users email
	 **/
	public function getEmail() {
		return ($this->email);
	}

	/**
	 * mutator method for users email
	 *
	 * @param string $newEmail new value of users email
	 * @throws InvalidArgumentException if the email is not a string or insecure
	 * @throws RangeException if $newEmail is > 128 characters
	 */
	public function setEmail($newEmail) {
		// verify the email is a string and secure
		$newEmail = trim($newEmail);
		$newEmail = filter_var($newEmail, FILTER_SANITIZE_STRING);
		if(empty($newEmail) === true) {
			throw (new mysqli_sql_exception("email is empty or insecure"));
		}

		// verify the email will fit in the database
		if(strlen($newEmail) > 128) {
			throw(new mysqli_sql_exception("email too large"));
		}

		// store the email
		$this->email = $newEmail;
	}

	/**
	 * accessor method for username
	 *
	 * @return string value for username
	 **/
	public function getUsername() {
		return ($this->username);
	}

	/**
	 * mutator method for username
	 *
	 * @param string $newUsername value for username
	 * @throws InvalidArgumentException if username is not a string or insecure
	 * @throws RangeException if $newUsername is > 32 characters
	 **/
	public function setUsername($newUsername) {
		// verify the username is a string and secure
		$newUsername = trim($newUsername);
		$newUsername = filter_var($newUsername, FILTER_SANITIZE_STRING);
		if(empty ($newUsername) === true) {
			throw(new mysqli_sql_exception("username is empty or insecure"));
		}

		// verify the username will fit in the database
		if(strlen($newUsername) > 32) {
			throw(new mysqli_sql_exception("username too long"));
		}

		// store the username
		$this->username = $newUsername;
	}

	/**
	 * inserts this user into mySQL
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public function insert(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// enforce the userId is null (i.e., dont insert a user that already exists)
		if($this->userId !== null) {
			throw(new mysqli_sql_exception("this user already exists"));
		}

		// create query template
		$query = "INSERT INTO users(userId, email, username) VALUES (?, ?, ?)";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		// bind the member variables to the place holders in the template
		$wasClean = $statement->bind_param("iss", $this->userId, $this->email, $this->username);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("unable to bind parameters:"));
		}

		// execute the statement
		if($statement->execute() === false) {
			throw(new mysqli_sql_exception("unable to execute mySQL statement"));
		}

		// update the null userId with what mysql just gave us
		$this->userId = $mysqli->insert_id;

		// clean up the statement
		$statement->close();
	}

	/**
	 * deletes this user from mysql
	 *
	 * @param resource $mysqli pointer to mysql connection, by reference
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public function delete(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// enforce the userId is not null (i.e., dont delete a user that has not been inserted)
		if($this->userId === null) {
			throw(new mysqli_sql_exception("unable to delete a user that does not exist"));
		}

		// create query template
		$query = "DELETE FROM users WHERE userId = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		// bind the member variables to the place holder in the template
		$wasClean = $statement->bind_param("i", $this->userId);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("unable to bind parameters"));
		}

		// execute the statement
		if($statement->execute() === false) {
			throw(new mysqli_sql_exception("unable to execute mySQL statement"));
		}

		// clean up the statement
		$statement->close();
	}

	/**
	 * updates the user in mySQL
	 *
	 * @param resource $mysqli pointer to mysql connection, by reference
	 * @throws mysqli_sql_exception when mysql related errors occur
	 **/
	public function update(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// enforce the userId is not null (i.e., dont update a user that hasnt been inserted)
		if($this->userId === null) {
			throw(new mysqli_sql_exception("unable to update a user that does not exist"));
		}

		// create a query template
		$query = "UPDATE users SET userId = ?, email = ?, username = ? WHERE userId = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		// bind the member variables to the place holders in the template
		$wasClean = $statement->bind_param("iss", $this->userId, $this->email, $this->username);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("unable to bind parameters"));
		}

		// execute the statement
		if($statement->execute() === false) {
			throw(new mysqli_sql_exception("unable to execute mysql statement: " . $statement->error));
		}

		// clean up the statement
		$statement->close();
	}

	/**
	 * gets the user by userId
	 *
	 * @param resource $mysqli pointer to mysql connection, by reference
	 * @param string $newUsername username to search for
	 * @return mixed array of users found, or null if not found
	 * @throws mysqli_sql_exception when mysql related errors occur
	 **/
	public static function getUserByUsername(&$mysqli, $username) {
		// handle degenerate cases
		if(gettype($mysqli) !== "obeject" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// sanitize the description before searching
		$username = trim($username);
		$username = filter_var($username, FILTER_SANITIZE_STRING);

		// create query template
		$query = "SELECT userId, email, username FROM username WHERE username LIKE ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		// bind the username to the place holder in the template
		$usernam = "%$username%";
		$wasClean = $statement->bind_param("s", $username);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("unable to bind parameters"));
		}

		// execute the statement
		if($statement->execute() === false) {
			throw(new mysqli_sql_exception("unable to execute mySQL statement"));
		}

		// get result from the SELECT query
		$result = $statement->get_result();
		if($result === false) {
			throw(new mysqli_sql_exception("unable to get result set"));
		}

		// build an array of users
		$users = array();
		while(($row = $result->fetch_assoc()) !== null) {
			try {
				$user = new Users($row["userId"], $row["email"], $row["username"]);
				$users[] = $user;
			} catch(Exception $exception) {
				// if the row couldnt be converted, rethrow it
				throw(new mysqli_sql_exception($exception->getMessage(), 0, $exception));
			}

		}

		// count the results in the array and return:
		// 1) null if 0 results
		// 2) a single object if 1 result
		// 3) the entire array if > 1 result
		$numberOfUsers = count($users);
		if($numberOfUsers === 0) {
			return (null);
		} else if($numberOfUsers === 1) {
			return ($users[0]);
		} else {
			return ($users);
		}
	}
}