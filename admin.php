<?php
session_start();

// অ্যাডমিন প্যানেলের ডিফল্ট সেটিংস
$admin_pass = "Maruf2012";
$config_file = 'config.json';
$stats_file = 'stats.json';

// ১. লগআউট লজিক
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ২. লগইন চেক
if (isset($_POST['admin_login'])) {
    if ($_POST['admin_password'] === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $login_error = "Access Denied! Incorrect Password.";
    }
}

// ৩. ফাইল চেক ও অটো-জেনারেট
if (!file_exists($config_file)) {
    $default_config = [
        "password" => "1234",
        "video_url" => "https://www.youtube.com/embed/tgbNymZ7vqY",
        "telegram_url" => "https://t.me/yourusername",
        "notice_text" => "Welcome! Contact admin for support.",
        "session_key" => time()
    ];
    file_put_contents($config_file, json_encode($default_config, JSON_PRETTY_PRINT));
}
$configData = json_decode(file_get_contents($config_file), true);

if (!file_exists($stats_file)) {
    file_put_contents($stats_file, json_encode(["total_visits" => 0, "logins" => 0]));
}
$stats = json_decode(file_get_contents($stats_file), true);
$msg = "";

// ৪. মেইন কন্ট্রোল লজিক (শুধুমাত্র লগইন থাকলে কাজ করবে)
if (isset($_SESSION['admin_logged_in'])) {
    
    // সবকিছু একসাথে আপডেট করার মাস্টার বাটন
    if (isset($_POST['update_master'])) {
        $configData['password'] = htmlspecialchars($_POST['new_password']);
        $configData['video_url'] = htmlspecialchars($_POST['video_url']);
        $configData['telegram_url'] = htmlspecialchars($_POST['telegram_url']);
        $configData['notice_text'] = htmlspecialchars($_POST['notice_text']);
        
        file_put_contents($config_file, json_encode($configData, JSON_PRETTY_PRINT));
        $msg = "সফলভাবে সবকিছু আপডেট হয়েছে!";
    }

    // ফোর্স লগআউট (সেশন কি পরিবর্তন)
    if (isset($_POST['force_logout'])) {
        $configData['session_key'] = time(); 
        file_put_contents($config_file, json_encode($configData, JSON_PRETTY_PRINT));
        $msg = "সব ইউজারকে লগআউট করে দেওয়া হয়েছে!";
    }

    // স্ট্যাটাস রিসেট
    if (isset($_POST['reset_stats'])) {
        $stats = ["total_visits" => 0, "logins" => 0];
        file_put_contents($stats_file, json_encode($stats));
        $msg = "ভিজিটর ডাটা রিসেট হয়েছে!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MASTER DASHBOARD | MARUF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&family=Plus+Jakarta+Sans:wght@300;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #020617; color: #fff; }
        .glass-panel { background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 2rem; }
        .input-dark { background: rgba(2, 6, 23, 0.8); border: 1px solid rgba(255, 255, 255, 0.05); transition: 0.3s; }
        .input-dark:focus { border-color: #06b6d4; box-shadow: 0 0 15px rgba(6, 182, 212, 0.2); }
        .btn-gradient { background: linear-gradient(90deg, #06b6d4, #3b82f6); color: #000; font-weight: 800; transition: 0.4s; }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(6, 182, 212, 0.3); color: #fff; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-5">

    <?php if (!isset($_SESSION['admin_logged_in'])): ?>
    <div class="glass-panel w-full max-w-md p-10 text-center shadow-2xl">
        <div class="mb-6"><i class="fas fa-user-shield text-5xl text-cyan-500"></i></div>
        <h2 class="text-2xl font-black mb-8 tracking-widest uppercase">Admin Login</h2>
        <form method="POST" class="space-y-4">
            <input type="password" name="admin_password" class="w-full input-dark rounded-xl p-4 outline-none text-center" placeholder="Admin Key" required>
            <button type="submit" name="admin_login" class="w-full btn-gradient py-4 rounded-xl uppercase tracking-widest">Unlock Panel</button>
        </form>
    </div>

    <?php else: ?>
    <div class="glass-panel w-full max-w-4xl p-8 lg:p-12 shadow-2xl border-t border-cyan-500/20">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-5">
            <div>
                <h1 style="font-family: 'Orbitron';" class="text-3xl font-black text-cyan-400 tracking-tighter">MASTER CONSOLE</h1>
                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-[0.4em]">Neural Control Interface</p>
            </div>
            <a href="?logout=true" class="px-6 py-2 bg-red-500/10 text-red-500 border border-red-500/20 rounded-full hover:bg-red-500 hover:text-white transition-all font-bold text-xs uppercase">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10">
            <div class="bg-slate-900/50 p-6 rounded-2xl border border-white/5 flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-[10px] uppercase font-bold tracking-widest">Visitor Traffic</p>
                    <p class="text-4xl font-black"><?php echo number_format($stats['total_visits']); ?></p>
                </div>
                <i class="fas fa-chart-line text-3xl text-cyan-500/30"></i>
            </div>
            <div class="bg-slate-900/50 p-6 rounded-2xl border border-white/5 flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-[10px] uppercase font-bold tracking-widest">Successful Logins</p>
                    <p class="text-4xl font-black"><?php echo number_format($stats['logins']); ?></p>
                </div>
                <i class="fas fa-key text-3xl text-blue-500/30"></i>
            </div>
        </div>

        <?php if($msg): ?>
            <div class="mb-8 p-4 bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 text-center rounded-xl text-xs font-bold animate-pulse">
                <i class="fas fa-check-circle mr-2"></i><?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Client Access Key</label>
                    <div class="relative">
                        <i class="fas fa-unlock-alt absolute left-4 top-1/2 -translate-y-1/2 text-cyan-500"></i>
                        <input type="text" name="new_password" value="<?php echo $configData['password']; ?>" class="w-full input-dark p-4 pl-12 rounded-xl outline-none font-bold">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Telegram URL</label>
                    <div class="relative">
                        <i class="fab fa-telegram-plane absolute left-4 top-1/2 -translate-y-1/2 text-blue-400"></i>
                        <input type="text" name="telegram_url" value="<?php echo $configData['telegram_url']; ?>" class="w-full input-dark p-4 pl-12 rounded-xl outline-none">
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tutorial Video Link (YouTube Embed)</label>
                <div class="relative">
                    <i class="fab fa-youtube absolute left-4 top-1/2 -translate-y-1/2 text-red-500"></i>
                    <input type="text" name="video_url" value="<?php echo $configData['video_url']; ?>" class="w-full input-dark p-4 pl-12 rounded-xl outline-none" placeholder="https://www.youtube.com/embed/VIDEO_ID">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Scrolling Notice Text</label>
                <div class="relative">
                    <i class="fas fa-bullhorn absolute left-4 top-4 text-cyan-500"></i>
                    <textarea name="notice_text" rows="3" class="w-full input-dark p-4 pl-12 rounded-xl outline-none resize-none"><?php echo $configData['notice_text']; ?></textarea>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4">
                <button type="submit" name="update_master" class="flex-1 btn-gradient py-5 rounded-xl uppercase tracking-widest">
                    Apply All Changes
                </button>
                <button type="submit" name="force_logout" onclick="return confirm('সবাইকে লগআউট করতে চান?')" class="flex-1 bg-orange-500/10 border border-orange-500/30 text-orange-500 py-5 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-orange-600 hover:text-white transition-all">
                    Force Logout (All)
                </button>
                <button type="submit" name="reset_stats" onclick="return confirm('স্ট্যাটাস রিসেট করতে চান?')" class="w-16 bg-slate-900 border border-white/5 text-slate-600 rounded-xl hover:text-red-500 transition-all">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </form>

        <div class="mt-12 pt-8 border-t border-white/5 text-center">
            <p class="text-[9px] text-slate-600 font-bold tracking-[0.5em] uppercase">Control Panel v5.0 • Powered by Maruf</p>
        </div>
    </div>
    <?php endif; ?>

</body>
</html>