<?php
/**
 * Small Cross Section of a reddit comment page
 *
 * This Comments class can be considered a small example of what reddit may store when comments are posted. This can be extended to show
 * other features of reddit.
 *
 * @author Jay Renteria <jay@jayrenteria.com>
 **/
class Comments {
	/**
	 * id for the comment; this is the primary key
	 **/
	private $commentId;
	/**
	 * id of the user that wrote the comment; this is a foreign key
	 **/
	private $userId;
	/**
	 * the comments textual content
	 **/
	private $commentContent;
	/**
	 * date and time that the comment was written
	 **/
	private $commentDate;

	/**
	 * constructor for this commment class
	 *
	 * @param mixed $newCommentId id of this comment or null if a new comment
	 * @param int $newUserId id of the user that wrote the comment
	 * @param string $newCommentContent string containing actual comment content
	 * @param mixed $newCommentDate date and time comment was written or null to set current date and time
	 * @throws InvalidArgumentException it data types are not valid
	 * @throws RangeException if data values are out of bounds (e.g. strings too long, negative integers)
	 **/
	public function __construct($newCommentId, $newUserId, $newCommentContent, $newCommentDate = null) {
		try {
			$this->setCommentId($newCommentId);
			$this->setUserId($newUserId);
			$this->setCommentContent($newCommentContent);
			$this->setCommentDate($newCommentDate);
		} catch(InvalidArgumentException $invalidArgument) {
			// rethrow the exception to the caller
			throw(new InvalidArgumentException($invalidArgument->getMessage(), 0, $invalidArgument));
		} catch(RangeException $range) {
			// rethrow the exception to the caller
			throw(new RangeException($range->getMessage(), 0, $range));
		}
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
		// convert and store profile id
		$this->commentId = intval($newCommentId);
	}

	/**
	 * accessor method for user id
	 *
	 * @return int value of the user id
	 **/
	public function getUserId() {
		return ($this->$userId);
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
	 * accessor for commentContent
	 *
	 * @return string value for comment content
	 **/
	public function getCommentContent() {
		return ($this->commentContent);
	}

	/**
	 * mutator method for comment content
	 *
	 * @param string $newCommentContent new value of comment content
	 * @throws InvalidArgumentException if $newCommentContent is not a string or insecure
	 * @throws RangeException if $newCommentContent is > 15000(edit in sql)
	 **/
	public function setCommentContent($newCommentContent) {
		// verify that the comment content is secure
		$newCommentContent = trim($newCommentContent);
		$newCommentContent = filter_var($newCommentContent, FILTER_SANITIZE_STRING);
		if(empty($newCommentContent) === true) {
			throw(new InvalidArgumentException("comment content is empty or insecure"));
		}

		// verify that the comment will fit in the database
		if(strlen($newCommentContent) > 15000) {
			throw(new RangeException("comment content too large"));
		}

		// store the comment content
		$this->commentContent = $newCommentContent;
	}

	/**
	 * accessor method for comment date
	 *
	 * @return DateTime value of comment date
	 **/
	public function getCommentDate() {
		return ($this->commentDate);
	}

	/**
	 * mutator method for comment date
	 *
	 * @param mixed $newCommentDate comment date as a DateTime object or string (or null to load current time)
	 * @throws InvalidArgumentException if $newCommentDate is not a valid object or string
	 * @throws RangeException if $newCommentDate is a date that does not exist
	 **/
	public function setCommentDate($newCommentDate) {
		// base case: if the date is null, use current date and time
		if($newCommentDate === null) {
			$this->commentDate = new DateTime();
			return;
		}

		// base case: if the date is a DateTime object, there's no work to be done
		if(is_object($newCommentDate) === true && get_class($newCommentDate) === "DateTime") {
			$this->commentDate = $newCommentDate;
			return;
		}

		// treat the date as a mySQL date string: Y-m-d H:i:s
		$newCommentDate = trim($newCommentDate);
		if((preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $newCommentDate, $matches)) !== 1) {
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
			throw(new RangeException("comment date $newCommentDate is not a Gregorian date"));
		}

		// verify the time is really a valid wall clock time
		if($hour < 0 || $hour >= 24 || $minute < 0 || $minute >= 60 || $second < 0 || $second >= 60) {
			throw(new RangeException("comment date $newCommentDate is not a valid time"));
		}
		// store the comment date
		$newCommentDate = DateTime::createFromFormat("Y-m-d H:i:s", $newCommentDate);
		$this->commentDate = $newCommentDate;
	}

