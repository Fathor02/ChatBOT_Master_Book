<?php
$responses = [];
$mode = $_POST['mode'] ?? 'single';
$userInput = $_POST['value'] ?? '';

$models = [
    'nex-agi/deepseek-v3.1-nex-n1:free' => 'DeepSeek V3.1 (NEX)',
    'arcee-ai/trinity-mini:free'        => 'Arcee AI Trinity Mini'
];

$apiKey = "isi_API_KEY_KAMU";
$url = "https://openrouter.ai/api/v1/chat/completions";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($userInput)) {
    $selectedModels = ($mode === 'single')
        ? [$_POST['model']]
        : array_keys($models);

    foreach ($selectedModels as $modelId) {
        $payload = json_encode([
            "model" => $modelId,
            "messages" => [
                [
                    "role" => "system",
                    "content" => "Kamu adalah chatbot ahli buku."
                ],
                [
                    "role" => "user",
                    "content" => $userInput
                ]
            ]
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer $apiKey"
            ],
            CURLOPT_RETURNTRANSFER => true
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $responses[$modelId] =
            json_decode($response, true)['choices'][0]['message']['content'] ?? 'No response';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Chatbot Ahli Buku</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg, #064e3b, #0f766e);
    min-height: 100vh;
    overflow-x: hidden;
}

/* ========== BUBBLE BACKGROUND (INTERNAL IMAGE) ========== */
.bubble-bg {
    position: fixed;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    overflow: hidden;
}

.bubble-bg span {
    position: absolute;
    bottom: -150px;
    width: 90px;
    height: 90px;
    background-image: url('image1.png');
    background-size: contain;
    background-repeat: no-repeat;
    opacity: 0.5;
    animation: bubbleUp linear infinite;
}

@keyframes bubbleUp {
    from {
        transform: translateY(0) scale(0.6);
        opacity: 0;
    }
    20% { opacity: 0.3; }
    to {
        transform: translateY(-120vh) scale(1.4);
        opacity: 0;
    }
}

/* variasi bubble */
.bubble-bg span:nth-child(1) { left: 5%;  animation-duration: 18s; }
.bubble-bg span:nth-child(2) { left: 15%; animation-duration: 25s; width: 45px; height: 45px; }
.bubble-bg span:nth-child(3) { left: 30%; animation-duration: 20s; }
.bubble-bg span:nth-child(4) { left: 45%; animation-duration: 30s; width: 90px; height: 90px; }
.bubble-bg span:nth-child(5) { left: 60%; animation-duration: 22s; }
.bubble-bg span:nth-child(6) { left: 75%; animation-duration: 28s; }
.bubble-bg span:nth-child(7) { left: 85%; animation-duration: 19s; }

/* ========== FLOATING BOOK ========== */
.floating-books span {
    position: fixed;
    bottom: -80px;
    font-size: 42px;
    opacity: 0.25;
    animation: bookUp linear infinite;
    z-index: 1;
    pointer-events: none;
}

@keyframes bookUp {
    to {
        transform: translateY(-120vh) translateX(80px) rotate(12deg);
        opacity: 0;
    }
}

.floating-books span:nth-child(1) { left: 15%; animation-duration: 22s; }
.floating-books span:nth-child(2) { left: 45%; animation-duration: 28s; }
.floating-books span:nth-child(3) { left: 75%; animation-duration: 24s; }

/* ========== UI ========== */
.chat-wrapper {
    position: relative;
    z-index: 2;
    max-width: 980px;
    margin: auto;
}

.chat-card {
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 25px 60px rgba(0,0,0,.3);
}

.chat-header {
    background: linear-gradient(135deg, #064e3b, #047857);
    color: #fff;
    padding: 25px;
    text-align: center;
}

.answer-card {
    border-radius: 15px;
    box-shadow: 0 15px 30px rgba(0,0,0,.2);
}

pre {
    white-space: pre-wrap;
    font-family: 'Segoe UI', sans-serif;
    font-size: 14px;
}
</style>
</head>

<body>

<!-- BACKGROUND BUBBLE -->
<div class="bubble-bg">
    <span></span><span></span><span></span><span></span>
    <span></span><span></span><span></span>
</div>

<!-- FLOATING BOOK -->
<div class="floating-books">
    <span>📘</span>
    <span>📚</span>
    <span>📖</span>
</div>

<div class="container py-5 chat-wrapper">

<div class="chat-card mb-4">
    <div class="chat-header">
        <h3>📚 Chatbot Master Book</h3>
        <small>AI Ringkasan & Rekomendasi Buku</small>
    </div>

    <div class="p-4">
        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="radio" name="mode" value="single" checked> Tunggal
                    <input type="radio" name="mode" value="compare" class="ms-3"> Bandingkan
                </div>
                <div class="col-md-6">
                    <select name="model" class="form-select">
                        <?php foreach ($models as $id => $label): ?>
                            <option value="<?= $id ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <input type="text" name="value" class="form-control mb-3"
                   placeholder="Contoh: ringkas buku filsafat untuk pemula" required>

            <button class="btn btn-success w-100 fw-bold">Kirim</button>
        </form>
    </div>
</div>

<?php if (!empty($responses)): ?>
<div class="row">
<?php foreach ($responses as $modelId => $text): ?>
<div class="col-md-6 mb-4">
    <div class="card answer-card">
        <div class="card-body">
            <strong><?= $models[$modelId] ?></strong>
            <pre><?= htmlspecialchars($text) ?></pre>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

</div>
</body>
</html>

