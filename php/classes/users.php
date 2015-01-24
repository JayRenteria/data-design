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

	// CONSTRUCTOR GOES HERE

	/**
	 * accessor method for the userId

	 @return int value of user id
	 **/
	public function getUserId(){
		return($this->userId);
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
}