	/**
	 * inserts this comment into mySQL
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public function insert(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// enforce the commentId is null (i.e., dont insert a comment that already exists)
		if($this->commentId !== null) {
			throw(new mysqli_sql_exception("this comment already exists"));
		}

		// create query template
		$query = "INSERT INTO comments(userId, commentContent, commentDate) VALUES (?, ?, ?)";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		// bind the member variables to the place holders in the template
		$formattedDate = $this->commentDate->format("Y-m-d H:i:s");
		$wasClean = $statement->bind_param("iss", $this->userId, $this->commentContent, $formattedDate);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("unable to bind parameters:"));
		}

		// execute the statement
		if($statement->execute() === false) {
			throw(new mysqli_sql_exception("unable to execute mySQL statement"));
		}

		// update the null contentId with what mysql just gave us
		$this->commentId = $mysqli->insert_id;

		// clean up the statement
		$statement->close();
	}

	/**
	 * deletes this comment from mysql
	 *
	 * @param resource $mysqli pointer to mysql connection, by reference
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public function delete(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// enforce the commentId is not null (i.e., dont delete a  comment that has not been inserted)
		if($this->commentId === null) {
			throw(new mysqli_sql_exception("unable to delete a comment that does not exist"));
		}

		// create query template
		$query = "DELETE FROM comments WHERE commentId = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		// bind the member variables to the place holder in the template
		$wasClean = $statement->bind_param("i", $this->commentId);
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
	 * updates the comment in mySQL
	 *
	 * @param resource $mysqli pointer to mysql connection, by reference
	 * @throws mysqli_sql_exception when mysql related errors occur
	 **/
	public function update(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// enfore the commentId is not null (i.e., dont update a comment that hasnt been inserted)
		if($this->commentId === null) {
			throw(new mysqli_sql_exception("unable to update a comment that does not exist"));
		}

		// create a query template
		$query = "UPDATE comments SET userId = ?, commentContent = ?, commentDate = ? WHERE commentId = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		// bind the member variables to the place holders in the template
		$formattedDate = $this->commentDate->format("Y-m-d H:i:s");
		$wasClean = $statement->bind_param("issi", $this->userId, $this->commentContent, $formattedDate, $this->commentId);
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
	 * gets the comment by content
	 *
	 * @param resource $mysqli pointer to mysql connection, by reference
	 * @param string $commentContent comment content to search for
	 * @return mixed array of Comments found, or null if not found
	 * @throws mysqli_sql_exception when mysql related errors occur
	 **/
	public static function getCommentByCommentContent(&$mysqli, $commentContent) {
		// handle degenerate cases
		if(gettype($mysqli) !== "obeject" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// sanitize the description before searching
		$commentContent = trim($commentContent);
		$commentContent = filter_var($commentContent, FILTER_SANITIZE_STRING);

		// create query template
		$query = "SELECT commentId, userId, commentContent, commentDate FROM comments WHERE commentContent LIKE ?";
		$statement =$mysqli->prepare($query);
		if($statment === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		// bind the comment content to the place holder in the template
		$commentContent = "%$commentContent%";
		$wasClean = $statement->bind_param("s", $commentContent);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("unable to bind parameters"));
		}

		// execute the statement
		if($statement->execute() === false) {
			throw(new mysqli_sql_exception("unable to execute mySQL statement"));
		}
	}
}
?>