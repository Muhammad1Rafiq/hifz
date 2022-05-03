<?php

$BOT_TOKEN = "5397498179:AAGEOwB8yKMiHtvolbmmkZ6ylF5ZKzQkb0o";
$con = new mysqli("server54.mainpacket.com", "basknetn_krd", "1m2u3h4A@", "basknetn_hifz");

// Check connection
if ($con->connect_errno) {
    echo "Failed to connect to MySQL: " . $con->connect_error;
    exit();
}

$update = file_get_contents('php://input');
$update = json_decode($update, true);
$userChatId = $update["message"]["from"]["id"] ? $update["message"]["from"]["id"] : null;
if ($userChatId == null)
    $userChatId = $update["my_chat_member"]["chat"]["id"] ? $update["my_chat_member"]["chat"]["id"] : null;

if ($userChatId) {
    $userMessage = $update["message"]["text"] ? $update["message"]["text"] : "Nothing";
    $firstName = "";
    $firstName = $firstName . $update["message"]["from"]["first_name"] ? $update["message"]["from"]["first_name"] : "N/A";
    $lastName = "";
    $lastName = $lastName . $update["message"]["from"]["last_name"] ? $update["message"]["from"]["last_name"] : "N/A";
    $fullName = $firstName . " " . $lastName;
    $replyMsg = "Hello " . $fullName . "\nYou said: " . $userMessage;

    $result = mysqli_query($con, "SELECT * FROM `surah`");
    $a = array();
    while($row = mysqli_fetch_assoc($result)){
        $object = [[["text" => "# ".$row['id']." - ".$row['name']." - ".$row['kuname']]]];
        $a = array_merge($a,$object);
    }
    $surahSelect = json_encode([
        "keyboard" => $a
    ]);

    try {
        if ($update["my_chat_member"]["new_chat_member"]["status"] == "kicked") {
            $result = mysqli_query($con, "UPDATE `users` SET `connected`= 0 where `chat_id` = $userChatId");
            if ($result)
                echo $fullName . " kicked !";
        }
    }
    catch (\Throwable $th) {
        echo $th;
    }

    if(substr($userMessage,0,1) == "#"){
        $sub = substr($userMessage,2,4);
        $sub = substr($sub,0,strpos($sub, " "));
        echo "(".$sub.")";
        $result = mysqli_query($con, "UPDATE `users_surah`  SET `surah` = $sub where `chat_id` = $userChatId ");

    }


    if ($userMessage == "/start") {
        $result = mysqli_query($con, "SELECT * FROM `users` where `chat_id` = $userChatId");
        if (mysqli_num_rows($result) >= 1) {
            echo "user in database!";
            $result = mysqli_query($con, "UPDATE `users` SET `connected`= 1 where `chat_id` = $userChatId");
            if ($result)
                echo $fullName . " updated !";
        }
        else {
            $result = mysqli_query($con, "INSERT INTO `users` VALUES (null, '$userChatId', '$firstName', '$lastName', '$fullName', '','0', current_timestamp(), 1)");
            $result = mysqli_query($con, "INSERT INTO `users_reciter`  VALUES (null, $userChatId, 'MaherAlMuaiqly128kbps')");
            $result = mysqli_query($con, "INSERT INTO `users_surah`  VALUES (null, $userChatId, 1)");
            echo "added to database! " . $result;
        }
    }

    if ($update["message"]["contact"] != null) {
        $phone = $update["message"]["contact"]['phone_number'];
        $user_id = $update["message"]["contact"]['user_id'];
        $result = mysqli_query($con, "UPDATE `users` SET `phone`= '$phone', `user_id` = $user_id where `chat_id` = $userChatId");

        if ($result)
            echo $userChatId . "phone and userID updated !";
    }
    else {

    }

    $markup = json_encode([
        "keyboard" => [
            [
                [
                    "text" => "الفاتحة",
                    "callback_data" => "1"
                ],
                [
                    "text" => "البقرة",
                    "callback_data" => "1"
                ],
                [
                    "text" => "ال عمران",
                    "callback_data" => "1"
                ],
                [
                    "text" => "نساء",
                    "callback_data" => "1"
                ],

            ],
            [
                [
                    "text" => "الفاتحة",
                    "callback_data" => "1"
                ],
                [
                    "text" => "البقرة",
                    "callback_data" => "1"
                ],
                [
                    "text" => "ال عمران",
                    "callback_data" => "1"
                ],
                [
                    "text" => "نساء",
                    "callback_data" => "1"
                ],

            ]

        ],
    ]);

    if ($userMessage == "/reciter") {
        $result = mysqli_query($con, "SELECT * FROM `reciter` where `show` = 1");
        while ($row = mysqli_fetch_assoc($result)) {
            echo $row['text'];
        }
        $markup = json_encode([
            "keyboard" => [
                [

                    ["text" => "ماهر المعيقلي"],
                    ["text" => "سعد الغامدی"],
                    ["text" => "ياسر الدوسري"],
                    ["text" => "احمد العجمی"],
                ],
                [
                    ["text" => "ناصر القطامي"],
                    ["text" => "ایمن سوید"],
                    ["text" => "مشاري العفاسي"]
                ],
                [
                    ["text" => "محمود الحصري (تەعلیمی)"],
                    ["text" => "محمود الحصري (تەجویدی)"],
                    ["text" => "عبد الباسط عبد الصمد"],
                ],
                [
                    ["text" => "محمد المنشاوي (تەجویدی)"],
                    ["text" => "محمد المنشاوي"],
                ],
                [
                    ["text" => "ماهر المعيقلي (تەجویدی 2020)"],
                    ["text" => "عبدالرحمن السديس"]
                ]
            ],
        ]);

        $parameters = array(
            "chat_id" => $userChatId,
            "text" => "قورئان خوێن هەڵبژێرە",
            "parse_mode" => "html",
            'reply_markup' => $surahSelect
        );
        send("sendMessage", $parameters);
    }

    if ($userMessage <= 604 && $userMessage >= 1) {
        $page = str_pad($userMessage, 3, '0', STR_PAD_LEFT);
        $reciter = 1;
        $result = mysqli_query($con, "SELECT * FROM `users_reciter` where `chat_id` = '$userChatId'");
        while ($row = mysqli_fetch_assoc($result)) {
            $reciter = $row['reciter'];
        }
        $parameters = array(
            "chat_id" => $userChatId,
            "text" => "",
            "parse_mode" => "html",
            "audio" => "https://basknet.net/muhammad/002282.mp3"
        );
        send("sendAudio", $parameters);
        echo "requested";
    }

    $result = mysqli_query($con, "SELECT * FROM `reciter` where `text` = '" . $userMessage . "'");
    if (mysqli_num_rows($result)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $result1 = mysqli_query($con, "UPDATE `users_reciter` SET `reciter` = '" . $row['path'] . "' WHERE `chat_id` = $userChatId");
            if ($result1 == 1)
                echo "reciter Updated!";
        }
    }
}


function send($method, $data)
{
    $BOT_TOKEN = "5397498179:AAGEOwB8yKMiHtvolbmmkZ6ylF5ZKzQkb0o";
    $url = "https://api.telegram.org/bot$BOT_TOKEN/$method";

    if (!$curld = curl_init()) {
        exit;
    }
    curl_setopt($curld, CURLOPT_POST, true);
    curl_setopt($curld, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curld, CURLOPT_URL, $url);
    curl_setopt($curld, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($curld);
    curl_close($curld);
    return $url;
}

?>