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

// 检查是否通过 POST 方法提交了表单
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 检查用户是否已经加入房间
    if (isset($_SESSION['joined_room']) && $_SESSION['joined_room'] === true) {
        echo json_encode(array('error' => '你已经加入了一个房间，不能创建新房间。'));
        exit;
    }

    // 获取用户输入的房间名称、最大玩家数
    $roomName = $_POST['room_name'];
    $maxPlayers = $_POST['max_players'];

    // 进行输入验证
    if (empty($roomName) || empty($maxPlayers)) {
        echo json_encode(array('error' => '房间名称和最大玩家数不能为空'));
        exit;
    }

    // 确保最大玩家数在 6 到 12 之间
    if ($maxPlayers < 6 || $maxPlayers > 12) {
        echo json_encode(array('error' => '最大玩家数必须在 6 到 12 之间'));
        exit;
    }

    // 生成一个 4-6 位的随机房间号
    $roomNumber = generateRandomRoomNumber();

    // 将房间信息存储到数据库中
    $sql = "INSERT INTO rooms (room_number, name, max_players, players) VALUES (?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $players = json_encode([array("playerName" => $_SERVER['REMOTE_ADDR'])]); // 初始玩家列表为房主
    $stmt->bind_param("ssis", $roomNumber, $roomName, $maxPlayers, $players);
    if ($stmt->execute()) {
        // 获取插入的房间 ID
        $roomId = $conn->insert_id;
        echo json_encode(array('success' => true, 'roomId' => $roomId));
    } else {
        echo json_encode(array('error' => '创建房间失败，请稍后重试'));
        exit;
    }

    $playerName = $_SERVER['REMOTE_ADDR']; // 玩家IP
    $role = '暂无';
    $status = '在线';
    $voteCount = '0';
    $isowner = '1';
    
    // 插入玩家信息到 room_players 表
    $sql = "INSERT INTO room_players (room_id, player_name, role, status, vote_count, is_owner) VALUES (?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssii", $roomNumber, $playerName, $role, $status, $voteCount, $isowner);
    if ($stmt->execute()) {
        // 可以添加更多的成功处理逻辑
    } else {
        echo json_encode(array('error' => "玩家信息插入失败：". $stmt->error));
        exit;
    }

    // 设置会话变量，表示用户已经加入房间
    $_SESSION['joined_room'] = true;

    $stmt->close();
} else {
    echo json_encode(array('error' => '无效的请求方法'));
    exit;
}

// 关闭数据库连接
$conn->close();

// 生成 4-6 位随机房间号的函数
function generateRandomRoomNumber() {
    $length = rand(4, 6);
    $characters = '1234567890';
    $roomNumber = '';
    $firstChar = $characters[rand(1, 9)]; // 确保第一个字符不为 0
    $roomNumber.= $firstChar;
    for ($i = 1; $i < $length; $i++) {
        $roomNumber.= $characters[rand(0, strlen($characters) - 1)];
    }
    return $roomNumber;
}
?>