<?php
// ১. সেশন শুরু
session_start();

// ২. কনফিগ ফাইল থেকে বর্তমান সেশন চাবি (Session Key) চেক করা
$config_file = 'config.json';
$configData = json_decode(file_get_contents($config_file), true);
$server_key = $configData['session_key'] ?? 'default';

// ৩. সিকিউরিটি চেক: যদি সেশন না থাকে অথবা অ্যাডমিন সবাইকে লগআউট করে দেয়
if (!isset($_SESSION['auth_user']) || $_SESSION['auth_user'] !== "YES_LOGGED_IN" || $_SESSION['current_key'] !== $server_key) {
    session_destroy(); // পুরনো সেশন মুছে ফেলা
    header("Location: index.php"); // লগইন পেজে পাঠিয়ে দেওয়া
    exit();
}

// ৪. ভিজিটর ট্র্যাকিং (অ্যাডমিন প্যানেলের জন্য)
$stats_file = 'stats.json';
if (file_exists($stats_file)) {
    $stats = json_decode(file_get_contents($stats_file), true);
    $stats['total_visits'] = ($stats['total_visits'] ?? 0) + 1;
    file_put_contents($stats_file, json_encode($stats));
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>𝐅𝐅 𝐄𝐌𝐎𝐓𝐄 𝐏𝐋𝐀𝐘</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --bg-color: #0d0d0d; --card-bg: #1a1a1a; --text-color: #ffffff; --accent-blue: #00d2ff; }
        body { background-color: var(--bg-color); color: var(--text-color); font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; display: flex; flex-direction: column; align-items: center; overflow-x: hidden; }
        @keyframes rainbow { 0% { color: #ff0000; } 25% { color: #ffcc00; } 50% { color: #00ff00; } 75% { color: #00d2ff; } 100% { color: #ff0000; } }
        .header { width: 100%; height: 60px; background: #161616; display: flex; justify-content: space-between; align-items: center; padding: 0 15px; box-sizing: border-box; position: sticky; top: 0; z-index: 100; border-bottom: 1px solid #333; }
        .brand-name { font-size: 18px; font-weight: bold; animation: rainbow 3s linear infinite; }
        .header-right { display: flex; align-items: center; gap: 12px; position: relative; }
        .join-btn { background: #0088cc; color: white; border: none; padding: 6px 15px; border-radius: 20px; text-decoration: none; font-weight: bold; font-size: 13px; display: flex; align-items: center; gap: 5px; }
        .settings-btn { color: white; font-size: 18px; cursor: pointer; transition: 0.3s; }
        .settings-dropdown { position: absolute; top: 50px; right: 0; width: 160px; background: #1a1a1a; border: 1px solid #333; border-radius: 10px; display: none; flex-direction: column; padding: 12px; z-index: 1000; box-shadow: 0 8px 16px rgba(0,0,0,0.5); }
        .settings-dropdown.active { display: flex; }
        .setting-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; font-size: 13px; }
        .logout-link { color: #ff4d4d; text-decoration: none; font-weight: bold; font-size: 13px; text-align: center; border-top: 1px solid #333; padding-top: 10px; display: block; width: 100%; }
        .tgl { position: relative; display: inline-block; width: 36px; height: 18px; }
        .tgl input { opacity: 0; width: 0; height: 0; }
        .tgl-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #444; transition: .4s; border-radius: 18px; }
        .tgl-slider:before { position: absolute; content: ""; height: 12px; width: 12px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .tgl-slider { background-color: var(--accent-blue); }
        input:checked + .tgl-slider:before { transform: translateX(18px); }
        .container { width: 95%; max-width: 450px; margin-top: 15px; padding-bottom: 110px; }
        .mode-container { background: #1a1a1a; padding: 12px 15px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border: 1px solid #333; }
        .mode-label { font-size: 14px; font-weight: bold; color: #bbb; }
        .switch { position: relative; display: inline-block; width: 50px; height: 24px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #444; transition: .4s; border-radius: 24px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #00a8e8; }
        input:checked + .slider:before { transform: translateX(26px); }
        .input-field { width: 100%; background: #222; border: 1px solid #444; padding: 12px; border-radius: 8px; color: white; margin-bottom: 10px; box-sizing: border-box; outline: none; }
        .input-field:disabled { opacity: 0.5; cursor: not-allowed; }
        .uid-input-group { display: flex; gap: 8px; margin-bottom: 10px; }
        .add-uid-btn { background: #333; color: white; border: 1px solid #555; padding: 0 15px; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .uid-display-area { background: #000; color: #00ff00; padding: 10px; border-radius: 8px; margin-bottom: 12px; font-size: 12px; border: 1px dashed #444; min-height: 20px; display: flex; flex-wrap: wrap; gap: 5px; }
        .uid-tag { display: inline-flex; align-items: center; background: #333; color: #00ff00; padding: 4px 10px; border-radius: 5px; font-size: 11px; border: 1px solid #444; }
        .remove-uid { color: #ff4d4d; margin-left: 8px; cursor: pointer; font-weight: bold; font-size: 14px; }
        .play-btn { width: 100%; background: #00a8e8; color: white; border: none; padding: 15px; border-radius: 8px; font-weight: bold; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; }
        .select-title { text-align: center; color: var(--accent-blue); margin: 25px 0 15px; font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .emote-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 25px; }
        .emote-card { background: var(--card-bg); border-radius: 12px; padding: 8px; text-align: center; border: 1px solid #333; cursor: pointer; transition: all 0.3s ease; display: flex; flex-direction: column; align-items: center; position: relative; overflow: hidden; }
        .emote-card.selected { transform: scale(1.05); border-color: var(--accent-blue); background: #252525; }
        .emote-card img { width: 100%; aspect-ratio: 1/1; border-radius: 8px; object-fit: cover; margin-bottom: 5px; background: #000; }
        .emote-name { font-size: 10px; color: #bbb; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%; }
        .playing-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.85); display: none; flex-direction: column; align-items: center; justify-content: center; z-index: 10; border-radius: 12px; }
        .loader-circle { width: 20px; height: 20px; border: 2px solid rgba(255,255,255,0.2); border-top: 2px solid var(--accent-blue); border-radius: 50%; animation: spin 0.8s linear infinite; margin-bottom: 5px; }
        .playing-txt { color: var(--accent-blue); font-size: 9px; font-weight: bold; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .ping-footer { position: fixed; bottom: 15px; padding: 6px 18px; border-radius: 25px; display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: bold; z-index: 1000; }
        .ping-green { background: rgba(0, 255, 0, 0.8); border: 1.5px solid #00ff00; color: #000; }
        .ping-red { background: rgba(255, 0, 0, 0.8); border: 1.5px solid #ff0000; color: #fff; }
        .ping-dot { width: 10px; height: 10px; border-radius: 50%; animation: blink 1s infinite; }
        #response { background: #000; color: #00ff00; padding: 12px; border-radius: 8px; font-size: 11px; margin-top: 15px; display: none; text-align: center; }
    </style>
</head>
<body>

<audio id="clickSound" preload="auto">
    <source src="https://assets.mixkit.co/active_storage/sfx/2569/2569-preview.mp3" type="audio/mpeg">
</audio>

<div class="header">
    <div class="brand-name">𝐅𝐅 𝐄𝐌𝐎𝐓𝐄 𝐏𝐋𝐀𝐘</div>
    <div class="header-right">
        <a href="https://t.me/your_link" target="_blank" class="join-btn"><i class="fab fa-telegram"></i> JOIN</a>
        <i class="fas fa-cog settings-btn" id="settingsBtn"></i>
        <div class="settings-dropdown" id="settingsDropdown">
            <div class="setting-row"><span>Sound Effect</span><label class="tgl"><input type="checkbox" id="soundSwitch" checked><span class="tgl-slider"></span></label></div>
            <a href="index.php" class="logout-link">LOGOUT</a>
        </div>
    </div>
</div>

<div class="container">
    <div class="mode-container">
        <span class="mode-label">𝐎𝐍𝐄 𝐂𝐋𝐈𝐂𝐊 𝐌𝐎𝐃𝐄</span>
        <label class="switch"><input type="checkbox" id="oneClickSwitch" onchange="toggleMode()"><span class="slider"></span></label>
    </div>

    <input type="text" id="tc" class="input-field" placeholder="Team Code">
    <input type="text" id="emtid" class="input-field" placeholder="Emote ID" readonly>
    <div class="uid-input-group">
        <input type="text" id="uid-input" class="input-field" style="margin-bottom:0;" placeholder="Enter Player UID">
        <button id="addUidBtn" class="add-uid-btn" onclick="addUID()">+ Add</button>
    </div>
    <div id="uid-list-show" class="uid-display-area">𝐏𝐋𝐀𝐘𝐄𝐑 𝐔𝐈𝐃s: (None)</div>
    <button class="play-btn" onclick="sendRequest()">𝐏𝐋𝐀𝐘 𝐄𝐌𝐎𝐓𝐄</button>
    <div id="response"></div>

    <div class="select-title">𝐒𝐄𝐋𝐄𝐂𝐓 𝐄𝐌𝐎𝐓𝐄 𝐀𝐍𝐃 𝐏𝐋𝐀𝐘 𝐄𝐌𝐎𝐓𝐄</div>

    <div class="emote-grid">
        <div class="emote-card" onclick="handleEmoteClick(this, '909000075')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://i.postimg.cc/05S7zSZc/909000075.png"><div class="emote-name">Cobra Rising</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000063')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://i.postimg.cc/J4PVZs49/909000063.png"><div class="emote-name">Draco Summon</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909041005')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909041005.png"><div class="emote-name">Diz My Popblaster</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000068')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://i.postimg.cc/44q3DFTD/909000068.png"><div class="emote-name">Feeding Time</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000085')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://i.postimg.cc/d34gKf7J/909000085.png"><div class="emote-name">Born of Light</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000081')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://i.postimg.cc/9QjnzT9J/909000081.png"><div class="emote-name">Draco Soul</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000090')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://i.postimg.cc/Pr0DTzgd/909000090.png"><div class="emote-name">Blood Wraith</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000098')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://i.postimg.cc/13Jf4GTy/909000098.png"><div class="emote-name">The Chosen Victor</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909033001')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://i.postimg.cc/76MvYVsC/909033001.png"><div class="emote-name">Fire born</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909033002')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://i.postimg.cc/j5GcWw2C/909033002.png"><div class="emote-name">Golden Feather</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909035007')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://i.postimg.cc/sXR1v3zN/909035007.png"><div class="emote-name">Weapon Magician</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909035012')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://i.postimg.cc/pX9xbgbm/909035012.png"><div class="emote-name">Howlers Rage</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909037011')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://i.postimg.cc/MpKLJBQx/909037011.png"><div class="emote-name">Drachen Tear</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909038012')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://i.postimg.cc/x1JKmTrS/909038012.png"><div class="emote-name">Achiever Flip</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051003')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://i.postimg.cc/63367qSL/909051003.png"><div class="emote-name">Rain of Spikes</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909038010')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909038010.png"><div class="emote-name">Cinder Summon</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909039011')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909039011.png"><div class="emote-name">Crimson Doom</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909040010')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909040010.png"><div class="emote-name">Chromasonic Shot</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909042008')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909042008.png"><div class="emote-name">Real Tiger</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909045001')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909045001.png"><div class="emote-name">Cyclone Arrival</div></div>

        <div class="emote-card" onclick="handleEmoteClick(this, '909049010')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909049010.png"><div class="emote-name">P90 Surfer</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909052001')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909052001.png"><div class="emote-name">New Emote 1</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909052002')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909052002.png"><div class="emote-name">New Emote 2</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909052003')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909052003.png"><div class="emote-name">New Emote 3</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909052004')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909052004.png"><div class="emote-name">New Emote 4</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909052005')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909052005.png"><div class="emote-name">New Emote 5</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909052006')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909052006.png"><div class="emote-name">New Emote 6</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909052007')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909052007.png"><div class="emote-name">New Emote 7</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909052008')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909052008.png"><div class="emote-name">New Emote 8</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909052009')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909052009.png"><div class="emote-name">New Emote 9</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909052010')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909052010.png"><div class="emote-name">New Emote 10</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909052011')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909052011.png"><div class="emote-name">New Emote 11</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909052012')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909052012.png"><div class="emote-name">New Emote 12</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909052013')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909052013.png"><div class="emote-name">New Emote 13</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909052014')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909052014.png"><div class="emote-name">New Emote 14</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909052015')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909052015.png"><div class="emote-name">New Emote 15</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051001')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051001.png"><div class="emote-name">Prismatic Flight</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051004')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051004.png"><div class="emote-name">Shower Time</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051005')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051005.png"><div class="emote-name">Boss Energy</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051006')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051006.png"><div class="emote-name">Double Holster</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051007')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051007.png"><div class="emote-name">Finger Guns</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051008')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051008.png"><div class="emote-name">Shivering</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051009')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051009.png"><div class="emote-name">Triple Shush</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051010')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051010.png"><div class="emote-name">On Motorbike</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051011')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051011.png"><div class="emote-name">Twisted Stare</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051012')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051012.png"><div class="emote-name">Celestial Shot</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051013')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051013.png"><div class="emote-name">Red Petals</div></div>

        <div class="emote-card" onclick="handleEmoteClick(this, '909051014')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051014.png"><div class="emote-name">Puffer Ride</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051015')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051015.png"><div class="emote-name">Cant Stop Laughing</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051016')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051016.png"><div class="emote-name">Crowned Glory Emote</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051017')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051017.png"><div class="emote-name">Choppy Co-op</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051018')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051018.png"><div class="emote-name">Gather Around</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051021')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051021.png"><div class="emote-name">Passinho</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051022')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051022.png"><div class="emote-name">All Good, Boss!</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909051023')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909051023.png"><div class="emote-name">67</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909042007')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909042007.png"><div class="emote-name">100 Gloo Sculpture</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000002')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909000002.png"><div class="emote-name">LOL</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000006')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909000006.png"><div class="emote-name">Chicken</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000008')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909000008.png"><div class="emote-name">Shoot Dance</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000011')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909000011.png"><div class="emote-name">Mummy Dance</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000012')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909000012.png"><div class="emote-name">Push-up</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000014')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909000014.png"><div class="emote-name">FFWC Throne</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000015')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909000015.png"><div class="emote-name">Dragon Fist</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000017')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909000017.png"><div class="emote-name">Jaguar Dance</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000020')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909000020.png"><div class="emote-name">Devils Move</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000028')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909000028.png"><div class="emote-name">Crane Kick</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000034')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909000034.png"><div class="emote-name">Pirates Flag</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000045')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909000045.png"><div class="emote-name">I heart you</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000043')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909000043.png"><div class="emote-name">Aim, fire!</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000048')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909000048.png"><div class="emote-name">Why? Oh Why?</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909036008')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909036008.png"><div class="emote-name">Skateboard Swag</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909036009')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909036009.png"><div class="emote-name">Phantom Tamer</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909036010')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909036010.png"><div class="emote-name">The Signal</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909037001')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909037001.png"><div class="emote-name">Reindeer Float</div></div>
         <div class="emote-card" onclick="handleEmoteClick(this, '909037002')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909037002.png"><div class="emote-name">Bamboo Dance</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909037004')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909037004.png"><div class="emote-name">Trophy Grab</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909037006')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909037006.png"><div class="emote-name">Yum</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909037009')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909037009.png"><div class="emote-name">Neon Sign</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909037010')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909037010.png"><div class="emote-name">Beast Tease</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909038001')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909038001.png"><div class="emote-name">The Influencer</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909038005')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909038005.png"><div class="emote-name">Angry Walk</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909038006')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909038006.png"><div class="emote-name">Make Some Noise</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909038008')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909038008.png"><div class="emote-name">Croco Hooray</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909038011')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909038011.png"><div class="emote-name">Shall We Dance?</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909039009')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909039009.png"><div class="emote-name">Grace On Wheels</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909040001')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909040001.png"><div class="emote-name">The Chromatic Finish</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909040002')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909040002.png"><div class="emote-name">Smash the Feather</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909039007')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909039007.png"><div class="emote-name">Grenade Magic</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909040004')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909040004.png"><div class="emote-name">Fishing for Wisdom</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909040005')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909040005.png"><div class="emote-name">Chromatic Pop Dance</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909040006')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909040006.png"><div class="emote-name">Chroma Twist Twist</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909040008')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909040008.png"><div class="emote-name">Birth of Justice</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909000010')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909000010.png"><div class="emote-name">Love Flowers</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909040014')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909040014.png"><div class="emote-name">Helicopter Shot</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909041004')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909041004.png"><div class="emote-name">Flying Ink Sword</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909041006')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909041006.png"><div class="emote-name">Dance Puppet Dance!</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909041009')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909041009.png"><div class="emote-name">Feel the Electricity</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909041010')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909041010.png"><div class="emote-name">Whac-A-Cotton</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909041013')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909041013.png"><div class="emote-name">CS-Ranked Grandmaster Emote</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909041014')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909041014.png"><div class="emote-name">Monster Clubbing</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909042001')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909042001.png"><div class="emote-name">Stir-Fry Frostfire</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909042002')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909042002.png"><div class="emote-name">Money Rain</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909042006')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909042006.png"><div class="emote-name">Excellent Service</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909042017')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909042017.png"><div class="emote-name">Free Fire Toiletman</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909042005')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909042005.png"><div class="emote-name">This Way</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909042012')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909042012.png"><div class="emote-name">Lamborghini Ride</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909043005')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909043005.png"><div class="emote-name">Huge Feast</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909043009')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909043009.png"><div class="emote-name">Speed Summon</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909044002')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909044002.png"><div class="emote-name">The Unicyclist</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909044004')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909044004.png"><div class="emote-name">Happy Lamb Shouldering</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909045003')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909045003.png"><div class="emote-name">Giddy Up!</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909045009')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909045009.png"><div class="emote-name">Moonwalk</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909045015')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909045015.png"><div class="emote-name">Floating Meditation</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909045016')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909045016.png"><div class="emote-name">Naatu Naatu</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909045017')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909045017.png"><div class="emote-name">Champions Walk</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909046001')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909046001.png"><div class="emote-name">Aura Boarder</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909046010')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909046010.png"><div class="emote-name">Max Firepower</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909046015')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909046015.png"><div class="emote-name">Isatis Spatial Awareness</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909047001')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909047001.png"><div class="emote-name">I Wont Bow Down</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909047005')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909047005.png"><div class="emote-name">Slippery Throne</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909047007')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909047007.png"><div class="emote-name">Love Me, Love Me Not</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909047009')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909047009.png"><div class="emote-name">The Thinker</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909046016')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909046016.png"><div class="emote-name">Nagis Trapping</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909047010')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909047010.png"><div class="emote-name">Match Countdown</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909047015')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909047015.png"><div class="emote-name">Rasengan</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909047016')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909047016.png"><div class="emote-name">A Thousand Years of Death</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909047017')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909047017.png"><div class="emote-name">Ninja Sign</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909047018')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909047018.png"><div class="emote-name">Ninja Run</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909047019')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909047019.png"><div class="emote-name">Clone Jutsu</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909048002')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909048002.png"><div class="emote-name">Midnight Peruse</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909048003')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909048003.png"><div class="emote-name">Guitar Groove</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909048004')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909048004.png"><div class="emote-name">Keyboard Player</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909048005')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909048005.png"><div class="emote-name">On Drums</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909048006')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909048006.png"><div class="emote-name">Chac-Chac</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909048007')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909048007.png"><div class="emote-name">Pillow Fight</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909048008')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909048008.png"><div class="emote-name">Target Practice</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909048011')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909048011.png"><div class="emote-name">Flag Summon</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909048016')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909048016.png"><div class="emote-name">Half-Time Chilling</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909048017')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909048017.png"><div class="emote-name">Throw-In</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909048018')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909048018.png"><div class="emote-name">Bailalo Rocky</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909049006')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909049006.png"><div class="emote-name">Creation Days</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909049001')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909049001.png"><div class="emote-name">Nailoong Time!</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909049002')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909049002.png"><div class="emote-name">Hand Raise</div></div>
        <div class="emote-card" onclick="handleEmoteClick(this, '909049003')"><div class="playing-overlay"><div class="loader-circle"></div><div class="playing-txt">PLAYING...</div></div><img src="https://cdn.jsdelivr.net/gh/ShahGCreator/icon@main/PNG/909049003.png"><div class="emote-name">Kick It Up</div></div>

    </div>
