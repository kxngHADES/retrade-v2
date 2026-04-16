# Registraton service

## Authentication Service

### Overview

The `Authentication_service` class handles user registration and session management. It uses a PDO database connection and PHP sessions.

**Namespace:** `Lib\services`  
**Database table:** `users`  
**Session keys:** `uid`, `firstName`, `lastName`, `email`

---

### Class: `Authentication_services`

#### Dependencies

- `Lib\db\Database` - provides the PDO connection
- PHP sessions - automatically started IF not active

#### Full class code
```php
<?php

require __DIR__ . '/../../config/bootstrap.php';

namespace Lib\services;

use Lib\db\Database;
use PDO;
use PDOException;

class Authentication_service {
	private PDO $db;

	public function __construct() {
		$this->db = Database::getConnection();
	}

	//User Registration
	public function register(string $firstName, string $lastName, string $email, string $phone, string $password): string|false {

		if (session_status() === PHP_SESSION_NONE){
			session_start();
		}

		try {

			$this->db->beginTransaction();

			$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

			$query = "INSERT INTO users (firstName, lastName, email, phoneNumber, password, is_phone_verified)VALUES (:firstName, :lastName, :email, :phoneNumber, :password, 1)";

			$stmt = $this->db->prepare($query);
			$stmt->execute([
				":firstName" => $firstName,
				":lastName" => $lastName,
				":email" => $email,
				":phoneNumber" => $phone,
				":password" => $hashedPassword
			]);

			$stmt = $this->db->prepare("SELECT BIN_TO_UUID(uid) as uuid, firstName, lastName, email FROM users WHERE email = :email LIMIT 1");
			$stmt->execute([':email' => $email]);
			$user = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($user) {
				$this->db->commit();

				$_SESSION['uid'] = $user['uuid'];
				$_SESSION['firstName'] = $user['firstName'];
				$_SESSION['lastName'] = $user['lastName'];
				$_SESSION['email'] = $user['email'];
				return $user['uuid'];
			} else {
				$this->db->rollBack();

				error_log("Registration failed: User inserted but not found for email: $email");
				return false;
			}

		} catch (PDOException $e){

			if ($this->db->inTransaction()) {
				$this->db->rollBack();
			}

			if ($e->getCode() == 23000){
				error_log("Duplicate user: $email or $phone");
			} else {
				error_log("Registration failed: " . $e->getMessage());
			}
			
			return false;
		}
	}
}
```


#### Method: `register`

##### Purpose
Creates a new user account, stores the user's data in the session, and returns the UUID. The operation is atomic - both the insert and the SELECT succeed of fail together


##### Signature

```php
public function register(
	string $firstName,
	string $lastName,
	string $email,
	string $phone,
	string $password
): string|false
```


##### Parameters

| Parameter | Type | Description |
| ---- | ---- | ---- |
| $firstName | string | User's first name |
| $lastName | string | User's last name |
| $email | string | unique email address |
| $phone | string | unique phone number |
| $password | string | Plain text password whcih will be hashed |

##### Return Value
* `string` - The users UUID converted from BINARY(16) using BIN_TO_UUID()
* `false` - Registration failed
	 * deuplicate entry
	 * database error
	 * missing user after insert


##### Side Effects
* starts a session if none is active
* writes to `$_SESSION`:
	 * uid
	 * firstName
	 * lastName
	 * email
* Logs errors using error_log()

##### Exceptions Handled
* `PDOException` with code 23000 (duplicate entry) -> logs "Duplicate user" for the error
* Other `PDOExcpetion` -> logs the full error message


### Database Requirements
The user table at least has these columns:

| Column | Type | Constraints |
| ---- | ---- | ---- |
| uid | BINARY(16) | Primary key, auto-generated |
| firstName | VARCHAR | NOT NULL |
| lastName | VARCHAR | NOT NULL |
| email | VARCHAR | Unique, NOT NULL|
| phoneNumber | VARCHAR | Unique, NOT NULL |
| password | VARCHAR | NOT NULL (hashed) |
| is_phone_verified | TINYINT | DEFAULT 1 |
