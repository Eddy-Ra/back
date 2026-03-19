<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
	public function prefillRelance(Request $request): JsonResponse
	{
		$client = new Client([
			'timeout' => 12,
		]);

		$webhookUrl = 'https://wfw.omega-connect.tech/webhook-test/53b181f1-7b25-4835-8509-c49f2db48b90';

		$email = '';
		$subject = '';
		$message = '';
		$raw = null;
		$lastError = null;

		// 1) Essayer POST JSON
        try {
			$payload = [
				'source' => 'backend-prefill',
			];
			// Inclure les champs saisis par l'utilisateur pour amélioration
			$inputEmail = (string) $request->input('email', '');
			$inputSubject = (string) $request->input('subject', '');
			$inputMessage = (string) $request->input('message', '');
			if ($inputEmail !== '') {
				$payload['email'] = $inputEmail;
				$payload['to'] = $inputEmail;
				$payload['dest'] = $inputEmail;
			}
			if ($inputSubject !== '') {
				$payload['subject'] = $inputSubject;
				$payload['sujet'] = $inputSubject;
				$payload['title'] = $inputSubject;
				$payload['objet'] = $inputSubject;
			}
			if ($inputMessage !== '') {
				$payload['message'] = $inputMessage;
				$payload['body'] = $inputMessage;
				$payload['content'] = $inputMessage;
				$payload['texte'] = $inputMessage;
				$payload['text'] = $inputMessage;
			}

            Log::info('Webhook POST start', ['url' => $webhookUrl, 'payload' => $payload]);
            $response = $client->post($webhookUrl, [
				'headers' => [
					'Content-Type' => 'application/json',
					'Accept' => 'application/json, text/plain;q=0.8, */*;q=0.5',
                    'User-Agent' => 'OC-Relay/1.0',
				],
                'json' => $payload,
                'http_errors' => false,
			]);
            $respBody = (string) $response->getBody();
            Log::info('Webhook POST done', [
                'status' => $response->getStatusCode(),
                'ct' => $response->getHeaderLine('content-type'),
                'body_preview' => substr($respBody, 0, 500),
            ]);
            [$email, $subject, $message, $raw] = $this->extractFields($response->getHeaderLine('content-type'), $respBody);
		} catch (\Throwable $e) {
            $lastError = $e->getMessage();
            Log::error('Webhook POST error', ['error' => $lastError]);
		}

		// 2) Si vide, essayer GET
		if ($email === '' && $subject === '' && $message === '') {
            try {
                Log::info('Webhook GET start', ['url' => $webhookUrl]);
				$response = $client->get($webhookUrl, [
					'headers' => [
                        'Accept' => 'application/json, text/plain;q=0.8, */*;q=0.5',
                        'User-Agent' => 'OC-Relay/1.0',
					],
                    'http_errors' => false,
				]);
                $respBody = (string) $response->getBody();
                Log::info('Webhook GET done', [
                    'status' => $response->getStatusCode(),
                    'ct' => $response->getHeaderLine('content-type'),
                    'body_preview' => substr($respBody, 0, 500),
                ]);
                [$email, $subject, $message, $raw] = $this->extractFields($response->getHeaderLine('content-type'), $respBody);
			} catch (\Throwable $e) {
				$lastError = $lastError ?: $e->getMessage();
                Log::error('Webhook GET error', ['error' => $lastError]);
			}
		}

		// Toujours renvoyer 200 avec ce qu'on a, pour que le front ouvre la modale
		return response()->json([
			'email' => $email,
			'subject' => $subject,
			'message' => $message,
			'raw' => $raw,
			'error' => ($email === '' && $subject === '' && $message === '') ? ($lastError ?: null) : null,
		]);
	}

	private function extractFields(string $contentType, string $body): array
	{
		$email = '';
		$subject = '';
		$message = '';
		$raw = null;

		$isJson = stripos($contentType, 'application/json') !== false;
		if ($isJson) {
			$data = json_decode($body, true);
			if (is_array($data)) {
				$raw = $data;
				$pick = function(array $obj, array $keys): string {
					foreach ($keys as $k) {
						if (array_key_exists($k, $obj) && $obj[$k] !== '' && $obj[$k] !== null) return (string) $obj[$k];
					}
					return '';
				};
				$email = $pick($data, ['email','to','dest','recipient','adresse','mail']);
				$subject = $pick($data, ['improved_subject','ai_subject','subject_ai','subject','sujet','title','objet']);
				$message = $pick($data, ['improved_message','ai_message','message_ai','message','body','content','texte','text']);

				// Essayer niveau imbriqué courant (data, result, payload, output, response)
				foreach (['data','result','payload','output','response'] as $child) {
					if (isset($data[$child]) && is_array($data[$child])) {
						if ($email === '') $email = $pick($data[$child], ['email','to','dest','recipient','adresse','mail']);
						if ($subject === '') $subject = $pick($data[$child], ['improved_subject','ai_subject','subject_ai','subject','sujet','title','objet']);
						if ($message === '') $message = $pick($data[$child], ['improved_message','ai_message','message_ai','message','body','content','texte','text']);
					}
				}

				return [$email, $subject, $message, $raw];
			}
		}

		// Fallback: tenter d'extraire depuis du texte brut "key: value"
		$text = trim($body);
		if ($text !== '') {
			$raw = $text;
			if (preg_match('/email\s*[:=]\s*(\S+)/i', $text, $m)) {
				$email = $m[1];
			}
			if (preg_match('/(subject|sujet|title|objet)\s*[:=]\s*(.+)/i', $text, $m)) {
				$subject = trim($m[2]);
			}
			if (preg_match('/(improved_message|ai_message|message_ai|message|body|content|texte|text)\s*[:=]\s*([\s\S]+)/i', $text, $m)) {
				$message = trim($m[2]);
			}
		}

		return [$email, $subject, $message, $raw];
	}
}