</div>

<div id="ping-box" class="ping-footer ping-green">
    <div class="ping-dot"></div>
    <span>SERVER PING: <span id="ping-count">2500</span> ms</span>
</div>

<script>
    let addedUIDs = [];
    const clickSound = document.getElementById('clickSound');
    const settingsBtn = document.getElementById('settingsBtn');
    const settingsDropdown = document.getElementById('settingsDropdown');

    settingsBtn.addEventListener('click', () => settingsDropdown.classList.toggle('active'));

    window.addEventListener('click', (e) => {
        if (!settingsBtn.contains(e.target) && !settingsDropdown.contains(e.target)) {
            settingsDropdown.classList.remove('active');
        }
    });

    function toggleMode() {
        const isOneClick = document.getElementById('oneClickSwitch').checked;
        document.getElementById('tc').disabled = isOneClick;
        document.getElementById('uid-input').disabled = isOneClick;
        document.getElementById('addUidBtn').disabled = isOneClick;
    }

    function addUID() {
        const input = document.getElementById('uid-input');
        const val = input.value.trim();
        if (val !== "" && !addedUIDs.includes(val)) {
            addedUIDs.push(val);
            renderUIDs();
            input.value = "";
        }
    }

    function removeUID(index) {
        addedUIDs.splice(index, 1);
        renderUIDs();
    }

    function renderUIDs() {
        const displayArea = document.getElementById('uid-list-show');
        if (addedUIDs.length === 0) {
            displayArea.innerHTML = "Target UIDs: (None)";
            return;
        }
        displayArea.innerHTML = "";
        addedUIDs.forEach((uid, index) => {
            displayArea.innerHTML += `<span class="uid-tag">${uid} <span class="remove-uid" onclick="removeUID(${index})">×</span></span>`;
        });
    }

    function handleEmoteClick(element, id) {
        if(!id) return;
        document.querySelectorAll('.emote-card').forEach(card => card.classList.remove('selected'));
        element.classList.add('selected');
        document.getElementById('emtid').value = id;

        const isOneClick = document.getElementById('oneClickSwitch').checked;
        const isSoundOn = document.getElementById('soundSwitch').checked;

        if(isOneClick) {
            if(isSoundOn) { clickSound.currentTime = 0; clickSound.play().catch(() => {}); }
            const overlay = element.querySelector('.playing-overlay');
            if(overlay) {
                overlay.style.display = 'flex';
                setTimeout(() => { overlay.style.display = 'none'; }, 2000);
            }
            sendRequest();
        }
    }

    async function sendRequest() {
        const tc = document.getElementById('tc').value;
        const emtid = document.getElementById('emtid').value;
        const resDiv = document.getElementById('response');
        if(!document.getElementById('oneClickSwitch').checked && (!tc || addedUIDs.length === 0)) return;
        if(!emtid) return;

        resDiv.style.display = "block";
        resDiv.innerText = "𝐄𝐦𝐨𝐭𝐞 𝐒𝐞𝐧𝐝𝐢𝐧𝐠...";
        try {
            await fetch(`https://raigenrohan.2host.top/emotebdapi.php//play?tc=${tc}&uid=${addedUIDs.join(",")}&emtid=${emtid}`);
            resDiv.innerText = "𝐄𝐌𝐎𝐓𝐄 𝐒𝐄𝐍𝐓 𝐒𝐔𝐂𝐂𝐄𝐒𝐒𝐅𝐔𝐋𝐋𝐘✅";
        } catch (err) {
            resDiv.innerText = "Emote Not Sent Error ❌";
        }
    }

    setInterval(() => {
        const ping = Math.floor(Math.random() * (6000 - 500) + 500);
        const pb = document.getElementById('ping-box');
        document.getElementById('ping-count').innerText = ping;
        pb.className = ping < 3000 ? 'ping-footer ping-green' : 'ping-footer ping-red';
    }, 2000);
</script>

</body>
</html>