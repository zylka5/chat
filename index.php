<?php
ini_set('display_errors', 'On');


require 'vendor/autoload.php'; // załaduj bibliotekę GuzzleHttp
use GuzzleHttp\Client;

// Tworzenie klienta HTTP
$client = new Client([
    'base_uri' => 'https://api.openai.com/v1/',
    'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . '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'
    ]
]);

// Funkcja do pobierania historii
function getChatHistory() {
    if (file_exists('chat_history.json')) {
        $history = json_decode(file_get_contents('chat_history.json'), true);
        if (is_array($history)) {
            return $history;
        }
    }
    return [];
}

// Funkcja do dodawania wpisu do historii
function addChatHistory($message) {
    $history = getChatHistory();
    $history[] = $message;
    file_put_contents('chat_history.json', json_encode($history));
}

// Pobieranie historii
$chatHistory = getChatHistory();

// Wyświetlanie historii
echo "<h2>Historia</h2>";
echo "<ul>";
foreach ($chatHistory as $entry) {
    echo "<li>{$entry['author']}: {$entry['message']}</li>";
}
echo "</ul>";

// Przetwarzanie formularza
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'];

    // Wysyłanie zapytania do OpenAI API
    $response = $client->post('engines/davinci/completions', [
        'json' => [
            'prompt' => "User: $message\nAI:",
            'temperature' => 0.7,
            'max_tokens' => 60,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0
        ]
    ]);

    // Przetwarzanie odpowiedzi
    $data = json_decode($response->getBody(), true);
    $generated = $data['choices'][0]['text'];

    // Dodawanie wpisu do historii
    addChatHistory(['author' => 'User', 'message' => $message]);
    addChatHistory(['author' => 'AI', 'message' => $generated]);

    // Wyświetlanie wygenerowanej odpowiedzi
    echo "<p><strong>AI:</strong> $generated</p>";
}
?>

<!-- Formularz -->
<h2>Czat</h2>
<form method="post">
    <p><label for="message">Wiadomość:</label></p>
    <p><textarea name="message" id="message" rows="5"></textarea></p>
    <p><button type="submit">Wyślij</button></p>
</form>
