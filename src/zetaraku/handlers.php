<?php
function handle_list_songs() {
	$q    = trim((string) get_query('q', ''));
	$cat  = trim((string) get_query('category', ''));
	$page = max(1, (int) get_query('page', 1));
	$limit= min(100, max(1, (int) get_query('limit', 20)));
	$offset = ($page - 1) * $limit;

	$where  = [];
	$params = [];
	$types  = '';

	if ($q !== '') {
		$where[] = '(title LIKE ? OR artist LIKE ?)';
		$params[] = "%$q%"; $params[] = "%$q%";
		$types   .= 'ss';
	}
	if ($cat !== '') {
		$where[] = 'category = ?';
		$params[] = $cat;
		$types   .= 's';
	}
	$sqlWhere = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

	// Count
	$row = select_one("SELECT COUNT(*) AS c FROM songs $sqlWhere", $types, $params);
	$total = (int)($row['c'] ?? 0);

	// Data
	$sql = "SELECT songId, title, category, artist, bpm, imageName, version, releaseDate, isNew, comment
		FROM songs
		$sqlWhere
		ORDER BY songId ASC
		LIMIT ? OFFSET ?";
	$params2 = $params;
	$types2  = $types . 'ii';
	$params2[] = $limit; $params2[] = $offset;

	$rows = select_all($sql, $types2, $params2);

	send_json([
			'success' => 'true',
			'page' => $page,
			'limit' => $limit,
			'total' => $total,
			'items' => $rows
	]);
}

function handle_create_song() {
	$b = read_json_body();

	$sql = "INSERT INTO songs
		(title, category, artist, bpm, imageName, version, releaseDate, isNew, comment)
		VALUES
		(?,?,?,?,?,?,?,?,?)";

	// Types: s s s i s s s i s
	$types = 'sssisssis';
	$params = [
		$b['title']       ?? null,
		$b['category']    ?? null,
		$b['artist']      ?? null,
		isset($b['bpm']) ? (int)$b['bpm'] : null,
		$b['imageName']   ?? null,
		$b['version']     ?? null,
		$b['releaseDate'] ?? null, // YYYY-MM-DD
		isset($b['isNew']) ? (int)!!$b['isNew'] : null,
		$b['comment']     ?? null,
	];

	$info = exec_write($sql, $types, $params);
	send_json(['songId' => (int)$info['insert_id']], 201);
}

function handle_get_song($songId) {
	$song = select_one("SELECT * FROM songs WHERE songId = ?", 'i', [$songId]);
	if (!$song) send_json(['error' => 'Song not found'], 404);

	if (get_query('include') === 'sheets') {
		$song['sheets'] = select_all("SELECT * FROM sheets WHERE songId = ? ORDER BY id ASC", 'i', [$songId]);
	}
	send_json($song);
}

function handle_update_song($songId) {
	$b = read_json_body();

	$fields = ['title','category','artist','bpm','imageName','version','releaseDate','isNew','comment'];
	$sets = [];
	$params = [];
	$types  = '';

	foreach ($fields as $f) {
		if (array_key_exists($f, $b)) {
			$sets[] = "$f = ?";
			if ($f === 'bpm')       { $params[] = isset($b[$f]) ? (int)$b[$f] : null; $types .= 'i'; }
			elseif ($f === 'isNew') { $params[] = (int)!!$b[$f]; $types .= 'i'; }
			else { $params[] = $b[$f]; $types .= 's'; }
		}
	}
	if (!$sets) send_json(['error' => 'No fields to update'], 400);

	$params[] = $songId; $types .= 'i';
	$sql = "UPDATE songs SET ".implode(', ', $sets)." WHERE songId = ?";

	exec_write($sql, $types, $params);
	http_response_code(204); exit;
}

function handle_delete_song($songId) {
	try {
		$info = exec_write("DELETE FROM songs WHERE songId = ?", 'i', [$songId]);
		if ($info['affected'] === 0) send_json(['error' => 'Song not found'], 404);
		http_response_code(204); exit;
	} catch (mysqli_sql_exception $e) {
		// 1451 = cannot delete or update a parent row: a foreign key constraint fails
		if ($e->getCode() === 1451) {
			send_json(['error' => 'Delete sheets first (foreign key constraint)'], 409);
		}
		throw $e;
	}
}

function handle_list_sheets_by_song($songId) {
	$rows = select_all("SELECT * FROM sheets WHERE songId = ? ORDER BY id ASC", 'i', [$songId]);
	send_json($rows);
}

// ---- SHEETS
function handle_create_sheet() {
	$b = read_json_body();
	if (!isset($b['songId'])) send_json(['error'=>'songId is required'], 400);

	$sql = "INSERT INTO sheets
		(songId, difficulty, level, levelValue, noteDesigner, tap, hold, slide, touch, breakCount, total)
		VALUES (?,?,?,?,?,?,?,?,?,?,?)";

	// Types: i s s d s i i i i i i
	// For DECIMAL(3,1) we’ll bind as double (d)
	$types = 'issdsiiiiii';
	$params = [
		(int)$b['songId'],
		$b['difficulty']   ?? null,
		$b['level']        ?? null,
		isset($b['levelValue']) ? (float)$b['levelValue'] : null,
		$b['noteDesigner'] ?? null,
		isset($b['tap'])        ? (int)$b['tap']        : null,
		isset($b['hold'])       ? (int)$b['hold']       : null,
		isset($b['slide'])      ? (int)$b['slide']      : null,
		isset($b['touch'])      ? (int)$b['touch']      : null,
		isset($b['breakCount']) ? (int)$b['breakCount'] : null,
		isset($b['total'])      ? (int)$b['total']      : null,
	];

		$info = exec_write($sql, $types, $params);
		send_json(['id' => (int)$info['insert_id']], 201);
}

function handle_get_sheet($id) {
	$row = select_one("SELECT * FROM sheets WHERE id = ?", 'i', [$id]);
	if (!$row) send_json(['error' => 'Sheet not found'], 404);
	send_json($row);
}

function handle_update_sheet($id) {
	$b = read_json_body();

	$fields = ['songId','difficulty','level','levelValue','noteDesigner','tap','hold','slide','touch','breakCount','total'];
	$sets = [];
	$params = [];
	$types  = '';

	foreach ($fields as $f) {
		if (array_key_exists($f, $b)) {
			$sets[] = "$f = ?";
			if (in_array($f, ['songId','tap','hold','slide','touch','breakCount','total'])) {
				$params[] = isset($b[$f]) ? (int)$b[$f] : null; $types .= 'i';
			} elseif ($f === 'levelValue') {
				$params[] = isset($b[$f]) ? (float)$b[$f] : null; $types .= 'd';
			} else {
				$params[] = $b[$f]; $types .= 's';
			}
		}
	}
	if (!$sets) send_json(['error'=>'No fields to update'], 400);

	$params[] = $id; $types .= 'i';
	$sql = "UPDATE sheets SET ".implode(', ', $sets)." WHERE id = ?";

	exec_write($sql, $types, $params);
	http_response_code(204); exit;
}

function handle_delete_sheet($id) {
	$info = exec_write("DELETE FROM sheets WHERE id = ?", 'i', [$id]);
	if ($info['affected'] === 0) send_json(['error' => 'Sheet not found'], 404);
	http_response_code(204); exit;
}

?>
