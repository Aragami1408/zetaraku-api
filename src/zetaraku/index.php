<?php
require_once __DIR__.'/db.php';
require_once __DIR__.'/helpers.php';
require_once __DIR__.'/handlers.php';

allow_cors();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$segments = path_segments();

if (count($segments) === 0) {
	send_json([
			'name' => 'Zetaraku REST API',
			'version' => 'v1',
			'resources' => [
			'GET /songs',
			'POST /songs',
			'GET /songs/{id}',
			'PUT /songs/{id}',
			'DELETE /songs/{id}',
			'GET /songs/{id}/sheets',
			'GET /sheets/{id}',
			'POST /sheets',
			'PUT /sheets/{id}',
			'DELETE /sheets/{id}',
			]
	], 200);
}

$resource = $segments[0];

if ($resource === 'songs') {
	if ($method === 'GET' && count($segments) === 1) {
		handle_list_songs();
	}
	elseif ($method === 'POST' && count($segments) === 1) {
		handle_create_song();
	}
	elseif (count($segments) >= 2 && preg_match('/^\d+$/', $segments[1])) {
		$songId = (int)$segments[1];

		if     ($method === 'GET'    && count($segments) === 2) handle_get_song($songId);
		elseif ($method === 'PUT'    && count($segments) === 2) handle_update_song($songId);
		elseif ($method === 'DELETE' && count($segments) === 2) handle_delete_song($songId);
		elseif ($method === 'GET'    && count($segments) === 3 && $segments[2] === 'sheets') handle_list_sheets_by_song($songId);
		else send_json(['error' => 'Not found'], 404);
	} else {
		send_json(['error' => 'Not found'], 404);
	}
	exit;
}

if ($resource === 'sheets') {
	if     ($method === 'POST'   && count($segments) === 1) { handle_create_sheet(); }
	elseif ($method === 'GET'    && count($segments) === 2) { handle_get_sheet(validate_int($segments[1],'sheet id')); }
	elseif ($method === 'PUT'    && count($segments) === 2) { handle_update_sheet(validate_int($segments[1],'sheet id')); }
	elseif ($method === 'DELETE' && count($segments) === 2) { handle_delete_sheet(validate_int($segments[1],'sheet id')); }
	else { send_json(['error'=>'Not found'],404); }
	exit;
}

send_json(['error' => 'Resource \''. $resource . '\' not found'], 400);
?>
