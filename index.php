<?php
session_start();
$config_file = 'config.json';
$error = "";

// ১. কনফিগ ফাইল থেকে ডাটা লোড করা
if (file_exists($config_file)) {
    $configData = json_decode(file_get_contents($config_file), true);
} else {
    $configData = [
        "password" => "1234",
        "video_url" => "https://www.youtube.com/embed/tgbNymZ7vqY",
        "telegram_url" => "https://t.me/yourusername",
        "notice_text" => "Welcome to our premium system! Contact admin for access.",
        "session_key" => "default123"
    ];
}

/** * ইউটিউব লিঙ্ক ফিক্সার (যাতে সাদা হয়ে না থাকে)
 */
$yt_url = $configData['video_url'];
if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $yt_url, $match)) {
    $final_video_url = "https://www.youtube.com/embed/" . $match[1];
} else {
    $final_video_url = $yt_url;
}

// লগইন লজিক
if (isset($_POST['login'])) {
    if ($_POST['password'] === $configData['password']) {
        $_SESSION['auth_user'] = "YES_LOGGED_IN";
        $_SESSION['current_key'] = $configData['session_key'] ?? 'default';
        header("Location: emote.php");
        exit();
    } else {
        $error = "Incorrect Password! Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DYNAMIC PREMIER SYSTEM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@500;800&family=Hind+Siliguri:wght@400;700&display=swap');
        
        body {
            background: #020617;
            font-family: 'Hind Siliguri', sans-serif;
            color: #fff;
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh; margin: 0; overflow-x: hidden;
        }

        /* Notice Animation */
        .notice-container {
            background: rgba(0, 242, 255, 0.1);
            border: 1px solid rgba(0, 242, 255, 0.3);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
            white-space: nowrap;
        }
        .notice-text {
            display: inline-block;
            padding: 10px;
            font-weight: bold;
            color: #00f2ff;
            animation: marquee 15s linear infinite;
        }
        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }

        .main-card {
            width: 90%; max-width: 400px;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 30px; border-radius: 30px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }

        .video-box {
            width: 100%; height: 200px;
            background: #000; border-radius: 15px;
            overflow: hidden; margin-bottom: 25px;
            border: 2px solid #1e293b;
        }

        .input-style {
            width: 100%; background: rgba(0,0,0,0.4);
            border: 1px solid rgba(0, 242, 255, 0.2);
            padding: 15px 20px; border-radius: 15px;
            color: #fff; text-align: center; font-size: 18px;
            outline: none; transition: 0.3s;
        }
        .input-style:focus { border-color: #00f2ff; box-shadow: 0 0 15px rgba(0, 242, 255, 0.2); }

        .tg-button {
            display: flex; align-items: center; justify-content: center;
            gap: 10px; background: #229ED9;
            padding: 12px; border-radius: 15px;
            color: #fff; font-weight: bold; margin-top: 15px;
            text-decoration: none; transition: 0.3s;
        }
        .tg-button:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(34, 158, 217, 0.3); }

        .login-btn {
            width: 100%; background: linear-gradient(90deg, #00f2ff, #0066ff);
            padding: 15px; border-radius: 15px; color: #000;
            font-weight: bold; margin-top: 20px; border: none;
            cursor: pointer; text-transform: uppercase;
        }
    </style>
</head>
<body>

    <div class="main-card">
        <div class="text-center mb-4">
            <h3 style="font-family: 'Orbitron'; letter-spacing: 2px;" class="text-xs text-cyan-400 mb-2 uppercase">Tutorial Guide</h3>
            <div class="video-box">
                <iframe width="100%" height="100%" src="<?php echo $final_video_url; ?>" frameborder="0" allowfullscreen></iframe>
            </div>
        </div>

        <div class="notice-container">
            <div class="notice-text">
                <i class="fas fa-bullhorn"></i> <?php echo $configData['notice_text']; ?>
            </div>
        </div>

        <form action="" method="POST">
            <?php if($error): ?>
                <p class="text-red-500 text-center text-sm mb-3"><?php echo $error; ?></p>
            <?php endif; ?>
            
            <input type="password" name="password" class="input-style" placeholder="Enter Access Password" required>
            
            <button type="submit" name="login" class="login-btn">
                Initialize Access <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </form>

        <a href="<?php echo $configData['telegram_url']; ?>" target="_blank" class="tg-button">
            <i class="fab fa-telegram-plane text-xl"></i> Telegram Contact
        </a>

        <p class="text-center text-[10px] text-slate-500 mt-6 tracking-[3px]">POWERED BY MARUF SYSTEM</p>
    </div>

</body>
</html>