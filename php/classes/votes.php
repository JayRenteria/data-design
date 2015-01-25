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
	 * the votes amount, +1 or -1
	 **/
	private $vote;

	/// CONSTRUCTOR GOES HERE


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
		// convert and store profile id
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
	 * @param mixed $newTimeRecorded vpte date as a DateTime object or string (or null to load current time)
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

