# Developer SQL query help for future

## Fetch anything using the php uid
```sql
SELECT * FROM table WHERE id = UUID_TO_BIN('id_value');
```


### Example fetching user

```php
$stmt = $pdo->prepare("
	SELECT BIN_TO_UUID(uid) AS uid, firstName, lastName, email FROM users WHERE uid = UUID_TO_BIN(:uid)
");

$stmt->execute([':uid' => $_SESSION['uid']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
```


> [!NOTE]
> * The `uid` column is `BINARY(16)` - you cannot compare it directly with a string. Always use `UUID_TO_BIN()` on the input
> * `UUID_TO_BIN()` defaults to the same binary layout as `UUID_TO_BIN(UUID())` when we inserted the row, so it will match correctly
> * if you omit `BIN_TO_UUID()` in the `SELECT`, the binary value would be returned as a raw string of 16 bytes (unreadable)