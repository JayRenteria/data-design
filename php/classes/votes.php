<?php


class Votes {
	/**
	 * id for the user; this is a composite part of the primary key
	 **/
	private $userId;
	/**
	 * id of the comment that the vote is on; this is a composite part of the primary key
	 **/
	private $commentId;
	/**
	 * date and time that the vote occured
	 **/
	private $timeRecorded;
	/**
	 * constructor for this votes class
	 *
	 * @param int $newUserId id of the user that voted
	 * @param mixed $newCommentId id of this comment or null if a new comment
	 * @param mixed $newTimeRecorded date and time vote was made or null to set current date and time
	 * @param int $newVote int of vote that was done
	 * @throws InvalidArgumentException it data types are not valid
	 * @throws RangeException if data values are out of bounds (e.g. strings too long, negative integers)
	 **/
	public function __construct($newUserId, $newCommentId, $newTimeRecorded, $newVote = null) {
		try {
			$this->setUserId($newUserId);
			$this->setCommentId($newCommentId);
			$this->setTimeRecorded($newTimeRecorded);
			$this->setVote($newVote);
		} catch(InvalidArgumentException $invalidArgument) {
			// rethrow the exception to the caller
			throw(new InvalidArgumentException($invalidArgument->getMessage(), 0, $invalidArgument));
		} catch(RangeException $range) {
			// rethrow the exception to the caller
			throw(new RangeException($range->getMessage(), 0, $range));
		}
	}

	/**
	 * the votes amount, +1 or -1
	 **/
	private $vote;


	/**
	 * accessor method for user id
	 *
	 * @return int value of comment id
	 **/
	public function getUserId() {
		return ($this->userId);
	}

	/**
	 * mutator method for user id
	 *
	 * @param int $newUserId new value for user id
	 * @throws InvalidArgumentException if $newUserId is not an integer
	 * @throws RangeException if $newUserId is not positive
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
	 * accessor method for comment id
	 *
	 * @return mixed value of comment id
	 **/
	public function getCommentId() {
		return ($this->commentId);
	}

	/**
	 * mutator method for commentId
	 *
	 * @param int $newCommentId new value of commentId
	 * @throws InvalidArgumentException if $newCommentId is not an integer
	 * @throws RangeException if $newCommentId is not positive
	 **/
	public function setCommentId($newCommentId) {
		// base case: if the comment id is null this is a new comment without a mySQL assigned id (yet)
		if($newCommentId === null) {
			$this->commentId = null;
			return;
		}

		// verify that the commentId is an integer
		$newCommentId = filter_var($newCommentId, FILTER_VALIDATE_INT);
		if($newCommentId === false) {
			throw(new InvalidArgumentException("comment id is not a valid integer"));
		}

		// verify that the integer is positive
		if($newCommentId <= 0) {
			throw(new RangeException("comment id is not positive"));
		}
		// convert and store comment id
		$this->commentId = intval($newCommentId);
	}

	/**
	 * accessor method for comment date
	 *
	 * @return DateTime value of comment date
	 **/
	public function getTimeRecorded() {
		return ($this->timeRecorded);
	}

	/**
	 * mutator method for vote time
	 *
	 * @param mixed $newTimeRecorded vote date as a DateTime object or string (or null to load current time)
	 * @throws InvalidArgumentException if $newTimeRecorded is not a valid object or string
	 * @throws RangeException if $newTimeRecorded is a date that does not exist
	 **/
	public function setTimeRecorded($newTimeRecorded) {
		// base case: if the date is null, use current date and time
		if($newTimeRecorded === null) {
			$this->timeRecorded = new DateTime();
			return;
		}

		// base case: if the date is a DateTime object, there's no work to be done
		if(is_object($newTimeRecorded) === true && get_class($newTimeRecorded) === "DateTime") {
			$this->timeRecorded = $newTimeRecorded;
			return;
		}

		// treat the date as a mySQL date string: Y-m-d H:i:s
		$newTimeRecorded = trim($newTimeRecorded);
		if((preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $newTimeRecorded, $matches)) !== 1) {
			throw(new InvalidArgumentException("comment date is not a valid date"));
		}

		// verify the date is a valid calendar date
		$year = intval($matches[1]);
		$month = intval($matches[2]);
		$day = intval($matches[3]);
		$hour = intval($matches[4]);
		$minute = intval($matches[5]);
		$second = intval($matches[6]);
		if(checkdate($month, $day, $year) === false) {
			throw(new RangeException("comment date $newTimeRecorded is not a Gregorian date"));
		}

