<?php

namespace Lib\services;

require_once __DIR__ . '/../../config/bootstrap.php';

use Lib\db\Database;
use Lib\services\profile_services;
use PDO;
use PDOException;



class Chat_services {
    private PDO $db;

    public function __construct() {
		$this->db = Database::getConnection();
    }

    public function createChatRoom(string $uid, string $seller_id) {
        try {
            $user_one = min($uid, $seller_id);
            $user_two = max($uid, $seller_id);

            // 1. Check if the room already exists
            $sql = "SELECT BIN_TO_UUID(room_id) as room_id 
                    FROM chat_rooms 
                    WHERE user_one = UUID_TO_BIN(:user_one) 
                    AND user_two = UUID_TO_BIN(:user_two) 
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_one' => $user_one,
                'user_two' => $user_two
            ]);

            $room = $stmt->fetch(PDO::FETCH_ASSOC);

            // If room already exists, redirect into it immediately
            if ($room && isset($room['room_id'])) {
                header('Location: /pages/chat/room/?room_id=' . $room['room_id']);
                exit;
            }

            // 2. If it does not exist, insert it
            $sql = "INSERT INTO chat_rooms (user_one, user_two) 
                    VALUES (UUID_TO_BIN(:user_one), UUID_TO_BIN(:user_two))";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_one' => $user_one,
                'user_two' => $user_two
            ]);

            // 3. Fetch the newly created room ID
            $sql = "SELECT BIN_TO_UUID(room_id) as room_id 
                    FROM chat_rooms 
                    WHERE user_one = UUID_TO_BIN(:user_one) 
                    AND user_two = UUID_TO_BIN(:user_two) 
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_one' => $user_one,
                'user_two' => $user_two
            ]);

            $room = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$room) {
                throw new \Exception("Room not found after insert");
            }

            $room_id = $room['room_id'];

            header('Location: /pages/chat/room/?room_id=' . $room_id);
            exit;

        } catch (\PDOException $e) {
            error_log("Error creating room: " . $e->getMessage());
        }
    }

    public function getRoom(string $room_id): ?string {
        $sql = "SELECT BIN_TO_UUID(room_id) as room_id FROM chat_rooms WHERE room_id = UUID_TO_BIN(:room_id) LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'room_id'=>$room_id
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['room_id'] : null;
    }

    public function getUserRooms(string $uid): array {
        try {
            $sql = "
                SELECT 
                    BIN_TO_UUID(r.room_id) AS room_id,
                    CONCAT(u.firstName, ' ', u.lastName) AS full_name,
                    u.profile_image_url,
                    m.message_text AS last_message
                FROM chat_rooms r

                -- join the OTHER user
                JOIN users u 
                    ON u.uid = CASE 
                        WHEN r.user_one = UUID_TO_BIN(:uid1) THEN r.user_two
                        ELSE r.user_one
                    END

                -- latest message per room
                LEFT JOIN (
                    SELECT cm1.*
                    FROM chat_messages cm1
                    JOIN (
                        SELECT room_id, MAX(sent_at) AS max_sent
                        FROM chat_messages
                        GROUP BY room_id
                    ) cm2 
                    ON cm1.room_id = cm2.room_id 
                    AND cm1.sent_at = cm2.max_sent
                ) m ON m.room_id = r.room_id

                WHERE r.user_one = UUID_TO_BIN(:uid2)
                OR r.user_two = UUID_TO_BIN(:uid3)

                ORDER BY m.sent_at DESC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'uid1' => $uid,
                'uid2' => $uid,
                'uid3' => $uid
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error fetching user rooms: " . $e->getMessage());
            return [];
        }
    }

    public function sendMessage(string $roomId, string $senderId, string $messageText, ?string $attachmentUrl = null, ?string $fileType = null): ?array {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO chat_messages (room_id, sender_id, message_text) 
                    VALUES (UUID_TO_BIN(:room_id), UUID_TO_BIN(:sender_id), :msg)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'room_id' => $roomId,
                'sender_id' => $senderId,
                'msg' => $messageText
            ]);

            $sqlGetId = "SELECT BIN_TO_UUID(message_id) AS msg_id FROM chat_messages WHERE room_id = UUID_TO_BIN(:room_id) AND sender_id = UUID_TO_BIN(:sender_id) ORDER BY sent_at DESC LIMIT 1";
            $stmtGetId = $this->db->prepare($sqlGetId);
            $stmtGetId->execute([
                'room_id' => $roomId,
                'sender_id' => $senderId
            ]);
            $msgRow = $stmtGetId->fetch(PDO::FETCH_ASSOC);
            $messageId = $msgRow['msg_id'];

            if ($attachmentUrl && $fileType) {
                // Insert attachment
                $sqlAtt = "INSERT INTO chat_attachments (message_id, attachment_url, file_type) VALUES (UUID_TO_BIN(:msg_id), :url, :type)";
                $stmtAtt = $this->db->prepare($sqlAtt);
                $stmtAtt->execute([
                    'msg_id' => $messageId,
                    'url' => $attachmentUrl,
                    'type' => $fileType
                ]);

                // Get attachment id
                $sqlAttId = "SELECT BIN_TO_UUID(attachment_id) as att_id FROM chat_attachments WHERE message_id = UUID_TO_BIN(:msg_id) ORDER BY created_at DESC LIMIT 1";
                $stmtAttId = $this->db->prepare($sqlAttId);
                $stmtAttId->execute(['msg_id' => $messageId]);
                $attRow = $stmtAttId->fetch(PDO::FETCH_ASSOC);

                // Update message with attachment_id
                if ($attRow && isset($attRow['att_id'])) {
                    $sqlUpdate = "UPDATE chat_messages SET attachment_id = UUID_TO_BIN(:att_id) WHERE message_id = UUID_TO_BIN(:msg_id)";
                    $stmtUpdate = $this->db->prepare($sqlUpdate);
                    $stmtUpdate->execute([
                        'att_id' => $attRow['att_id'],
                        'msg_id' => $messageId
                    ]);
                }
            }

            $this->db->commit();

            // Fetch the inserted message details to publish
            $msgDetails = $this->getMessage($messageId);

            // Fetch the room details to notify both users
            $sqlRoom = "SELECT BIN_TO_UUID(user_one) as u1, BIN_TO_UUID(user_two) as u2 FROM chat_rooms WHERE room_id = UUID_TO_BIN(:room_id)";
            $stmtRoom = $this->db->prepare($sqlRoom);
            $stmtRoom->execute(['room_id' => $roomId]);
            $roomUsers = $stmtRoom->fetch(PDO::FETCH_ASSOC);
            
            // Publish to Redis
            if ($roomUsers) {
                $redis = \Lib\cache\Redis::getInstance();
                $payload = json_encode([
                    'type' => 'new_message',
                    'room_id' => $roomId,
                    'message' => $msgDetails
                ]);
                $redis->publish("chat_user_" . $roomUsers['u1'], $payload);
                $redis->publish("chat_user_" . $roomUsers['u2'], $payload);
            }

            return $msgDetails;

        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error sending message: " . $e->getMessage());
            return null;
        }
    }

    public function getMessage(string $messageId): ?array {
        try {
            $sql = "
                SELECT 
                    BIN_TO_UUID(m.message_id) AS message_id,
                    BIN_TO_UUID(m.room_id) AS room_id,
                    BIN_TO_UUID(m.sender_id) AS sender_id,
                    m.message_text,
                    m.sent_at,
                    BIN_TO_UUID(m.attachment_id) AS attachment_id,
                    a.attachment_url,
                    a.file_type
                FROM chat_messages m
                LEFT JOIN chat_attachments a ON m.attachment_id = a.attachment_id
                WHERE m.message_id = UUID_TO_BIN(:msg_id)
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['msg_id' => $messageId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ? $res : null;
        } catch (\PDOException $e) {
            error_log("Error fetching message: " . $e->getMessage());
            return null;
        }
    }

    public function getRoomMessages(string $roomId): array {
        try {
            $sql = "
                SELECT 
                    BIN_TO_UUID(m.message_id) AS message_id,
                    BIN_TO_UUID(m.room_id) AS room_id,
                    BIN_TO_UUID(m.sender_id) AS sender_id,
                    m.message_text,
                    m.sent_at,
                    BIN_TO_UUID(m.attachment_id) AS attachment_id,
                    a.attachment_url,
                    a.file_type
                FROM chat_messages m
                LEFT JOIN chat_attachments a ON m.attachment_id = a.attachment_id
                WHERE m.room_id = UUID_TO_BIN(:room_id)
                ORDER BY m.sent_at ASC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['room_id' => $roomId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching room messages: " . $e->getMessage());
            return [];
        }
    }

    public function getRoomOtherUser(string $roomId, string $currentUserId): ?array {
        try {
            $sql = "
                SELECT u.firstName, u.lastName, u.profile_image_url
                FROM chat_rooms r
                JOIN users u ON u.uid = CASE
                    WHEN r.user_one = UUID_TO_BIN(:uid) THEN r.user_two
                    ELSE r.user_one
                END
                WHERE r.room_id = UUID_TO_BIN(:room_id)
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['uid' => $currentUserId, 'room_id' => $roomId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ? $res : null;
        } catch (\PDOException $e) {
            return null;
        }
    }
}