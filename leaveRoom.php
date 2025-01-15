<?php
// 显示所有错误
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 引入配置文件
require_once 'config.php';

// 开启会话
session_start();

// 获取数据库连接
$conn = getConnection();

// 检查用户是否已经加入房间
if (!isset($_SESSION['joined_room'])) {
    echo json_encode(array('error' => '你没有加入任何房间，无法退出。'));
    exit;
}

// 通过 GET 方式获取房间号或房间 ID 信息
$roomId = $_GET['roomId']; // 从 URL 参数中获取房间号或房间 ID，例如：quitRoom.php?roomId=12345

// 从 rooms 表中删除玩家信息
$sql = "DELETE FROM room_players WHERE room_id =? AND player_name =?";
$stmt = $conn->prepare($sql);
$playerName = $_SERVER['REMOTE_ADDR'];
$stmt->bind_param("is", $roomId, $playerName);
if ($stmt->execute()) {
    // 检查房间是否还有其他玩家
    $sql = "SELECT COUNT(*) as player_count FROM room_players WHERE room_id =?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $playerCount = $row['player_count'];
    if ($playerCount == 0) {
        // 如果没有玩家，删除房间
        $sql = "DELETE FROM rooms WHERE room_id =?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $roomId);
        $stmt->execute();
    }
    echo json_encode(array('success' => true));
} else {
    echo json_encode(array('error' => '退出房间失败，请稍后重试'));
    exit;
}

// 清除会话中的房间信息
unset($_SESSION['joined_room']);
unset($_SESSION['room_id']); // 清除存储的房间 ID 信息，根据实际情况修改

// 关闭数据库连接
$conn->close();
?>