		// verify the time is really a valid wall clock time
		if($hour < 0 || $hour >= 24 || $minute < 0 || $minute >= 60 || $second < 0 || $second >= 60) {
			throw(new RangeException("comment date $newTimeRecorded is not a valid time"));
		}
		// store the vote date
		$newTimeRecorded = DateTime::createFromFormat("Y-m-d H:i:s", $newTimeRecorded);
		$this->timeRecorded = $newTimeRecorded;
	}
	/**
	 * accessor method for vote id
	 *
	 * @return int value of vote
	 **/
	public function getVote(){
		return($this->vote);
	}

	/**
	 * mutator method for vote id
	 *
	 * @param int $newVote that has to be 1 or -1
	 * @throw InvalidArgumentException if it is not an integer
	 * @throw RangeException if not 1 or -1
	 **/
	public function setVote($newVote) {
		// base case: if the vote is null this is a new vote without a mySQL assigned id (yet)
		if($newVote=== null) {
			$this->vote = null;
			return;
		}

		// verify that the vote is an integer
		$newVote = filter_var($newVote, FILTER_VALIDATE_INT);
		if($newVote === false) {
			throw(new InvalidArgumentException("vote is not a valid integer"));
		}

		// verify that the integer is 1 or -1
		if($newVote > 1 || $newVote < -1) {
			throw(new RangeException("vote is not 1 or -1"));
		}
		// convert and store vote
		$this->vote = intval($newVote);
	}

	/**
	 * inserts this vote into mySQL
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public function insert(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// enforce the vote is null (i.e., dont insert a vote that already exists)
		if($this->vote !== null) {
			throw(new mysqli_sql_exception("this vote already exists"));
		}

		// create query template
		$query = "INSERT INTO vote(userId, commentId, timeRecorded, vote) VALUES (?, ?, ?, ?)";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		// bind the member variables to the place holders in the template
		$formattedDate = $this->timeRecorded->format("Y-m-d H:i:s");
		$wasClean = $statement->bind_param("issi", $this->userId, $this->commentId, $formattedDate, $this->vote);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("unable to bind parameters:"));
		}

		// execute the statement
		if($statement->execute() === false) {
			throw(new mysqli_sql_exception("unable to execute mySQL statement"));
		}

		// update the null contentId with what mysql just gave us
		$this->vote = $mysqli->insert_id;

		// clean up the statement
		$statement->close();
	}

	/**
	 * deletes this vote from mysql
	 *
	 * @param resource $mysqli pointer to mysql connection, by reference
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public function delete(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// enforce the vote is not null (i.e., dont delete a vote that has not been inserted)
		if($this->vote === null) {
			throw(new mysqli_sql_exception("unable to delete a vote that does not exist"));
		}

		// create query template
		$query = "DELETE FROM vote WHERE vote = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		// bind the member variables to the place holder in the template
		$wasClean = $statement->bind_param("i", $this->vote);
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
	 * updates the vote in mySQL
	 *
	 * @param resource $mysqli pointer to mysql connection, by reference
	 * @throws mysqli_sql_exception when mysql related errors occur
	 **/
	public function update(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// enfore the vote is not null (i.e., dont update a vote that hasnt been inserted)
		if($this->vote === null) {
			throw(new mysqli_sql_exception("unable to update a vote that does not exist"));
		}

		// create a query template
		$query = "UPDATE vote SET timeRecorded = ?, vote = ? WHERE (userId = ?, commentId = ?)";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		// bind the member variables to the place holders in the template
		$formattedDate = $this->timeRecorded->format("Y-m-d H:i:s");
		$wasClean = $statement->bind_param("iisi", $this->userId, $this->commentId, $formattedDate, $this->vote);
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
	 * gets the vote by vote
	 *
	 * @param resource $mysqli pointer to mysql connection, by reference
	 * @param int $vote vote amount to search for
	 * @return mixed array of votes found, or null if not found
	 * @throws mysqli_sql_exception when mysql related errors occur
	 **/
	public static function getVoteByVote(&$mysqli, $vote) {
		// handle degenerate cases
		if(gettype($mysqli) !== "obeject" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// sanitize the description before searching
		$vote = trim($vote);
		$vote = filter_var($vote, FILTER_VALIDATE_INT);

		// create query template
		$query = "SELECT userId, commentId, timeRecorded, vote FROM vote WHERE vote LIKE ?";
		$statement =$mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		// bind the comment content to the place holder in the template
		$vote = "%$vote%";
		$wasClean = $statement->bind_param("i", $vote);
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

		// build an array of Comments
		$votes = array();
		while(($row= $result->fetch_assoc()) !== null) {
			try {
				$vote1 = new Votes($row["userId"], $row["commentId"], $row["timeRecorded"], $row["vote"]);
				$votes[] = $vote1;
			}
			catch(Exception $exception) {
				// if the row couldnt be converted, rethrow it
				throw(new mysqli_sql_exception($exception->getMessage(), 0, $exception));
			}

		}

		// count the results in the array and return:
		// 1) null if 0 results
		// 2) a single object if 1 result
		// 3) the entire array if > 1 result
		$numberOfVotes = count($votes);
		if($numberOfVotes === 0) {
			return(null);
		} else if($numberOfVotes === 1) {
			return($votes[0]);
		} else {
			return($votes);
		}
	}
}
?>


